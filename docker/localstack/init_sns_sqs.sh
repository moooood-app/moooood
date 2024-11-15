#!/bin/bash
# SNS topics
awslocal sns create-topic --name ${NEW_ENTRY_SNS_TOPIC}
awslocal sns create-topic --name ${POST_PROCESSING_SNS_TOPIC}

# Processors SQS queues and subscriptions
awslocal sqs create-queue --queue-name ${ASENT_PROCESSOR_SQS_QUEUE}
awslocal sns subscribe --topic-arn "arn:aws:sns:${AWS_DEFAULT_REGION}:${AWS_ACCOUNT_ID}:${NEW_ENTRY_SNS_TOPIC}" --protocol sqs --notification-endpoint "arn:aws:sqs:${AWS_DEFAULT_REGION}:${AWS_ACCOUNT_ID}:${ASENT_PROCESSOR_SQS_QUEUE}"
awslocal sqs create-queue --queue-name ${COMPLEXITY_PROCESSOR_SQS_QUEUE}
awslocal sns subscribe --topic-arn "arn:aws:sns:${AWS_DEFAULT_REGION}:${AWS_ACCOUNT_ID}:${NEW_ENTRY_SNS_TOPIC}" --protocol sqs --notification-endpoint "arn:aws:sqs:${AWS_DEFAULT_REGION}:${AWS_ACCOUNT_ID}:${COMPLEXITY_PROCESSOR_SQS_QUEUE}"

# Post-processing SQS queue and subscription
awslocal sqs create-queue --queue-name ${POST_PROCESSING_SQS_QUEUE}
awslocal sns subscribe --topic-arn "arn:aws:sns:${AWS_DEFAULT_REGION}:${AWS_ACCOUNT_ID}:${POST_PROCESSING_SNS_TOPIC}" --protocol sqs --notification-endpoint "arn:aws:sqs:${AWS_DEFAULT_REGION}:${AWS_ACCOUNT_ID}:${POST_PROCESSING_SQS_QUEUE}"
