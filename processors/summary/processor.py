from transformers import pipeline
import logging
from base_processor import BaseProcessor

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)


class SummaryProcessor(BaseProcessor):
    def __init__(self):
        super().__init__(
            queue_url_env="SUMMARY_PROCESSOR_SQS_QUEUE",
            sns_topic_arn_env="POST_PROCESSING_SNS_ARN",
            max_messages_env="MAX_MESSAGES",
            max_duration_seconds_env="MAX_DURATION",
            aws_url_env="AWS_URL",
        )
        self.summarizer = pipeline("summarization", model="facebook/bart-large-cnn")

    def process_message(self, entry_text):
        try:
            summary = self.summarizer(
                entry_text,
                max_length=50,
                min_length=5,
                length_penalty=0.5,
            )
            logger.info(f"Processed summary: {summary}")
            summary = summary[0]["summary_text"]
            last_period_index = summary.rfind(".")
            if last_period_index != -1:
                summary = summary[: last_period_index + 1]
            return {
                "summary": summary,
            }

        except Exception as e:
            logger.error(f"Error in process_message: {e}")
            raise


if __name__ == "__main__":
    processor = SummaryProcessor()
    processor.poll_queue()
