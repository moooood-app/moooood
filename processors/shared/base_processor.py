import boto3
import time
import json
import os
import logging

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

class BaseProcessor:
    def __init__(self, queue_url_env, sns_topic_arn_env, max_messages_env, max_duration_seconds_env, aws_url_env):
        self.queue_url = os.getenv(queue_url_env)
        self.sns_topic_arn = os.getenv(sns_topic_arn_env)
        self.max_messages = int(os.getenv(max_messages_env, 10))
        self.max_duration_seconds = int(os.getenv(max_duration_seconds_env, 300))
        aws_url = os.getenv(aws_url_env)
        self.sqs = boto3.client('sqs', endpoint_url=f"http://{aws_url}")
        self.sns = boto3.client('sns', endpoint_url=f"http://{aws_url}")

    def process_message(self, entry_text):
        """To be implemented by subclasses."""
        raise NotImplementedError

    def send_sns_notification(self, result):
        try:
            self.sns.publish(
                TopicArn=self.sns_topic_arn,
                Message=json.dumps(result),
                Subject=f"{self.__class__.__name__} Result",
            )
            logger.info(f"Sent SNS notification: {result}")
        except Exception as e:
            logger.error(f"Error sending SNS notification: {e}")
            raise

    def poll_queue(self):
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
                            'result': self.process_message(payload.pop('content')),
                            'processor': self.__class__.__name__.lower().removesuffix('processor'),
                        }
                        payload.update(result)
                        self.send_sns_notification(payload)
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
                time.sleep(5)  # Backoff before retrying
