import bentoml
from pydantic import Field, BaseModel
import typing as t
from transformers import AutoTokenizer
import onnxruntime as ort
import torch.nn.functional as F
import torch
import os
import numpy as np

image = bentoml.images.Image(
        python_version='3.11',
        distro='alpine',
    ).requirements_file("requirements.txt")

class AnalyzeInput(BaseModel):
    entry: str = Field(
        title="Entry Text",
        description="The entry text to analyze for complexity.",
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
        title="Admiration",
        description="The level of admiration expressed in the text.",
        example=0.5,
    )
    anger: float = Field(
        title="Admiration",
        description="The level of admiration expressed in the text.",
        example=0.5,
    )
    annoyance: float = Field(
        title="Admiration",
        description="The level of admiration expressed in the text.",
        example=0.5,
    )
    approval: float = Field(
        title="Admiration",
        description="The level of admiration expressed in the text.",
        example=0.5,
    )
    caring: float = Field(
        title="Admiration",
        description="The level of admiration expressed in the text.",
        example=0.5,
    )
    confusion: float = Field(
        title="Admiration",
        description="The level of admiration expressed in the text.",
        example=0.5,
    )
    curiosity: float = Field(
        title="Admiration",
        description="The level of admiration expressed in the text.",
        example=0.5,
    )
    desire: float = Field(
        title="Admiration",
        description="The level of admiration expressed in the text.",
        example=0.5,
    )
    disappointment: float = Field(
        title="Admiration",
        description="The level of admiration expressed in the text.",
        example=0.5,
    )
    disapproval: float = Field(
        title="Admiration",
        description="The level of admiration expressed in the text.",
        example=0.5,
    )
    disgust: float = Field(
        title="Admiration",
        description="The level of admiration expressed in the text.",
        example=0.5,
    )
    embarrassment: float = Field(
        title="Admiration",
        description="The level of admiration expressed in the text.",
        example=0.5,
    )
    excitement: float = Field(
        title="Admiration",
        description="The level of admiration expressed in the text.",
        example=0.5,
    )
    fear: float = Field(
        title="Admiration",
        description="The level of admiration expressed in the text.",
        example=0.5,
    )
    gratitude: float = Field(
        title="Admiration",
        description="The level of admiration expressed in the text.",
        example=0.5,
    )
    grief: float = Field(
        title="Admiration",
        description="The level of admiration expressed in the text.",
        example=0.5,
    )
    joy: float = Field(
        title="Admiration",
        description="The level of admiration expressed in the text.",
        example=0.5,
    )
    love: float = Field(
        title="Admiration",
        description="The level of admiration expressed in the text.",
        example=0.5,
    )
    nervousness: float = Field(
        title="Admiration",
        description="The level of admiration expressed in the text.",
        example=0.5,
    )
    optimism: float = Field(
        title="Admiration",
        description="The level of admiration expressed in the text.",
        example=0.5,
    )
    pride: float = Field(
        title="Admiration",
        description="The level of admiration expressed in the text.",
        example=0.5,
    )
    realization: float = Field(
        title="Admiration",
        description="The level of admiration expressed in the text.",
        example=0.5,
    )
    relief: float = Field(
        title="Admiration",
        description="The level of admiration expressed in the text.",
        example=0.5,
    )
    remorse: float = Field(
        title="Admiration",
        description="The level of admiration expressed in the text.",
        example=0.5,
    )
    sadness: float = Field(
        title="Admiration",
        description="The level of admiration expressed in the text.",
        example=0.5,
    )
    surprise: float = Field(
        title="Admiration",
        description="The level of admiration expressed in the text.",
        example=0.5,
    )
    neutral: float = Field(
        title="Admiration",
        description="The level of admiration expressed in the text.",
        example=0.5,
    )

model_ref = bentoml.models.get("go_emotions_onnx:latest")
labels = model_ref.info.metadata["labels"]
tokenizer_dir = model_ref.custom_objects["tokenizer"]
tokenizer = AutoTokenizer.from_pretrained(tokenizer_dir)
session = ort.InferenceSession(model_ref.path_of("model.onnx"))

# ONNX session using GPU (with fallback to CPU)
session = ort.InferenceSession(
    model_ref.path_of("model.onnx"),
    providers=["CUDAExecutionProvider", "CPUExecutionProvider"]
)

@bentoml.service(
    image=image,
    name="emotions",
    title="Text Emotions Service",
    description="Extract 28 emotions from text.",
)
class Complexity:
    @bentoml.api(input_spec=AnalyzeInput, output_spec=AnalyzeOutput)
    def analyze(self, **params: t.Any) -> AnalyzeOutput:
        entry = params["entry"]

        return AnalyzeOutput(**{
            "flesch_kincaid_grade_level": textstat.flesch_kincaid_grade(entry),
            "flesch_reading_ease": textstat.flesch_reading_ease(entry),
            "gunning_fog_index": textstat.gunning_fog(entry),
            "smog_index": textstat.smog_index(entry),
            "automated_readability_index": textstat.automated_readability_index(entry),
            "coleman_liau_index": textstat.coleman_liau_index(entry),
            "linsear_write_formula": textstat.linsear_write_formula(entry),
            "dale_chall_readability_score": textstat.dale_chall_readability_score(entry),
            "readability_consensus": textstat.text_standard(entry, float_output=True),
        })
