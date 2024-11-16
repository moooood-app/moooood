import spacy
import asent
import logging
from base_processor import BaseProcessor

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)


class SentimentProcessor(BaseProcessor):
    def __init__(self):
        super().__init__(
            queue_url_env="SENTIMENT_PROCESSOR_SQS_QUEUE",
            sns_topic_arn_env="POST_PROCESSING_SNS_ARN",
            max_messages_env="MAX_MESSAGES",
            max_duration_seconds_env="MAX_DURATION",
            aws_url_env="AWS_URL",
        )
        self.nlp = spacy.blank("en")
        self.nlp.add_pipe("sentencizer")
        self.nlp.add_pipe("asent_en_v1")

    def process_message(self, entry_text):
        try:
            doc = self.nlp(entry_text)
            result = doc._.polarity.to_dict()
            return {
                key: result[key]
                for key in result.keys()
                & {"negative", "positive", "neutral", "compound"}
            }
        except Exception as e:
            logger.error(f"Error in process_message: {e}")
            raise


if __name__ == "__main__":
    processor = SentimentProcessor()
    processor.poll_queue()
