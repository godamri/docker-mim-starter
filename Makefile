# You have to define the values in {}
APP_NAME=docker-mim-starter
VERSION=0.0.1
DOCKER_REPO=godamri

PORT=8081
DIST_PORT=8081

PGSQL_PORT=5432

# DOCKER TASKS
# Build the container
build: ## Build the container
	docker build -t $(APP_NAME) .

dev: ## Build the container with development mode
	docker build -t $(APP_NAME) -f Dockerfile.dev .

build-no-cache: ## Build the container without caching
	docker build --no-cache -t $(APP_NAME) .

run: ## Run container
	docker run -i -t --rm --env-file ./appsrc/.env -p $(PORT):$(DIST_PORT) --name="$(APP_NAME)" $(APP_NAME)

run-dev: ## Run container dev volume
	docker run -i -t --rm --env-file ./appsrc/.env -v $(PWD)/etc/bash/.bash_history:/home/petugas/.ash_history -v $(PWD)/appsrc:/var/www/app -p $(PORT):$(DIST_PORT) --name="$(APP_NAME)" $(APP_NAME)

root: ## Run container with root
	docker run --workdir /root --user root -i -t --rm --env-file ./appsrc/.env -v $(PWD)/etc/bash:/home/petugas/bash_custom -v $(PWD)/appsrc:/var/www/app -p $(PORT):$(DIST_PORT) --name="$(APP_NAME)" $(APP_NAME)

login: ## access container's terminal
	docker exec -it $(APP_NAME) /bin/sh

up: build run ## Run container on port

up-dev: build run-dev

root-b: dev root

stop: ## Stop and remove a running container
	docker stop $(APP_NAME); docker rm $(APP_NAME)

release: build-no-cache publish

# Docker publish
publish: publish-latest publish-version ## Publish the `{version}` ans `latest` tagged containers to ECR

publish-latest: tag-latest ## Publish the `latest` taged container to ECR
	@echo 'publish latest to $(DOCKER_REPO)'
	docker push $(DOCKER_REPO)/$(APP_NAME):latest

publish-version: tag-version ## Publish the `{version}` taged container to ECR
	@echo 'publish $(VERSION) to $(DOCKER_REPO)'
	docker push $(DOCKER_REPO)/$(APP_NAME):$(VERSION)

# Docker tagging
tag: tag-latest tag-version ## Generate container tags for the `{version}` ans `latest` tags

tag-latest: ## Generate container `{version}` tag
	@echo 'create tag latest'
	docker tag $(APP_NAME) $(DOCKER_REPO)/$(APP_NAME):latest

tag-version: ## Generate container `latest` tag
	@echo 'create tag $(VERSION)'
	docker tag $(APP_NAME) $(DOCKER_REPO)/$(APP_NAME):$(VERSION)