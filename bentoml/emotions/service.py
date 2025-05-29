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
        chunks = self._split_text_smart(entry)

        if not chunks:
            return AnalyzeOutput(**{field: 0.0 for field in AnalyzeOutput.model_fields.keys()})

        all_probs = []
        chunk_weights = []

        for chunk in chunks:
            try:
                # Use the classifier pipeline
                emotions = self.classifier(chunk, truncation=True, max_length=self.MAX_LENGTH + 2)

                # Extract emotion scores
                if isinstance(emotions, list) and len(emotions) > 0 and isinstance(emotions[0], list):
                    emotion_list = emotions[0]
                    chunk_probs = {emotion['label']: emotion['score'] for emotion in emotion_list}
                    all_probs.append(chunk_probs)
                    # Weight chunks by their length (longer chunks get more weight)
                    chunk_weights.append(len(chunk.split()))
                else:
                    print(f"Warning: Unexpected emotions structure: {emotions}")
                    continue

            except Exception as e:
                print(f"Warning: Error processing chunk: {e}")
                continue

        if not all_probs:
            print("Warning: No probabilities extracted from any chunks")
            return AnalyzeOutput(**{field: 0.0 for field in AnalyzeOutput.model_fields.keys()})

        # Weighted aggregation of probabilities
        aggregated_probs = self._weighted_aggregate(all_probs, chunk_weights)

        # Create output object with all emotions
        output_dict = {}
        for emotion_field in AnalyzeOutput.model_fields.keys():
            output_dict[emotion_field] = aggregated_probs[emotion_field] if emotion_field in aggregated_probs else 0.0

        return AnalyzeOutput(**output_dict)

    def _split_text_smart(self, text: str) -> List[str]:
        """
        Improved text chunking that:
        1. Respects sentence boundaries when possible
        2. Uses token-based length checking
        3. Handles overlaps more intelligently
        """
        # Quick check if text fits in one chunk
        tokens = self.tokenizer.encode(text, add_special_tokens=False)
        if len(tokens) <= self.MAX_LENGTH:
            return [text]

        # Split into sentences first
        sentences = self._split_sentences(text)
        chunks = []
        current_chunk = ""
        current_tokens = 0

        i = 0
        while i < len(sentences):
            sentence = sentences[i]
            sentence_tokens = len(self.tokenizer.encode(sentence, add_special_tokens=False))

            # If single sentence is too long, split it by words
            if sentence_tokens > self.MAX_LENGTH:
                if current_chunk:
                    chunks.append(current_chunk.strip())
                    current_chunk = ""
                    current_tokens = 0

                # Split long sentence into word-based chunks
                word_chunks = self._split_long_sentence(sentence)
                chunks.extend(word_chunks)
                i += 1
                continue

            # Check if adding this sentence would exceed limit
            if current_tokens + sentence_tokens > self.MAX_LENGTH:
                if current_chunk:
                    chunks.append(current_chunk.strip())

                # Start new chunk with overlap from previous chunk
                overlap_text = self._get_overlap_text(current_chunk)
                current_chunk = overlap_text + sentence + " "
                current_tokens = len(self.tokenizer.encode(current_chunk, add_special_tokens=False))
            else:
                current_chunk += sentence + " "
                current_tokens += sentence_tokens + 1  # +1 for space

            i += 1

        # Add final chunk
        if current_chunk.strip():
            chunks.append(current_chunk.strip())

        return [chunk for chunk in chunks if chunk.strip()]

    def _split_sentences(self, text: str) -> List[str]:
        """Split text into sentences using regex patterns."""
        # Simple sentence splitting - can be improved with more sophisticated methods
        sentences = re.split(r'[.!?]+\s+', text)
        # Clean up and filter empty sentences
        sentences = [s.strip() for s in sentences if s.strip()]
        return sentences

    def _split_long_sentence(self, sentence: str) -> List[str]:
        """Split a long sentence into word-based chunks."""
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
        """Get overlap text from the end of a chunk."""
        words = chunk.split()
        if len(words) <= self.OVERLAP_LENGTH:
            return ""

        overlap_words = words[-self.OVERLAP_LENGTH:]
        overlap_text = " ".join(overlap_words) + " "

        # Ensure overlap doesn't exceed token limit
        tokens = len(self.tokenizer.encode(overlap_text, add_special_tokens=False))
        if tokens > self.OVERLAP_LENGTH * 2:  # Safety margin
            # Truncate overlap if too long
            while tokens > self.OVERLAP_LENGTH and overlap_words:
                overlap_words.pop(0)
                overlap_text = " ".join(overlap_words) + " "
                tokens = len(self.tokenizer.encode(overlap_text, add_special_tokens=False))

        return overlap_text

    def _weighted_aggregate(self, all_probs: List[dict], weights: List[int]) -> dict:
        """Aggregate probabilities using weighted averaging."""
        if not all_probs:
            return {}

        # Normalize weights
        total_weight = sum(weights)
        normalized_weights = [w / total_weight for w in weights]

        aggregated_probs = {}
        for probs, weight in zip(all_probs, normalized_weights):
            for emotion, score in probs.items():
                aggregated_probs[emotion] = aggregated_probs.get(emotion, 0) + score * weight

        return aggregated_probs
