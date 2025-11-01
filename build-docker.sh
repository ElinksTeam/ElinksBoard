#!/bin/bash

# Docker Build and Push Script for ElinksBoard
# Usage: ./build-docker.sh [tag] [--push] [--registry=REGISTRY]
# 
# Examples:
#   ./build-docker.sh v1.0.0                                    # Build only
#   ./build-docker.sh v1.0.0 --push                            # Build and push to Docker Hub
#   ./build-docker.sh v1.0.0 --push --registry=ghcr           # Build and push to GitHub Container Registry
#   ./build-docker.sh v1.0.0 --push --registry=both           # Build and push to both registries

set -e

# Default values
TAG="${1:-latest}"
PUSH=""
REGISTRY="docker"  # docker, ghcr, or both
REPO_URL="https://github.com/ElinksTeam/ElinksBoard.git"
BRANCH_NAME="${BRANCH_NAME:-master}"

# Parse arguments
shift || true
while [[ $# -gt 0 ]]; do
    case $1 in
        --push)
            PUSH="true"
            shift
            ;;
        --registry=*)
            REGISTRY="${1#*=}"
            shift
            ;;
        *)
            shift
            ;;
    esac
done

# Set image names based on registry
DOCKER_IMAGE="elinksboard/elinksboard"
GHCR_IMAGE="ghcr.io/elinksteam/elinksboard"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}=== ElinksBoard Docker Build ===${NC}"
echo "Tag: ${TAG}"
echo "Branch: ${BRANCH_NAME}"
echo "Repository: ${REPO_URL}"
echo "Registry: ${REGISTRY}"
echo ""

# Check if Docker is available
if ! command -v docker &> /dev/null; then
    echo -e "${RED}Error: Docker is not installed or not in PATH${NC}"
    exit 1
fi

# Determine which images to build
BUILD_IMAGES=()
if [ "$REGISTRY" == "docker" ] || [ "$REGISTRY" == "both" ]; then
    BUILD_IMAGES+=("${DOCKER_IMAGE}:${TAG}")
fi
if [ "$REGISTRY" == "ghcr" ] || [ "$REGISTRY" == "both" ]; then
    BUILD_IMAGES+=("${GHCR_IMAGE}:${TAG}")
fi

# Build the image(s)
echo -e "${YELLOW}Building Docker image(s)...${NC}"
for IMAGE in "${BUILD_IMAGES[@]}"; do
    echo "Building: ${IMAGE}"
done

# Build with all tags
TAG_ARGS=""
for IMAGE in "${BUILD_IMAGES[@]}"; do
    TAG_ARGS="${TAG_ARGS} -t ${IMAGE}"
done

docker build \
    --build-arg CACHEBUST=$(date +%s) \
    --build-arg REPO_URL="${REPO_URL}" \
    --build-arg BRANCH_NAME="${BRANCH_NAME}" \
    ${TAG_ARGS} \
    -f Dockerfile \
    .

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Build successful!${NC}"
    for IMAGE in "${BUILD_IMAGES[@]}"; do
        echo "  - ${IMAGE}"
    done
else
    echo -e "${RED}❌ Build failed!${NC}"
    exit 1
fi

# Push if requested
if [ "$PUSH" == "true" ]; then
    echo ""
    echo -e "${YELLOW}Pushing image(s) to registry...${NC}"
    
    # Push to Docker Hub
    if [ "$REGISTRY" == "docker" ] || [ "$REGISTRY" == "both" ]; then
        echo ""
        echo "Pushing to Docker Hub..."
        if ! docker info 2>/dev/null | grep -q "Username"; then
            echo -e "${YELLOW}Warning: You may not be logged in to Docker Hub${NC}"
            echo "Run: docker login"
        fi
        
        docker push "${DOCKER_IMAGE}:${TAG}"
        if [ $? -eq 0 ]; then
            echo -e "${GREEN}✅ Pushed to Docker Hub: ${DOCKER_IMAGE}:${TAG}${NC}"
        else
            echo -e "${RED}❌ Failed to push to Docker Hub${NC}"
        fi
    fi
    
    # Push to GitHub Container Registry
    if [ "$REGISTRY" == "ghcr" ] || [ "$REGISTRY" == "both" ]; then
        echo ""
        echo "Pushing to GitHub Container Registry..."
        echo -e "${YELLOW}Note: Make sure you're logged in with: echo \$CR_PAT | docker login ghcr.io -u USERNAME --password-stdin${NC}"
        
        docker push "${GHCR_IMAGE}:${TAG}"
        if [ $? -eq 0 ]; then
            echo -e "${GREEN}✅ Pushed to GHCR: ${GHCR_IMAGE}:${TAG}${NC}"
        else
            echo -e "${RED}❌ Failed to push to GHCR${NC}"
        fi
    fi
fi

echo ""
echo -e "${GREEN}=== Done ===${NC}"
echo ""
echo "Built images:"
for IMAGE in "${BUILD_IMAGES[@]}"; do
    echo "  - ${IMAGE}"
done
echo ""
echo "To run the container:"
if [ "$REGISTRY" == "ghcr" ]; then
    echo "  docker run -d -p 7001:7001 ${GHCR_IMAGE}:${TAG}"
else
    echo "  docker run -d -p 7001:7001 ${DOCKER_IMAGE}:${TAG}"
fi
echo ""
if [ "$PUSH" != "true" ]; then
    echo "To push the image(s):"
    echo "  ./build-docker.sh ${TAG} --push --registry=${REGISTRY}"
fi
