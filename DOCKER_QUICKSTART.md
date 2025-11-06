# Docker Quick Start Guide

## 🚀 Quick Commands

### Using the Build Script (Recommended)

```bash
# 1. Build image with tag (Docker Hub)
./build-docker.sh v1.0.0

# 2. Build and push to Docker Hub
./build-docker.sh v1.0.0 --push --registry=docker

# 3. Build and push to GitHub Container Registry
./build-docker.sh v1.0.0 --push --registry=ghcr

# 4. Build and push to both registries
./build-docker.sh v1.0.0 --push --registry=both

# 5. Build from specific branch
BRANCH_NAME=develop ./build-docker.sh v1.0.0 --push --registry=ghcr
```

### Manual Commands

**Docker Hub:**
```bash
# Build
docker build \
  --build-arg CACHEBUST=$(date +%s) \
  --build-arg REPO_URL="https://github.com/ElinksTeam/ElinksBoard.git" \
  --build-arg BRANCH_NAME="master" \
  -t elinksboard/elinksboard:tagname \
  .

# Push
docker login
docker push elinksboard/elinksboard:tagname
```

**GitHub Container Registry:**
```bash
# Build
docker build \
  --build-arg CACHEBUST=$(date +%s) \
  --build-arg REPO_URL="https://github.com/ElinksTeam/ElinksBoard.git" \
  --build-arg BRANCH_NAME="master" \
  -t ghcr.io/elinksteam/elinksboard:tagname \
  .

# Login and Push
export CR_PAT=YOUR_TOKEN
echo $CR_PAT | docker login ghcr.io -u YOUR_USERNAME --password-stdin
docker push ghcr.io/elinksteam/elinksboard:tagname
```

## 📋 Prerequisites

1. **Enable Docker in Dev Container** (if using Gitpod/VS Code Dev Containers)
   - The `.devcontainer/devcontainer.json` has been updated with Docker-in-Docker
   - **Rebuild your container** to apply changes:
     - VS Code: `Ctrl+Shift+P` → "Dev Containers: Rebuild Container"
     - Gitpod: Restart workspace

2. **Login to Registry**
   
   **For Docker Hub:**
   ```bash
   docker login
   ```
   
   **For GitHub Container Registry:**
   ```bash
   # Create a Personal Access Token (classic) with write:packages scope
   # at https://github.com/settings/tokens
   export CR_PAT=YOUR_TOKEN
   echo $CR_PAT | docker login ghcr.io -u YOUR_USERNAME --password-stdin
   ```

## 🔧 Common Tasks

### Build for Multiple Architectures

```bash
docker buildx create --name multiarch --use
docker buildx build \
  --platform linux/amd64,linux/arm64 \
  --build-arg CACHEBUST=$(date +%s) \
  --build-arg REPO_URL="https://github.com/ElinksTeam/ElinksBoard.git" \
  --build-arg BRANCH_NAME="master" \
  -t elinksboard/elinksboard:tagname \
  --push \
  .
```

### Test Locally

```bash
docker run -d -p 7001:7001 --name test elinksboard/elinksboard:tagname
docker logs -f test
docker stop test && docker rm test
```

### Tag and Push Multiple Versions

```bash
# Build once
docker build -t elinksboard/elinksboard:v1.0.0 .

# Tag as latest
docker tag elinksboard/elinksboard:v1.0.0 elinksboard/elinksboard:latest

# Push both
docker push elinksboard/elinksboard:v1.0.0
docker push elinksboard/elinksboard:latest
```

## ⚠️ Important Notes

1. **Container Rebuild Required**: After updating `.devcontainer/devcontainer.json`, you must rebuild your dev container for Docker to be available.

2. **Build Arguments**: The Dockerfile requires these build args:
   - `CACHEBUST`: Timestamp to force fresh git clone
   - `REPO_URL`: Repository URL
   - `BRANCH_NAME`: Branch to build from

3. **Docker Hub Permissions**: Ensure you have push access to `elinksboard/elinksboard` or use your own namespace.

## 📚 Full Documentation

See [DOCKER_BUILD.md](DOCKER_BUILD.md) for complete documentation including:
- Detailed build options
- Troubleshooting guide
- CI/CD integration examples
- Docker Compose usage
