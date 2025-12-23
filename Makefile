.PHONY: help build up down restart logs shell install migrate seed test psalm fix-cs check-cs deptrac quality

help: ## Show this help
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

build: ## Build Docker containers
	docker-compose build

up: ## Start Docker containers
	docker-compose up -d

down: ## Stop Docker containers
	docker-compose down

restart: ## Restart Docker containers
	docker-compose restart

logs: ## Show logs
	docker-compose logs -f

shell: ## Open shell in app container
	docker-compose exec app bash

install: ## Install dependencies
	docker-compose exec app composer install

key: ## Generate application key
	docker-compose exec app php artisan key:generate

migrate: ## Run migrations
	docker-compose exec app php artisan migrate

seed: ## Seed database
	docker-compose exec app php artisan db:seed

fresh: ## Fresh database with seed
	docker-compose exec app php artisan migrate:fresh --seed

process: ## Process pending messages
	docker-compose exec app php artisan messages:process

queue: ## Start queue worker manually
	docker-compose exec app php artisan queue:work

test: ## Run tests
	docker-compose exec app php artisan test

test-coverage: ## Run tests with coverage
	docker-compose exec app php artisan test --coverage

psalm: ## Run Psalm static analysis
	docker-compose exec app composer psalm

fix-cs: ## Fix code style
	docker-compose exec app composer cs-fix

check-cs: ## Check code style
	docker-compose exec app composer cs-check

deptrac: ## Check architecture dependencies
	docker-compose exec app composer deptrac

setup: build up install key migrate seed ## Complete setup
	@echo "Setup complete! Application is running on http://localhost:8081"

quality: psalm check-cs deptrac ## Run all quality checks

