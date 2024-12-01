FROM python:3.9-slim AS base

RUN apt-get update && \
    apt-get install -y gcc g++ libffi-dev libssl-dev supervisor && \
    apt-get clean

ARG PROCESSOR_NAME

COPY processors/python/${PROCESSOR_NAME}_requirements.txt /app/
RUN pip install --no-cache-dir -r /app/${PROCESSOR_NAME}_requirements.txt

COPY docker/processors/python/supervisor/supervisor.conf /etc/supervisor/conf.d/supervisor.conf

WORKDIR /app

CMD ["supervisord", "-c", "/etc/supervisor/conf.d/supervisor.conf"]

# FROM base AS prod

# COPY processors/python/ /app/
