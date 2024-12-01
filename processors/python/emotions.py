from factories import ServiceFactory
from typing import Union, List, Dict, Any
import os
import unicodedata
from transformers import pipeline

classifier = pipeline(task="text-classification", model="SamLowe/roberta-base-go_emotions", top_k=None)

class EmotionsProcessor:
    def process(self, entry: str) -> Union[Dict[str, Any], List[Any]]:
        emotions = classifier(unicodedata.normalize("NFKD", entry))

        return {
            emotion['label']: emotion['score'] for emotion in emotions[0]
        }

def main():
    ServiceFactory.create(EmotionsProcessor(), os.getenv("EMOTIONS_PROCESSOR_SQS_QUEUE")).handle()

if __name__ == "__main__":
    main()
