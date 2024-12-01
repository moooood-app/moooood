import boto3
from typing import Union, List, Dict, Any
import json
import logging

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

class SNSNotifier:
    def __init__(self, topic_arn: str, aws_url: str):
        self.topic_arn = topic_arn
        self.sns = boto3.client('sns', endpoint_url=f"http://{aws_url}")

    def publish(self, data: Union[Dict[str, Any], List[Any]]) -> None:
        try:
            self.sns.publish(
                TopicArn=self.topic_arn,
                Message=json.dumps(data),
                Subject=f"{data['processor']} Result",
            )
            logger.info(f"Sent SNS notification: {data}")
        except Exception as e:
            logger.error(f"Error sending SNS notification: {e}")
            raise
