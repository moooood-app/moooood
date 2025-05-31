import bentoml
from pydantic import Field, BaseModel
import typing as t
from transformers import pipeline, AutoTokenizer
from bentoml.models import HuggingFaceModel
from typing import List
import numpy as np
import re

image = bentoml.images.Image(
    python_version='3.11',
    distro='alpine',
).requirements_file("requirements.txt")

class AnalyzeInput(BaseModel):
    entry: str = Field(
        title="Entry Text",
        description="The entry text to analyze for emotions.",
        example="The quick brown fox jumps over the lazy dog.",
        min_length=1,
    )

class AnalyzeOutput(BaseModel):
    admiration: float = Field(
        title="Admiration",
        description="The level of admiration expressed in the text.",
        example=0.5,
    )
    amusement: float = Field(
        title="Amusement",
        description="The level of amusement expressed in the text.",
        example=0.5,
    )
    anger: float = Field(
        title="Anger",
        description="The level of anger expressed in the text.",
        example=0.5,
    )
    annoyance: float = Field(
        title="Annoyance",
        description="The level of annoyance expressed in the text.",
        example=0.5,
    )
    approval: float = Field(
        title="Approval",
        description="The level of approval expressed in the text.",
        example=0.5,
    )
    caring: float = Field(
        title="Caring",
        description="The level of caring expressed in the text.",
        example=0.5,
    )
    confusion: float = Field(
        title="Confusion",
        description="The level of confusion expressed in the text.",
        example=0.5,
    )
    curiosity: float = Field(
        title="Curiosity",
        description="The level of curiosity expressed in the text.",
        example=0.5,
    )
    desire: float = Field(
        title="Desire",
        description="The level of desire expressed in the text.",
        example=0.5,
    )
    disappointment: float = Field(
        title="Disappointment",
        description="The level of disappointment expressed in the text.",
        example=0.5,
    )
    disapproval: float = Field(
        title="Disapproval",
        description="The level of disapproval expressed in the text.",
        example=0.5,
    )
    disgust: float = Field(
        title="Disgust",
        description="The level of disgust expressed in the text.",
        example=0.5,
    )
    embarrassment: float = Field(
        title="Embarrassment",
        description="The level of embarrassment expressed in the text.",
        example=0.5,
    )
    excitement: float = Field(
        title="Excitement",
        description="The level of excitement expressed in the text.",
        example=0.5,
    )
    fear: float = Field(
        title="Fear",
        description="The level of fear expressed in the text.",
        example=0.5,
    )
    gratitude: float = Field(
        title="Gratitude",
        description="The level of gratitude expressed in the text.",
        example=0.5,
    )
    grief: float = Field(
        title="Grief",
        description="The level of grief expressed in the text.",
        example=0.5,
    )
    joy: float = Field(
        title="Joy",
        description="The level of joy expressed in the text.",
        example=0.5,
    )
    love: float = Field(
        title="Love",
        description="The level of love expressed in the text.",
        example=0.5,
    )
    nervousness: float = Field(
        title="Nervousness",
        description="The level of nervousness expressed in the text.",
        example=0.5,
    )
    optimism: float = Field(
        title="Optimism",
        description="The level of optimism expressed in the text.",
        example=0.5,
    )
    pride: float = Field(
        title="Pride",
        description="The level of pride expressed in the text.",
        example=0.5,
    )
    realization: float = Field(
        title="Realization",
        description="The level of realization expressed in the text.",
        example=0.5,
    )
    relief: float = Field(
        title="Relief",
        description="The level of relief expressed in the text.",
        example=0.5,
    )
    remorse: float = Field(
        title="Remorse",
        description="The level of remorse expressed in the text.",
        example=0.5,
    )
    sadness: float = Field(
        title="Sadness",
        description="The level of sadness expressed in the text.",
        example=0.5,
    )
    surprise: float = Field(
        title="Surprise",
        description="The level of surprise expressed in the text.",
        example=0.5,
    )
    neutral: float = Field(
        title="Neutral",
        description="The level of neutral emotion expressed in the text.",
        example=0.5,
    )

@bentoml.service(
    image=image,
    name="emotions",
    title="Text Emotions Service",
    description="Extract 28 emotions from text using GoEmotions model.",
)
class EmotionsService:
    # Define model reference at the class level as per BentoML docs
    model_ref = HuggingFaceModel("SamLowe/roberta-base-go_emotions")

    def __init__(self):
        # Load the actual model using the model reference
        self.classifier = pipeline(
            task="text-classification",
            model=self.model_ref,
            tokenizer=self.model_ref,
            top_k=None,
            function_to_apply="sigmoid",
            device_map="auto"
        )
        self.tokenizer = AutoTokenizer.from_pretrained(self.model_ref)
        # Reserve tokens for special tokens (CLS, SEP, etc.)
        self.MAX_LENGTH = 510  # Slightly less than 512 to account for special tokens
        self.OVERLAP_LENGTH = 50  # Fixed overlap instead of stride

    @bentoml.api(input_spec=AnalyzeInput, output_spec=AnalyzeOutput)
    def analyze(self, **params: t.Any) -> AnalyzeOutput:
        entry = params["entry"]

        try:
            # Use the classifier pipeline with truncation to handle max length
            emotions = self.classifier(entry, truncation=True, max_length=512)

            # Extract emotion scores
            if isinstance(emotions, list) and len(emotions) > 0 and isinstance(emotions[0], list):
                emotion_list = emotions[0]
                emotion_scores = {emotion['label']: emotion['score'] for emotion in emotion_list}
            else:
                # Fallback to empty scores if unexpected format
                emotion_scores = {}

        except Exception as e:
            print(f"Error processing text: {e}")
            emotion_scores = {}

        # Create output object with all emotions
        output_dict = {}
        for emotion_field in AnalyzeOutput.model_fields.keys():
            output_dict[emotion_field] = emotion_scores.get(emotion_field, 0.0)

        return AnalyzeOutput(**output_dict)
