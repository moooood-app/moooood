import time
from typing import Any
from notifiers.notifier import SNSNotifier
from protocols.processor import Processor
import logging
import json
import os
import boto3

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

class MessageHandler:
    def __init__(
        self,
        processor: Processor,
        notifier: SNSNotifier,
        queue_url: str,
    ):
        self.processor = processor
        self.notifier = notifier
        self.queue_url = queue_url
        self.max_messages = int(os.getenv("MAX_MESSAGES", 10))
        self.max_duration_seconds = int(os.getenv("MAX_DURATION", 300))

        aws_url = os.getenv("AWS_URL")
        self.sqs = boto3.client('sqs', endpoint_url=f"http://{aws_url}")

    def handle(self) -> None:
        """Polls the queue and processes the data, then notifies SNS."""
        processed_count = 0
        start_time = time.time()
        while processed_count < self.max_messages:
            elapsed_time = time.time() - start_time
            if elapsed_time > self.max_duration_seconds:
                logger.info(f"Max duration {self.max_duration_seconds}s reached. Exiting...")
                break
            try:
                response = self.sqs.receive_message(
                    QueueUrl=self.queue_url,
                    MaxNumberOfMessages=1,
                    WaitTimeSeconds=5
                )

                if "Messages" not in response:
                    logger.info("No messages available, retrying...")
                    time.sleep(1)
                    continue

                for message in response["Messages"]:
                    try:
                        message_body = json.loads(message['Body'])
                        payload = json.loads(message_body.get('Message'))
                        logger.info(f"Received SQS message: {payload}")

                        result = {
                            'result': self.processor.process(payload.pop('content')),
                            'processor': self.processor.__class__.__name__.lower().removesuffix('processor'),
                        }

                        payload.update(result)

                        self.notifier.publish(payload)

                        self.sqs.delete_message(
                            QueueUrl=self.queue_url,
                            ReceiptHandle=message['ReceiptHandle']
                        )

                        processed_count += 1
                    except Exception as e:
                        processed_count += 1
                        logger.error(f"Error processing message: {e}")
            except Exception as e:
                processed_count += 1
                logger.error(f"Error in polling SQS: {e}")
                time.sleep(5)
