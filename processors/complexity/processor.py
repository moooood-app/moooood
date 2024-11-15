import json
import boto3
import textstat
import time
import os
import logging

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

# Load environment variables
SQS_QUEUE_URL = os.getenv('COMPLEXITY_PROCESSOR_SQS_QUEUE')
SNS_TOPIC_ARN = os.getenv("POST_PROCESSING_SNS_ARN")
MAX_MESSAGES = int(os.getenv("MAX_MESSAGES", 10))
AWS_URL = f"http://{os.getenv('AWS_URL')}"

# TODO not production ready
sqs = boto3.client('sqs', endpoint_url=AWS_URL)
sns = boto3.client('sns', endpoint_url=AWS_URL)

def process_message(entry_text):
    metrics = {
        # The measure of the readability of a text based on the
        # average number of syllables per word and words per sentence.
        # Higher values indicate more complex language
        # ----------------------------------------------
        # Minimum Value: 0 (easiest)
        # Maximum Value: 100 (most difficult)
        'flesch_kincaid_grade_level': textstat.flesch_kincaid_grade(entry_text),
        # A score that indicates how easy or difficult a text is to read.
        # Higher scores indicate easier readability, while lower scores indicate more complex text.
        # ----------------------------------------------
        # Minimum Value: 0 (most difficult)
        # Maximum Value: 100 (easiest)
        'flesch_reading_ease': textstat.flesch_reading_ease(entry_text),
        # The Gunning Fog Index is a readability formula that estimates the years of formal education
        # required to understand a piece of text. It considers the average number of words per sentence
        # and the percentage of complex words (words with three or more syllables) in the text.
        # ----------------------------------------------
        # Minimum Value: 0 (easiest)
        # Maximum Value: No upper limit, but typically ranges from 6 to 20+
        'gunning_fog_index': textstat.gunning_fog(entry_text),
        # The SMOG Index (Simple Measure of Gobbledygook) is another readability formula
        # that estimates the years of education needed to understand a text.
        # It calculates the index based on the number of complex words (words with three
        # or more syllables) in a sample of text.
        # ----------------------------------------------
        # Minimum Value: 0 (easiest)
        # Maximum Value: No upper limit, but typically ranges from 6 to 20+
        'smog_index': textstat.smog_index(entry_text),
        # Another readability index similar to Flesch-Kincaid Grade Level,
        # estimating the years of education needed to understand the text.
        # Minimum Value: 0 (easiest)
        # Maximum Value: No upper limit, but typically corresponds to grade levels (e.g., 12 for 12th grade)
        'automated_readability_index': textstat.automated_readability_index(entry_text),
        # A readability index that calculates the grade level required
        # to understand the text based on characters per word and sentences per 100 words.
        # ----------------------------------------------
        # Minimum Value: -3 (rarely used in practice)
        # Maximum Value: No upper limit, but typically corresponds to grade levels (e.g., 12 for 12th grade)
        'coleman_liau_index': textstat.coleman_liau_index(entry_text),
        # The Linsear Write Formula is a readability formula that estimates the readability
        # of a text based on the number of simple and complex words in a sample.
        # It considers words with one or two syllables as simple words and words with three
        #  or more syllables as complex words.
        # ----------------------------------------------
        # Minimum Value: 0 (easiest)
        # Maximum Value: Typically ranges from 0 to 20+
        'linsear_write_formula': textstat.linsear_write_formula(entry_text),
        # The Dale-Chall Readability Score is a readability formula that estimates the readability
        # of a text by considering a list of "easy" words. The formula calculates the percentage
        # of words in a text that are not on the list of easy words.
        # ----------------------------------------------
        # Minimum Value: 0 (easiest)
        # Maximum Value: No upper limit, but typically corresponds to grade levels (e.g., 12 for 12th grade)
        'dale_chall_readability_score': textstat.dale_chall_readability_score(entry_text),
        # The Readability Consensus score is an average of several readability formulas,
        # including Flesch Reading Ease, Flesch-Kincaid Grade Level, Coleman-Liau Index,
        # Automated Readability Index, and Dale-Chall Readability Score.
        # This composite score provides an overall assessment of text readability,
        # considering multiple factors.
        # ----------------------------------------------
        # Minimum Value: 0 (most difficult)
        # Maximum Value: Typically ranges from 0 to 100, with higher values indicating easier readability.
        'readability_consensus': textstat.text_standard(entry_text, float_output=True)
    }

    metrics['complexity_rating'] = calculate_complexity_rating(metrics)

    return metrics

def calculate_complexity_rating(metrics):
    # Define weights for each metric (adjusted for importance)
    weights = {
        'flesch_kincaid_grade_level': 0.2,
        'flesch_reading_ease': 0.2,
        'gunning_fog_index': 0.1,
        'smog_index': 0.1,
        'automated_readability_index': 0.1,
        'coleman_liau_index': 0.1,
        'linsear_write_formula': 0.1,
        'dale_chall_readability_score': 0.1,
        'readability_consensus': 0.1
    }

    # Normalize metrics based on observed practical ranges and invert if needed
    normalized = {
        # Inverting scores where lower values mean harder text
        'flesch_kincaid_grade_level': max(0, min(100, metrics['flesch_kincaid_grade_level'] * 5)),
        'flesch_reading_ease': max(0, min(100, 100 + metrics['flesch_reading_ease'])),  # Adjust negative scores
        'gunning_fog_index': max(0, min(100, metrics['gunning_fog_index'] * 5)),
        'smog_index': max(0, min(100, metrics['smog_index'] * 5)),  # Address unusual smog_index of 0
        'automated_readability_index': max(0, min(100, metrics['automated_readability_index'] * 5)),
        'coleman_liau_index': max(0, min(100, metrics['coleman_liau_index'] * 5)),
        'linsear_write_formula': max(0, min(100, metrics['linsear_write_formula'] * 5)),
        'dale_chall_readability_score': max(0, min(100, metrics['dale_chall_readability_score'] * 5)),
        'readability_consensus': max(0, min(100, metrics['readability_consensus'] * 5))
    }

    # Calculate the weighted sum of the normalized metrics
    complexity_rating = sum(normalized[metric] * weights[metric] for metric in weights)

    # Return the final complexity rating
    return round(complexity_rating, 2)

def send_sns_notification(result):
    try:
        sns.publish(
            TopicArn=SNS_TOPIC_ARN,
            Message=json.dumps(result),
            Subject="Sentiment Analysis Result",
        )
        logger.info(f"Sent SNS notification: {result}")
    except Exception as e:
        logger.error(f"Error sending SNS notification: {e}")
        raise

def poll_queue():
    processed_count = 0

    while processed_count < MAX_MESSAGES:
        try:
            response = sqs.receive_message(
                QueueUrl=SQS_QUEUE_URL,
                MaxNumberOfMessages=1,
                WaitTimeSeconds=5
            )

            if "Messages" not in response:
                print("No messages available, retrying...")
                time.sleep(1)
                continue

            for message in response["Messages"]:
                try:
                    message_body = json.loads(message['Body'])
                    payload = json.loads(message_body.get('Message'))
                    logger.info(f"Received SQS message: {payload}")
                    result = {
                        'result': process_message(payload.pop('content')),
                        'processor': 'complexity',
                    }
                    payload.update(result)
                    send_sns_notification(payload)
                    sqs.delete_message(QueueUrl=SQS_QUEUE_URL, ReceiptHandle=message['ReceiptHandle'])
                    processed_count += 1

                except Exception as e:
                    print(f"Error processing message: {e}")
                    # Optionally, handle or log the error and delete the problematic message if desired
                    sqs.delete_message(QueueUrl=SQS_QUEUE_URL, ReceiptHandle=message['ReceiptHandle'])
        except Exception as e:
            logger.error(f"Error in polling SQS: {e}")
            time.sleep(5)  # Backoff before retrying

if __name__ == "__main__":
    poll_queue()
