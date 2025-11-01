# Docker Build and Push Guide

This guide explains how to build and push Docker images for ElinksBoard.

## Prerequisites

1. **Docker installed and running**
   ```bash
   docker --version
   ```

2. **Docker Hub account** (for pushing images)
   - Create account at https://hub.docker.com
   - Login: `docker login`

3. **Permissions** (if pushing to elinks/elinks)
   - You need to be a member of the `elinks` organization on Docker Hub
   - Or use your own namespace: `yourusername/elinks`

## Quick Start

### Build Only

```bash
# Build with default tag (latest)
./build-docker.sh

# Build with custom tag
./build-docker.sh v1.0.0

# Build with specific branch
BRANCH_NAME=develop ./build-docker.sh v1.0.0
```

### Build and Push

```bash
# Build and push to Docker Hub
./build-docker.sh v1.0.0 --push

# Build and push with specific branch
BRANCH_NAME=develop ./build-docker.sh v1.0.0 --push
```

## Manual Build Commands

If you prefer to run Docker commands directly:

### 1. Build the Image

```bash
docker build \
  --build-arg CACHEBUST=$(date +%s) \
  --build-arg REPO_URL="https://github.com/ElinksTeam/ElinksBoard.git" \
  --build-arg BRANCH_NAME="master" \
  -t elinks/elinks:tagname \
  -f Dockerfile \
  .
```

**Build Arguments:**
- `CACHEBUST`: Forces fresh git clone (uses current timestamp)
- `REPO_URL`: Git repository URL
- `BRANCH_NAME`: Git branch to clone

### 2. Tag the Image (Optional)

```bash
# Tag with version
docker tag elinks/elinks:tagname elinks/elinks:v1.0.0

# Tag as latest
docker tag elinks/elinks:tagname elinks/elinks:latest
```

### 3. Push to Docker Hub

```bash
# Login first (if not already logged in)
docker login

# Push specific tag
docker push elinks/elinks:tagname

# Push all tags
docker push elinks/elinks --all-tags
```

## Multi-Architecture Builds

To build for multiple architectures (amd64, arm64):

```bash
# Create and use buildx builder
docker buildx create --name multiarch --use

# Build and push for multiple platforms
docker buildx build \
  --platform linux/amd64,linux/arm64 \
  --build-arg CACHEBUST=$(date +%s) \
  --build-arg REPO_URL="https://github.com/ElinksTeam/ElinksBoard.git" \
  --build-arg BRANCH_NAME="master" \
  -t elinks/elinks:tagname \
  --push \
  .
```

## Testing the Image

### Run Locally

```bash
# Run with default settings
docker run -d -p 7001:7001 --name elinksboard elinks/elinks:tagname

# Run with custom environment
docker run -d \
  -p 7001:7001 \
  -e ENABLE_WEB=true \
  -e ENABLE_HORIZON=true \
  -e ENABLE_REDIS=false \
  --name elinksboard \
  elinks/elinks:tagname

# View logs
docker logs -f elinksboard

# Stop and remove
docker stop elinksboard && docker rm elinksboard
```

### Using Docker Compose

```bash
# Copy sample compose file
cp compose.sample.yaml docker-compose.yml

# Edit docker-compose.yml to use your image
# Change: image: ghcr.io/cedar2025/xboard:new
# To:     image: elinks/elinks:tagname

# Start services
docker-compose up -d

# View logs
docker-compose logs -f

# Stop services
docker-compose down
```

## Troubleshooting

### Docker Not Found in Dev Container

If you're in a Gitpod/dev container and Docker is not available:

1. **Enable Docker-in-Docker** in `.devcontainer/devcontainer.json`:
   ```json
   "features": {
     "ghcr.io/devcontainers/features/docker-in-docker": {
       "moby": true,
       "dockerDashComposeVersion": "v2"
     }
   }
   ```

2. **Rebuild the container**:
   - In VS Code: Command Palette → "Dev Containers: Rebuild Container"
   - Or restart your Gitpod workspace

### Build Fails

1. **Check Dockerfile syntax**:
   ```bash
   docker build --no-cache -t test .
   ```

2. **Verify build arguments**:
   ```bash
   docker build --progress=plain --no-cache \
     --build-arg CACHEBUST=$(date +%s) \
     --build-arg REPO_URL="https://github.com/ElinksTeam/ElinksBoard.git" \
     --build-arg BRANCH_NAME="master" \
     -t elinks/elinks:test .
   ```

3. **Check .dockerignore**:
   - Ensure necessary files aren't excluded
   - The `.docker/` directory must be included

### Push Fails

1. **Login to Docker Hub**:
   ```bash
   docker login
   ```

2. **Check permissions**:
   - Verify you have push access to the repository
   - Use your own namespace if needed: `yourusername/elinks`

3. **Check image name**:
   ```bash
   docker images | grep elinks
   ```

## Image Details

**Base Image:** `phpswoole/swoole:php8.2-alpine`

**Installed Extensions:**
- pcntl
- bcmath
- zip
- redis

**Additional Packages:**
- shadow, sqlite, mysql-client, mysql-dev, mariadb-connector-c
- git, patch
- supervisor, redis

**Exposed Port:** 7001

**Default Environment:**
- `ENABLE_WEB=true`
- `ENABLE_HORIZON=true`
- `ENABLE_REDIS=false`

## CI/CD Integration

### GitHub Actions Example

```yaml
name: Build and Push Docker Image

on:
  push:
    tags:
      - 'v*'

jobs:
  build:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      
      - name: Login to Docker Hub
        uses: docker/login-action@v2
        with:
          username: ${{ secrets.DOCKER_USERNAME }}
          password: ${{ secrets.DOCKER_PASSWORD }}
      
      - name: Extract tag name
        id: tag
        run: echo "TAG=${GITHUB_REF#refs/tags/}" >> $GITHUB_OUTPUT
      
      - name: Build and push
        run: |
          docker build \
            --build-arg CACHEBUST=$(date +%s) \
            --build-arg REPO_URL="https://github.com/ElinksTeam/ElinksBoard.git" \
            --build-arg BRANCH_NAME="${{ steps.tag.outputs.TAG }}" \
            -t elinks/elinks:${{ steps.tag.outputs.TAG }} \
            -t elinks/elinks:latest \
            .
          docker push elinks/elinks:${{ steps.tag.outputs.TAG }}
          docker push elinks/elinks:latest
```

## Additional Resources

- [Docker Documentation](https://docs.docker.com/)
- [Docker Hub](https://hub.docker.com/)
- [Dockerfile Best Practices](https://docs.docker.com/develop/develop-images/dockerfile_best-practices/)
- [Multi-platform Builds](https://docs.docker.com/build/building/multi-platform/)
