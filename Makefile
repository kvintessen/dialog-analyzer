COMPOSE = docker compose -f docker/docker-compose.yml
PHP = $(COMPOSE) exec php

.DEFAULT_GOAL := help

.PHONY: help setup env up down build install key migrate npm-build dev test sh artisan

help: ## Список команд
	@grep -E '^[a-zA-Z_-]+:.*## ' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*## "}; {printf "\033[36m%-12s\033[0m %s\n", $$1, $$2}'

setup: env up install key migrate npm-build ## Установка с нуля: .env, контейнеры, зависимости, БД, фронтенд

env: ## Скопировать .env/.env.testing/docker/.env из example, если их ещё нет
	[ -f .env ] || cp .env.example .env
	[ -f .env.testing ] || cp .env.testing.example .env.testing
	[ -f docker/.env ] || cp docker/.env.example docker/.env

up: ## Поднять контейнеры (собрать при необходимости)
	$(COMPOSE) up -d --build

down: ## Остановить и удалить контейнеры
	$(COMPOSE) down

install: ## composer install + npm install
	$(PHP) composer install
	$(PHP) npm install

key: ## Сгенерировать APP_KEY
	$(PHP) php artisan key:generate

migrate: ## Прогнать миграции с сидами
	$(PHP) php artisan migrate --seed

npm-build: ## Собрать фронтенд (production build)
	$(PHP) npm run build

dev: ## Vite dev-сервер с HMR (внутри контейнера, наружу не проброшен)
	$(PHP) npm run dev

test: ## Тесты; make test FILTER=SomeTest — точечный запуск
	$(PHP) php artisan test $(if $(FILTER),--filter=$(FILTER))

sh: ## Шелл внутри php-контейнера
	$(PHP) bash

artisan: ## make artisan CMD="migrate:status"
	$(PHP) php artisan $(CMD)
