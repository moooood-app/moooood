import spacy
import asent
import boto3
import time
import json
import os
import logging

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

# Load environment variables
SQS_QUEUE_URL = os.getenv('ASENT_PROCESSOR_SQS_QUEUE')
SNS_TOPIC_ARN = os.getenv("POST_PROCESSING_SNS_ARN")
MAX_MESSAGES = int(os.getenv("MAX_MESSAGES", 10))
AWS_URL = f"http://{os.getenv('AWS_URL')}"

# TODO not production ready
sqs = boto3.client('sqs', endpoint_url=AWS_URL)
sns = boto3.client('sns', endpoint_url=AWS_URL)

# Initialize NLP
nlp = spacy.blank('en')
nlp.add_pipe('sentencizer')
nlp.add_pipe("asent_en_v1")

def process_message(entry_text):
    try:
        doc = nlp(entry_text)
        result = doc._.polarity.to_dict()
        return {key: result[key] for key in result.keys() & {"negative", "positive", "neutral"}}
    except Exception as e:
        logger.error(f"Error in process_message: {e}")
        raise

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
                logger.info("No messages available, retrying...")
                time.sleep(1)
                continue

            for message in response["Messages"]:
                try:
                    message_body = json.loads(message['Body'])
                    payload = json.loads(message_body.get('Message'))
                    logger.info(f"Received SQS message: {payload}")
                    result = {
                        'result': process_message(payload.pop('content')),
                        'processor': 'asent',
                    }
                    payload.update(result)
                    send_sns_notification(payload)
                    sqs.delete_message(QueueUrl=SQS_QUEUE_URL, ReceiptHandle=message['ReceiptHandle'])
                    processed_count += 1

                except Exception as e:
                    logger.error(f"Failed to process message: {e}")
        except Exception as e:
            logger.error(f"Error in polling SQS: {e}")
            time.sleep(5)  # Backoff before retrying

if __name__ == "__main__":
    poll_queue()
