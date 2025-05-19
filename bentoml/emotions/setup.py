import bentoml
from transformers import AutoTokenizer
import shutil
import os

MODEL_ID = "SamLowe/roberta-base-go_emotions-onnx"
LOCAL_DIR = "go_emotions_model"

# Step 1: Download tokenizer
tokenizer = AutoTokenizer.from_pretrained(MODEL_ID)
tokenizer.save_pretrained(os.path.join(LOCAL_DIR, "tokenizer"))

# Step 2: Download ONNX model from Hugging Face manually if needed
# For this example, assume it's already saved as model.onnx in the same folder
onnx_model_path = os.path.join(LOCAL_DIR, "model.onnx")

# Step 3: Get labels
from huggingface_hub import hf_hub_download
import json

labels_path = hf_hub_download(MODEL_ID, filename="labels.json")
with open(labels_path, "r") as f:
    labels = json.load(f)

# Step 4: Save with BentoML
bentoml.models.save_model(
    name="go_emotions_onnx",
    model=onnx_model_path,
    custom_objects={"tokenizer": os.path.join(LOCAL_DIR, "tokenizer")},
    metadata={"labels": labels},
)
