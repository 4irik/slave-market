#!/usr/bin/make

USER_ID := $(shell id -u)
GROUP_ID := $(shell id -g)
APP_CONTAINER_TAG=pvbogdanov/slave-market:7.4
APP_IMAGE_NAME=slave_market

docker_bin := $(shell command -v docker 2> /dev/null)

# This will output the help for each task. thanks to https://marmelab.com/blog/2016/02/29/auto-documented-makefile.html
help: ## Показывает эту справочную информацию
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "  \033[32m%-15s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

build: ## Сборка контейнера
	$(docker_bin) build -t "$(APP_CONTAINER_TAG)" .

up: ## Запускаем контейнер
	if test "$$(docker ps | grep -c $(APP_IMAGE_NAME))" -ne "1"; then \
		docker run --rm --name $(APP_IMAGE_NAME) -u $(USER_ID):$(GROUP_ID) --expose=9000 -v "$$PWD":/app -w /app $(APP_CONTAINER_TAG) /bin/bash -c "tail -f /dev/null" & \
	fi

down: ## Останавливаем контейнер
	if test "$$(docker ps | grep -c $(APP_IMAGE_NAME))" -eq  "1"; then \
		  docker stop -t 1 $(APP_IMAGE_NAME); \
	fi

test: up ## Немного повеселимся!
	docker exec -it $(APP_IMAGE_NAME) ./vendor/bin/phpunit

install: up ## Установка зависимостей
	docker exec -it $(APP_IMAGE_NAME) composer install --no-interaction --ansi --no-suggest

shell: up ## Shell
	docker exec -it $(APP_IMAGE_NAME) /bin/bash