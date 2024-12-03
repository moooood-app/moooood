from factories import ServiceFactory
from typing import Union, List, Dict, Any
import os
import numpy as np
from transformers import pipeline

# Initialize the classifier
classifier = pipeline(
    task="text-classification",
    model="SamLowe/roberta-base-go_emotions",
    top_k=None,
    truncation=True,
)
class EmotionsProcessor:
    def __init__(self, max_length: int = 512, stride: int = 256):
        self.max_length = max_length
        self.stride = stride

    def _split_text_into_chunks(self, text: str) -> List[str]:
        """
        Splits the text into overlapping chunks based on the model's maximum token length.
        :param text: The input text to be split.
        :return: A list of text chunks.
        """
        # Tokenize the text without truncation
        tokens = classifier.tokenizer(text, truncation=False, return_tensors="pt")["input_ids"][0]
        chunks = [
            tokens[i : i + self.max_length - 2]
            for i in range(0, len(tokens), self.max_length - self.stride)
        ]
        # Decode the token chunks back to text
        return [
            classifier.tokenizer.decode(chunk, skip_special_tokens=True)
            for chunk in chunks
        ]

    def process(self, entry: str) -> Union[Dict[str, Any], List[Any]]:
        chunks = self._split_text_into_chunks(entry)
        all_probs = []

        for chunk in chunks:
            # Classify each chunk with truncation to handle unexpected length issues
            emotions = classifier(chunk, truncation=True)
            chunk_probs = {emotion['label']: emotion['score'] for emotion in emotions[0]}
            all_probs.append(chunk_probs)

        # Aggregate probabilities
        aggregated_probs = {}
        for probs in all_probs:
            for emotion, score in probs.items():
                aggregated_probs[emotion] = aggregated_probs.get(emotion, 0) + score

        # Normalize aggregated probabilities
        total_scores = sum(aggregated_probs.values())
        normalized_probs = {emotion: score / total_scores for emotion, score in aggregated_probs.items()}

        return normalized_probs

def main():
    ServiceFactory.create(EmotionsProcessor(), os.getenv("EMOTIONS_PROCESSOR_SQS_QUEUE")).handle()

if __name__ == "__main__":
    main()
