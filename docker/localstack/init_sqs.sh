#!/bin/bash
for queue in ${SQS_QUEUES//,/ } ; do
    awslocal sqs create-queue --queue-name $queue
	awslocal sns subscribe --topic-arn "arn:aws:sns:${AWS_DEFAULT_REGION}:${AWS_ACCOUNT_ID}:${NEW_ENTRY_SNS_TOPIC}" --protocol sqs --notification-endpoint "arn:aws:sqs:us-east-1:000000000000:$queue"
done
