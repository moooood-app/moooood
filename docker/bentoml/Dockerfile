FROM python:3.11-slim AS base

# Install curl
RUN apt-get update && apt-get install -y curl \
    && rm -rf /var/lib/apt/lists/*

RUN pip install --upgrade pip \
    && pip install bentoml uv

RUN pip install --upgrade pydantic

WORKDIR /workspace

FROM base AS dev

COPY docker/bentoml/entrypoint.sh /home/entrypoint.sh

ARG BENTO_NAME
COPY bentoml/${BENTO_NAME}/requirements.txt /workspace/requirements.txt

RUN pip install -r /workspace/requirements.txt

RUN chmod +x /home/entrypoint.sh

ENTRYPOINT ["/home/entrypoint.sh"]

FROM base AS builder

# Install dependencies
RUN apt-get update && apt-get install -y \
curl \
gnupg \
ca-certificates \
lsb-release \
&& curl -fsSL https://download.docker.com/linux/debian/gpg | gpg --dearmor -o /usr/share/keyrings/docker-archive-keyring.gpg \
&& echo "deb [arch=$(dpkg --print-architecture) signed-by=/usr/share/keyrings/docker-archive-keyring.gpg] \
https://download.docker.com/linux/debian $(lsb_release -cs) stable" | tee /etc/apt/sources.list.d/docker.list > /dev/null \
&& apt-get update \
&& apt-get install -y docker-ce-cli \
&& apt-get clean \
&& rm -rf /var/lib/apt/lists/*

RUN pip install -q uv
