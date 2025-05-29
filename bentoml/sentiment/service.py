import bentoml
from pydantic import BaseModel, Field
from typing import Any, List
from transformers import pipeline, AutoTokenizer
from bentoml.models import HuggingFaceModel
import re

image = bentoml.images.Image(
    python_version='3.11',
    distro='alpine',
).requirements_file("requirements.txt")

class SentimentInput(BaseModel):
    entry: str = Field(
        title="Entry Text",
        description="The input text to analyze for sentiment.",
        example="I love how this product works!",
        min_length=1,
    )

class SentimentOutput(BaseModel):
    positive: float = Field(description="Confidence score for positive sentiment.")
    neutral: float = Field(description="Confidence score for neutral sentiment.")
    negative: float = Field(description="Confidence score for negative sentiment.")

@bentoml.service(
    image=image,
    name="sentiment-analysis",
    title="Sentiment Analysis Service",
    description="Extract positive, neutral, and negative sentiments from text using CardiffNLP model.",
)
class SentimentService:
    model_ref = HuggingFaceModel("cardiffnlp/twitter-roberta-base-sentiment-latest")

    def __init__(self):
        self.classifier = pipeline(
            task="text-classification",
            model=self.model_ref,
            tokenizer=self.model_ref,
            top_k=None,
            function_to_apply="softmax",
            device_map="auto"
        )
        self.tokenizer = AutoTokenizer.from_pretrained(self.model_ref)
        self.MAX_LENGTH = 510
        self.OVERLAP_LENGTH = 50

    @bentoml.api(input_spec=SentimentInput, output_spec=SentimentOutput)
    def analyze(self, **params: Any) -> SentimentOutput:
        entry = params["entry"]
        chunks = self._split_text_smart(entry)

        all_scores = []
        chunk_weights = []

        for chunk in chunks:
            try:
                result = self.classifier(chunk, truncation=True, max_length=self.MAX_LENGTH + 2)
                if isinstance(result, list) and isinstance(result[0], list):
                    scores = {r['label'].lower(): r['score'] for r in result[0]}
                    all_scores.append(scores)
                    chunk_weights.append(len(chunk.split()))
            except Exception as e:
                print(f"Warning processing chunk: {e}")
                continue

        if not all_scores:
            return SentimentOutput(positive=0.0, neutral=0.0, negative=0.0)

        aggregated = self._weighted_aggregate(all_scores, chunk_weights)
        return SentimentOutput(
            positive=aggregated.get('positive', 0.0),
            neutral=aggregated.get('neutral', 0.0),
            negative=aggregated.get('negative', 0.0),
        )

    def _split_text_smart(self, text: str) -> List[str]:
        tokens = self.tokenizer.encode(text, add_special_tokens=False)
        if len(tokens) <= self.MAX_LENGTH:
            return [text]

        sentences = self._split_sentences(text)
        chunks = []
        current_chunk = ""
        current_tokens = 0
        i = 0

        while i < len(sentences):
            sentence = sentences[i]
            sentence_tokens = len(self.tokenizer.encode(sentence, add_special_tokens=False))

            if sentence_tokens > self.MAX_LENGTH:
                if current_chunk:
                    chunks.append(current_chunk.strip())
                current_chunk = ""
                current_tokens = 0
                word_chunks = self._split_long_sentence(sentence)
                chunks.extend(word_chunks)
                i += 1
                continue

            if current_tokens + sentence_tokens > self.MAX_LENGTH:
                if current_chunk:
                    chunks.append(current_chunk.strip())
                overlap_text = self._get_overlap_text(current_chunk)
                current_chunk = overlap_text + sentence + " "
                current_tokens = len(self.tokenizer.encode(current_chunk, add_special_tokens=False))
            else:
                current_chunk += sentence + " "
                current_tokens += sentence_tokens + 1

            i += 1

        if current_chunk.strip():
            chunks.append(current_chunk.strip())

        return [c for c in chunks if c.strip()]

    def _split_sentences(self, text: str) -> List[str]:
        return [s.strip() for s in re.split(r'[.!?]+\s+', text) if s.strip()]

    def _split_long_sentence(self, sentence: str) -> List[str]:
        words = sentence.split()
        chunks = []
        current_chunk = ""

        for word in words:
            test_chunk = current_chunk + " " + word if current_chunk else word
            tokens = len(self.tokenizer.encode(test_chunk, add_special_tokens=False))
            if tokens > self.MAX_LENGTH:
                if current_chunk:
                    chunks.append(current_chunk.strip())
                current_chunk = word
            else:
                current_chunk = test_chunk

        if current_chunk.strip():
            chunks.append(current_chunk.strip())

        return chunks

    def _get_overlap_text(self, chunk: str) -> str:
        words = chunk.split()
        if len(words) <= self.OVERLAP_LENGTH:
            return ""

        overlap_words = words[-self.OVERLAP_LENGTH:]
        overlap_text = " ".join(overlap_words) + " "
        tokens = len(self.tokenizer.encode(overlap_text, add_special_tokens=False))

        while tokens > self.OVERLAP_LENGTH and overlap_words:
            overlap_words.pop(0)
            overlap_text = " ".join(overlap_words) + " "
            tokens = len(self.tokenizer.encode(overlap_text, add_special_tokens=False))

        return overlap_text

    def _weighted_aggregate(self, scores: List[dict], weights: List[int]) -> dict:
        total_weight = sum(weights)
        weighted = {}

        for score_dict, w in zip(scores, weights):
            norm_w = w / total_weight
            for label, val in score_dict.items():
                weighted[label] = weighted.get(label, 0.0) + val * norm_w

        return weighted
