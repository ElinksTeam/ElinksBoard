# Docker Build and Push Guide

This guide explains how to build and push Docker images for ElinksBoard to Docker Hub and GitHub Container Registry (GHCR).

## Prerequisites

1. **Docker installed and running**
   ```bash
   docker --version
   ```

2. **Registry Access**
   
   **For Docker Hub:**
   - Create account at https://hub.docker.com
   - Login: `docker login`
   - Permissions: Member of `elinksboard` organization or use your own namespace
   
   **For GitHub Container Registry:**
   - GitHub account with repository access
   - Personal Access Token (classic) with `write:packages` scope
   - Login: `echo $CR_PAT | docker login ghcr.io -u USERNAME --password-stdin`

## Quick Start

### Build Only

```bash
# Build with default tag (latest) for Docker Hub
./build-docker.sh

# Build with custom tag for Docker Hub
./build-docker.sh v1.0.0

# Build for GitHub Container Registry
./build-docker.sh v1.0.0 --registry=ghcr

# Build with specific branch
BRANCH_NAME=develop ./build-docker.sh v1.0.0
```

### Build and Push

```bash
# Build and push to Docker Hub
./build-docker.sh v1.0.0 --push --registry=docker

# Build and push to GitHub Container Registry
./build-docker.sh v1.0.0 --push --registry=ghcr

# Build and push to both registries
./build-docker.sh v1.0.0 --push --registry=both

# Build and push with specific branch
BRANCH_NAME=develop ./build-docker.sh v1.0.0 --push --registry=ghcr
```

## Manual Build Commands

If you prefer to run Docker commands directly:

### Docker Hub

#### 1. Build the Image

```bash
docker build \
  --build-arg CACHEBUST=$(date +%s) \
  --build-arg REPO_URL="https://github.com/ElinksTeam/ElinksBoard.git" \
  --build-arg BRANCH_NAME="master" \
  -t elinksboard/elinksboard:tagname \
  -f Dockerfile \
  .
```

#### 2. Tag the Image (Optional)

```bash
# Tag with version
docker tag elinksboard/elinksboard:tagname elinksboard/elinksboard:v1.0.0

# Tag as latest
docker tag elinksboard/elinksboard:tagname elinksboard/elinksboard:latest
```

#### 3. Push to Docker Hub

```bash
# Login first (if not already logged in)
docker login

# Push specific tag
docker push elinksboard/elinksboard:tagname

# Push all tags
docker push elinksboard/elinksboard --all-tags
```

### GitHub Container Registry

#### 1. Create Personal Access Token

1. Go to https://github.com/settings/tokens
2. Click "Generate new token (classic)"
3. Select scopes:
   - `write:packages` - Upload packages
   - `read:packages` - Download packages (included with write)
   - `delete:packages` - Delete packages (optional)
4. Generate and save the token

#### 2. Login to GHCR

```bash
export CR_PAT=YOUR_TOKEN
echo $CR_PAT | docker login ghcr.io -u YOUR_USERNAME --password-stdin
```

#### 3. Build the Image

```bash
docker build \
  --build-arg CACHEBUST=$(date +%s) \
  --build-arg REPO_URL="https://github.com/ElinksTeam/ElinksBoard.git" \
  --build-arg BRANCH_NAME="master" \
  -t ghcr.io/elinksteam/elinksboard:tagname \
  -f Dockerfile \
  .
```

#### 4. Push to GHCR

```bash
docker push ghcr.io/elinksteam/elinksboard:tagname
```

#### 5. Make Package Public (Optional)

By default, packages are private. To make public:
1. Go to https://github.com/orgs/ElinksTeam/packages
2. Click on your package
3. Click "Package settings"
4. Scroll to "Danger Zone"
5. Click "Change visibility" → "Public"

**Build Arguments:**
- `CACHEBUST`: Forces fresh git clone (uses current timestamp)
- `REPO_URL`: Git repository URL
- `BRANCH_NAME`: Git branch to clone

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
  -t elinksboard/elinksboard:tagname \
  --push \
  .
```

## Testing the Image

### Run Locally

```bash
# Run with default settings
docker run -d -p 7001:7001 --name elinksboard elinksboard/elinksboard:tagname

# Run with custom environment
docker run -d \
  -p 7001:7001 \
  -e ENABLE_WEB=true \
  -e ENABLE_HORIZON=true \
  -e ENABLE_REDIS=false \
  --name elinksboard \
  elinksboard/elinksboard:tagname

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
# To:     image: elinksboard/elinksboard:tagname

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
     -t elinksboard/elinksboard:test .
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
   - Use your own namespace if needed: `yourusername/elinksboard`

3. **Check image name**:
   ```bash
   docker images | grep elinksboard
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

### GitHub Actions - Docker Hub

```yaml
name: Build and Push to Docker Hub

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
            -t elinksboard/elinksboard:${{ steps.tag.outputs.TAG }} \
            -t elinksboard/elinksboard:latest \
            .
          docker push elinksboard/elinksboard:${{ steps.tag.outputs.TAG }}
          docker push elinksboard/elinksboard:latest
```

### GitHub Actions - GitHub Container Registry

```yaml
name: Build and Push to GHCR

on:
  push:
    tags:
      - 'v*'
    branches:
      - master

jobs:
  build:
    runs-on: ubuntu-latest
    permissions:
      contents: read
      packages: write
    
    steps:
      - uses: actions/checkout@v3
      
      - name: Login to GitHub Container Registry
        uses: docker/login-action@v2
        with:
          registry: ghcr.io
          username: ${{ github.actor }}
          password: ${{ secrets.GITHUB_TOKEN }}
      
      - name: Extract metadata
        id: meta
        uses: docker/metadata-action@v4
        with:
          images: ghcr.io/${{ github.repository_owner }}/elinksboard
          tags: |
            type=ref,event=branch
            type=ref,event=tag
            type=semver,pattern={{version}}
            type=semver,pattern={{major}}.{{minor}}
      
      - name: Build and push
        uses: docker/build-push-action@v4
        with:
          context: .
          push: true
          tags: ${{ steps.meta.outputs.tags }}
          labels: ${{ steps.meta.outputs.labels }}
          build-args: |
            CACHEBUST=${{ github.run_number }}
            REPO_URL=https://github.com/${{ github.repository }}.git
            BRANCH_NAME=${{ github.ref_name }}
```

### GitHub Actions - Both Registries

```yaml
name: Build and Push to Multiple Registries

on:
  push:
    tags:
      - 'v*'

jobs:
  build:
    runs-on: ubuntu-latest
    permissions:
      contents: read
      packages: write
    
    steps:
      - uses: actions/checkout@v3
      
      - name: Login to Docker Hub
        uses: docker/login-action@v2
        with:
          username: ${{ secrets.DOCKER_USERNAME }}
          password: ${{ secrets.DOCKER_PASSWORD }}
      
      - name: Login to GitHub Container Registry
        uses: docker/login-action@v2
        with:
          registry: ghcr.io
          username: ${{ github.actor }}
          password: ${{ secrets.GITHUB_TOKEN }}
      
      - name: Extract tag name
        id: tag
        run: echo "TAG=${GITHUB_REF#refs/tags/}" >> $GITHUB_OUTPUT
      
      - name: Build and push
        run: |
          docker build \
            --build-arg CACHEBUST=$(date +%s) \
            --build-arg REPO_URL="https://github.com/${{ github.repository }}.git" \
            --build-arg BRANCH_NAME="${{ steps.tag.outputs.TAG }}" \
            -t elinksboard/elinksboard:${{ steps.tag.outputs.TAG }} \
            -t elinksboard/elinksboard:latest \
            -t ghcr.io/${{ github.repository_owner }}/elinksboard:${{ steps.tag.outputs.TAG }} \
            -t ghcr.io/${{ github.repository_owner }}/elinksboard:latest \
            .
          
          # Push to Docker Hub
          docker push elinksboard/elinksboard:${{ steps.tag.outputs.TAG }}
          docker push elinksboard/elinksboard:latest
          
          # Push to GHCR
          docker push ghcr.io/${{ github.repository_owner }}/elinksboard:${{ steps.tag.outputs.TAG }}
          docker push ghcr.io/${{ github.repository_owner }}/elinksboard:latest
```

## Additional Resources

- [Docker Documentation](https://docs.docker.com/)
- [Docker Hub](https://hub.docker.com/)
- [Dockerfile Best Practices](https://docs.docker.com/develop/develop-images/dockerfile_best-practices/)
- [Multi-platform Builds](https://docs.docker.com/build/building/multi-platform/)
