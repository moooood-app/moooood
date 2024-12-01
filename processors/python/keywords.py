from keybert import KeyBERT
from factories import ServiceFactory
from typing import Union, List, Dict, Any
import os

class KeywordsProcessor:
    def __init__(self):
        self.keybert_model = KeyBERT("all-mpnet-base-v2")

    def process(self, entry: str) -> Union[Dict[str, Any], List[Any]]:
        keywords = self.keybert_model.extract_keywords(
            entry,
            keyphrase_ngram_range=(1, 1),
            stop_words="english",
            top_n=10,
            use_mmr=True,
            diversity=0.10,
        )
        return [{"keyword": keyword, "score": score} for keyword, score in keywords]

def main():
    ServiceFactory.create(KeywordsProcessor(), os.getenv("KEYWORDS_PROCESSOR_SQS_QUEUE")).handle()

if __name__ == "__main__":
    main()
