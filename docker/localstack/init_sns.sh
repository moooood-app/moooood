#!/bin/bash
for topic in ${SNS_TOPICS//,/ } ; do
    awslocal sns create-topic --name $topic
done
