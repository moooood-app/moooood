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

        try:
            # Use the classifier pipeline with truncation to handle max length
            result = self.classifier(entry, truncation=True, max_length=512)

            # Extract sentiment scores
            if isinstance(result, list) and isinstance(result[0], list):
                scores = {r['label'].lower(): r['score'] for r in result[0]}
            else:
                # Fallback to empty scores if unexpected format
                scores = {}

        except Exception as e:
            print(f"Error processing text: {e}")
            scores = {}

        return SentimentOutput(
            positive=scores.get('positive', 0.0),
            neutral=scores.get('neutral', 0.0),
            negative=scores.get('negative', 0.0),
        )
