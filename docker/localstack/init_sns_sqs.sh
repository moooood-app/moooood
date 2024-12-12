#!/bin/bash
# SNS topics
awslocal sns create-topic --name ${NEW_ENTRY_SNS_TOPIC}
awslocal sns create-topic --name ${POST_PROCESSING_SNS_TOPIC}

# Processors SQS queues and subscriptions
awslocal sqs create-queue --queue-name ${SENTIMENT_PROCESSOR_SQS_QUEUE}
awslocal sns subscribe --topic-arn "arn:aws:sns:${AWS_DEFAULT_REGION}:${AWS_ACCOUNT_ID}:${NEW_ENTRY_SNS_TOPIC}" --protocol sqs --notification-endpoint "arn:aws:sqs:${AWS_DEFAULT_REGION}:${AWS_ACCOUNT_ID}:${SENTIMENT_PROCESSOR_SQS_QUEUE}"
awslocal sqs create-queue --queue-name ${COMPLEXITY_PROCESSOR_SQS_QUEUE}
awslocal sns subscribe --topic-arn "arn:aws:sns:${AWS_DEFAULT_REGION}:${AWS_ACCOUNT_ID}:${NEW_ENTRY_SNS_TOPIC}" --protocol sqs --notification-endpoint "arn:aws:sqs:${AWS_DEFAULT_REGION}:${AWS_ACCOUNT_ID}:${COMPLEXITY_PROCESSOR_SQS_QUEUE}"
awslocal sqs create-queue --queue-name ${KEYWORDS_PROCESSOR_SQS_QUEUE}
awslocal sns subscribe --topic-arn "arn:aws:sns:${AWS_DEFAULT_REGION}:${AWS_ACCOUNT_ID}:${NEW_ENTRY_SNS_TOPIC}" --protocol sqs --notification-endpoint "arn:aws:sqs:${AWS_DEFAULT_REGION}:${AWS_ACCOUNT_ID}:${KEYWORDS_PROCESSOR_SQS_QUEUE}"
awslocal sqs create-queue --queue-name ${EMOTIONS_PROCESSOR_SQS_QUEUE}
awslocal sns subscribe --topic-arn "arn:aws:sns:${AWS_DEFAULT_REGION}:${AWS_ACCOUNT_ID}:${NEW_ENTRY_SNS_TOPIC}" --protocol sqs --notification-endpoint "arn:aws:sqs:${AWS_DEFAULT_REGION}:${AWS_ACCOUNT_ID}:${EMOTIONS_PROCESSOR_SQS_QUEUE}"

# Email SQS queue
awslocal sqs create-queue --queue-name ${MAILER_SQS_QUEUE}

# Post-processing SQS queue and subscription
awslocal sqs create-queue --queue-name ${POST_PROCESSING_SQS_QUEUE}
awslocal sns subscribe --topic-arn "arn:aws:sns:${AWS_DEFAULT_REGION}:${AWS_ACCOUNT_ID}:${POST_PROCESSING_SNS_TOPIC}" --protocol sqs --notification-endpoint "arn:aws:sqs:${AWS_DEFAULT_REGION}:${AWS_ACCOUNT_ID}:${POST_PROCESSING_SQS_QUEUE}"

# Awards
awslocal sns create-topic --name ${AWARD_EVENTS_SNS_TOPIC}
awslocal sqs create-queue --queue-name ${AWARD_EVENTS_SQS_QUEUE}
awslocal sns subscribe --topic-arn "arn:aws:sns:${AWS_DEFAULT_REGION}:${AWS_ACCOUNT_ID}:${AWARD_EVENTS_SNS_TOPIC}" --protocol sqs --notification-endpoint "arn:aws:sqs:${AWS_DEFAULT_REGION}:${AWS_ACCOUNT_ID}:${AWARD_EVENTS_SQS_QUEUE}"
