#!/bin/bash

# Stop the script if any command fails
set -e

# Get the directory where the script is located
SCRIPT_DIR="$(dirname "$0")"
JOB_DEFINITION="$SCRIPT_DIR/../cloud-run/job.yaml"

# Load environment variables from .env file if it exists
if [ -f "$SCRIPT_DIR/../../.env" ]; then
    export $(grep -v '^#' "$SCRIPT_DIR/../../.env" | xargs)
fi

# Get the RUN_MODE from the first argument
RUN_MODE=$1

# Define command case RUN_MODE=command
RUN_MODE_COMMAND="sleep 5 \&\& php artisan migrate --seed -vvv"

# Copy the template to a new job.yaml file
cp "$SCRIPT_DIR/../cloud-run/job.template.yaml" "$JOB_DEFINITION"

# Replace placeholders
sed -i "s|<RUN_MODE>|$RUN_MODE|g" "$JOB_DEFINITION"
sed -i "s|<RUN_MODE_COMMAND>|$RUN_MODE_COMMAND|g" "$JOB_DEFINITION"
sed -i "s|<GCP_PROJECT_NUMBER>|$GCP_PROJECT_NUMBER|g" "$JOB_DEFINITION"
sed -i "s|<GCP_REGION>|$GCP_REGION|g" "$JOB_DEFINITION"
sed -i "s|<GCP_CLOUD_RUN_SERVICE>|$GCP_CLOUD_RUN_SERVICE|g" "$JOB_DEFINITION"
sed -i "s|<DOCKER_IMAGE>|$GCP_AR_REPOSITORY/app:latest|g" "$JOB_DEFINITION"

# Deploy the job using the modified job.yaml
gcloud beta run jobs replace "$JOB_DEFINITION" --region "$GCP_REGION" --project "$GCP_PROJECT_ID"

# Execute the job after replacing it
gcloud beta run jobs execute "$GCP_CLOUD_RUN_SERVICE-task-runner" --region "$GCP_REGION" --project "$GCP_PROJECT_ID"

# Clean up the temporary job.yaml file
rm "$SCRIPT_DIR/../cloud-run/job.yaml"