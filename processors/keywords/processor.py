from keybert import KeyBERT
import logging
from base_processor import BaseProcessor

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)


class KeywordsProcessor(BaseProcessor):
    def __init__(self):
        super().__init__(
            queue_url_env="KEYWORDS_PROCESSOR_SQS_QUEUE",
            sns_topic_arn_env="POST_PROCESSING_SNS_ARN",
            max_messages_env="MAX_MESSAGES",
            max_duration_seconds_env="MAX_DURATION",
            aws_url_env="AWS_URL",
        )
        self.keybert_model = KeyBERT("all-mpnet-base-v2")

    def process_message(self, entry_text):
        try:
            keywords = []
            for ngram_range, top_n in [((1, 1), 10), ((2, 2), 5)]:
                keywords += self.keybert_model.extract_keywords(
                    entry_text,
                    keyphrase_ngram_range=ngram_range,
                    stop_words="english",
                    top_n=top_n,
                    use_mmr=True,
                    diversity=0.25,
                )
            return [{"keyword": keyword, "score": score} for keyword, score in keywords]
        except Exception as e:
            logger.error(f"Error in process_message: {e}")
            raise


if __name__ == "__main__":
    processor = KeywordsProcessor()
    processor.poll_queue()
