#!/bin/bash

# Docker Build and Push Script for ElinksBoard
# Usage: ./build-docker.sh [tag] [--push]

set -e

# Default values
TAG="${1:-latest}"
PUSH="${2}"
IMAGE_NAME="elinks/elinks"
REPO_URL="https://github.com/ElinksTeam/ElinksBoard.git"
BRANCH_NAME="${BRANCH_NAME:-master}"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}=== ElinksBoard Docker Build ===${NC}"
echo "Image: ${IMAGE_NAME}:${TAG}"
echo "Branch: ${BRANCH_NAME}"
echo "Repository: ${REPO_URL}"
echo ""

# Check if Docker is available
if ! command -v docker &> /dev/null; then
    echo -e "${RED}Error: Docker is not installed or not in PATH${NC}"
    exit 1
fi

# Build the image
echo -e "${YELLOW}Building Docker image...${NC}"
docker build \
    --build-arg CACHEBUST=$(date +%s) \
    --build-arg REPO_URL="${REPO_URL}" \
    --build-arg BRANCH_NAME="${BRANCH_NAME}" \
    -t "${IMAGE_NAME}:${TAG}" \
    -f Dockerfile \
    .

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Build successful!${NC}"
    echo "Image: ${IMAGE_NAME}:${TAG}"
else
    echo -e "${RED}❌ Build failed!${NC}"
    exit 1
fi

# Push if requested
if [ "$PUSH" == "--push" ]; then
    echo ""
    echo -e "${YELLOW}Pushing image to registry...${NC}"
    
    # Check if logged in to Docker Hub
    if ! docker info | grep -q "Username"; then
        echo -e "${YELLOW}Warning: You may not be logged in to Docker Hub${NC}"
        echo "Run: docker login"
        read -p "Continue anyway? (y/N) " -n 1 -r
        echo
        if [[ ! $REPLY =~ ^[Yy]$ ]]; then
            exit 1
        fi
    fi
    
    docker push "${IMAGE_NAME}:${TAG}"
    
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✅ Push successful!${NC}"
    else
        echo -e "${RED}❌ Push failed!${NC}"
        exit 1
    fi
fi

echo ""
echo -e "${GREEN}=== Done ===${NC}"
echo "To run the container:"
echo "  docker run -d -p 7001:7001 ${IMAGE_NAME}:${TAG}"
echo ""
echo "To push the image:"
echo "  docker push ${IMAGE_NAME}:${TAG}"
