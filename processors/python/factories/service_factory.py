import boto3
from protocols import Processor
from notifiers import SNSNotifier
from queues import MessageHandler
import os

class ServiceFactory:
    @staticmethod
    def create(processor: Processor, queue_url: str) -> MessageHandler:
        topic_arn = os.getenv("POST_PROCESSING_SNS_ARN")

        sns_notifier = SNSNotifier(topic_arn=topic_arn, aws_url=os.getenv("AWS_URL"))

        return MessageHandler(
            processor=processor,
            notifier=sns_notifier,
            queue_url=queue_url,
        )
