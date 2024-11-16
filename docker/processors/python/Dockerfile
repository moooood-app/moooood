FROM python:3.9-slim as base

RUN apt-get update && \
    apt-get install -y gcc libffi-dev libssl-dev supervisor && \
    apt-get clean

ARG PROCESSOR_PATH

COPY ${PROCESSOR_PATH}/requirements.txt /app/
RUN pip install --no-cache-dir -r /app/requirements.txt

COPY docker/processors/python/supervisor/supervisor.conf /etc/supervisor/conf.d/supervisor.conf

COPY processors/shared/base_processor.py /app/base_processor.py

WORKDIR /app

CMD ["supervisord", "-c", "/etc/supervisor/conf.d/supervisor.conf"]

# FROM base as prod

# COPY ${PROCESSOR_PATH}/processor.py /app/processor.py
