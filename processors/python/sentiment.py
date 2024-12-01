import spacy
import asent
from factories import ServiceFactory
from typing import Union, List, Dict, Any
import os

class SentimentProcessor:
    def __init__(self):
        self.nlp = spacy.blank("en")
        self.nlp.add_pipe("sentencizer")
        self.nlp.add_pipe("asent_en_v1")

    def process(self, entry: str) -> Union[Dict[str, Any], List[Any]]:
        doc = self.nlp(entry)
        result = doc._.polarity.to_dict()
        return {
            key: result[key]
            for key in result.keys()
            & {"negative", "positive", "neutral", "compound"}
        }

def main():
    ServiceFactory.create(SentimentProcessor(), os.getenv("SENTIMENT_PROCESSOR_SQS_QUEUE")).handle()

if __name__ == "__main__":
    main()
