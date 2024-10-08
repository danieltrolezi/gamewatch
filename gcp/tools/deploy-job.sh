#!/bin/bash

if [ -f .env ]; then
    export $(cat .env | grep -v '^#' | xargs)
fi

# Stop the script if any command fails
set -e

# Get the RUN_MODE from the first argument
RUN_MODE=$1

# Get the directory where the script is located
SCRIPT_DIR="$(dirname "$0")"

# Copy the template to a new job.yaml file
cp "$SCRIPT_DIR/../job.template.yaml" "$SCRIPT_DIR/../job.yaml"

# Replace placeholders
sed -i "s|<RUN_MODE>|$RUN_MODE|" "$SCRIPT_DIR/../job.yaml"
sed -i "s|<GCP_PROJECT_NUMBER>|$GCP_PROJECT_NUMBER|" "$SCRIPT_DIR/../job.yaml"
sed -i "s|<GCP_REGION>|$GCP_REGION|" "$SCRIPT_DIR/../job.yaml"
sed -i "s|<GCP_CLOUD_RUN_SERVICE>|$GCP_CLOUD_RUN_SERVICE|" "$SCRIPT_DIR/../job.yaml"

# Deploy the job using the modified job.yaml
gcloud beta run jobs replace "$SCRIPT_DIR/../job.yaml" --region $GCP_REGION --project $GCP_PROJECT_ID

# Execute the job after replacing it
gcloud beta run jobs execute gamewatch-init-db --region $GCP_REGION --project $GCP_PROJECT_ID

# Clean up the temporary job.yaml file
rm "$SCRIPT_DIR/../job.yaml"


