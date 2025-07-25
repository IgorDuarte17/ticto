# Ticto - API
# =============================================

# Configurações
DOCKER_COMPOSE = docker-compose -f .setup/docker-compose.yml
APP_CONTAINER = app
DB_CONTAINER = mysql
REDIS_CONTAINER = redis
NGINX_CONTAINER = nginx

# Colors for output
GREEN = \033[0;32m
YELLOW = \033[1;33m
BLUE = \033[0;34m
RED = \033[0;31m
NC = \033[0m # No Color

.PHONY: help install start stop restart status logs logs-app logs-db logs-nginx shell root-shell artisan migrate migrate-fresh seed migrate-seed tinker route-list cache-clear swagger-generate swagger-publish optimize composer composer-install composer-update test test-pest test-services test-unit test-feature test-coverage test-watch frontend frontend-build frontend-watch npm-install health db-shell redis-shell clean reset info ports

# Default target
.DEFAULT_GOAL := help

## 🚀 COMANDOS PRINCIPAIS

help: ## Mostra esta ajuda
	@echo ""
	@echo "$(BLUE)🚀 TICTO - API$(NC)"
	@echo "$(BLUE)===============$(NC)"
	@echo ""
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "$(GREEN)%-15s$(NC) %s\n", $$1, $$2}' $(MAKEFILE_LIST)
	@echo ""
	@echo "$(YELLOW) Informações do ambiente:$(NC)"
	@echo "  🌐 Aplicação: http://localhost:8000"
	@echo "  🗄️  MySQL: localhost:3306 (ticto/password)"
	@echo "  🔴 Redis: localhost:6379"
	@echo ""

install: ## Instala e configura o ambiente completo
	@echo "$(GREEN) Instalando ambiente...$(NC)"
	@cd .setup && ./scripts/setup.sh

start: ## Inicia todos os containers
	@echo "$(GREEN)▶️  Iniciando containers...$(NC)"
	@$(DOCKER_COMPOSE) up -d
	@echo "$(GREEN)✅ Containers iniciados!$(NC)"

stop: ## Para todos os containers
	@echo "$(YELLOW)⏹️  Parando containers...$(NC)"
	@$(DOCKER_COMPOSE) down
	@echo "$(YELLOW)✅ Containers parados!$(NC)"

restart: ## Reinicia todos os containers
	@echo "$(YELLOW)🔄 Reiniciando containers...$(NC)"
	@$(DOCKER_COMPOSE) restart
	@echo "$(GREEN)✅ Containers reiniciados!$(NC)"

## MONITORAMENTO

status: ## Mostra status dos containers
	@echo "$(BLUE) Status dos containers:$(NC)"
	@$(DOCKER_COMPOSE) ps

logs: ## Mostra logs dos containers
	@echo "$(BLUE) Logs dos containers:$(NC)"
	@$(DOCKER_COMPOSE) logs --tail=50 -f

logs-app: ## Logs específicos do app
	@$(DOCKER_COMPOSE) logs --tail=50 -f app

logs-db: ## Logs específicos do MySQL
	@$(DOCKER_COMPOSE) logs --tail=50 -f mysql

logs-nginx: ## Logs específicos do Nginx
	@$(DOCKER_COMPOSE) logs --tail=50 -f nginx

## DESENVOLVIMENTO

shell: ## Acessa shell do container PHP
	@echo "$(BLUE) Acessando container PHP...$(NC)"
	@$(DOCKER_COMPOSE) exec $(APP_CONTAINER) bash

root-shell: ## Acessa shell como root
	@echo "$(BLUE) Acessando container como root...$(NC)"
	@$(DOCKER_COMPOSE) exec --user root $(APP_CONTAINER) bash

## LARAVEL

artisan: ## Executa comando Artisan (uso: make artisan cmd="migrate")
	@$(DOCKER_COMPOSE) exec $(APP_CONTAINER) php artisan $(cmd)

migrate: ## Executa migrações
	@echo "$(GREEN) Executando migrações...$(NC)"
	@$(DOCKER_COMPOSE) exec $(APP_CONTAINER) php artisan migrate

migrate-fresh: ## Recria banco e executa migrações
	@echo "$(YELLOW) Recriando banco de dados...$(NC)"
	@$(DOCKER_COMPOSE) exec $(APP_CONTAINER) php artisan migrate:fresh

seed: ## Executa seeders
	@echo "$(GREEN) Executando seeders...$(NC)"
	@$(DOCKER_COMPOSE) exec $(APP_CONTAINER) php artisan db:seed

migrate-seed: ## Migra e executa seeders
	@echo "$(GREEN) Executando migrações com seeders...$(NC)"
	@$(DOCKER_COMPOSE) exec $(APP_CONTAINER) php artisan migrate:fresh --seed

tinker: ## Abre Laravel Tinker
	@echo "$(BLUE) Abrindo Laravel Tinker...$(NC)"
	@$(DOCKER_COMPOSE) exec $(APP_CONTAINER) php artisan tinker

route-list: ## Lista todas as rotas
	@echo "$(BLUE) Listando rotas:$(NC)"
	@$(DOCKER_COMPOSE) exec $(APP_CONTAINER) php artisan route:list

cache-clear: ## Limpa todos os caches
	@echo "$(YELLOW) Limpando caches...$(NC)"
	@$(DOCKER_COMPOSE) exec $(APP_CONTAINER) php artisan cache:clear
	@$(DOCKER_COMPOSE) exec $(APP_CONTAINER) php artisan config:clear
	@$(DOCKER_COMPOSE) exec $(APP_CONTAINER) php artisan route:clear
	@$(DOCKER_COMPOSE) exec $(APP_CONTAINER) php artisan view:clear

## DOCUMENTAÇÃO API

swagger-generate: ## Gera documentação Swagger
	@echo "$(BLUE) Gerando documentação Swagger...$(NC)"
	@$(DOCKER_COMPOSE) exec $(APP_CONTAINER) php artisan l5-swagger:generate
	@echo "$(GREEN)✅ Documentação gerada! Acesse: http://localhost:8080/api/documentation$(NC)"

swagger-publish: ## Publica configuração do Swagger
	@echo "$(BLUE) Publicando configuração Swagger...$(NC)"
	@$(DOCKER_COMPOSE) exec $(APP_CONTAINER) php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider"

## COMPOSER

composer: ## Executa comando Composer (uso: make composer cmd="install")
	@$(DOCKER_COMPOSE) exec $(APP_CONTAINER) composer $(cmd)

composer-install: ## Instala dependências
	@echo "$(GREEN) Instalando dependências...$(NC)"
	@$(DOCKER_COMPOSE) exec $(APP_CONTAINER) composer install

composer-update: ## Atualiza dependências
	@echo "$(YELLOW) Atualizando dependências...$(NC)"
	@$(DOCKER_COMPOSE) exec $(APP_CONTAINER) composer update

## TESTES

test: ## Executa testes (PHPUnit padrão)
	@echo "$(BLUE) Executando testes PHPUnit...$(NC)"
	@$(DOCKER_COMPOSE) exec $(APP_CONTAINER) php artisan test

test-pest: ## Executa todos os testes com Pest
	@echo "$(BLUE) Executando todos os testes com Pest...$(NC)"
	@$(DOCKER_COMPOSE) exec $(APP_CONTAINER) composer test:pest

test-services: ## Executa testes das Services
	@echo "$(BLUE) Executando testes das Services...$(NC)"
	@$(DOCKER_COMPOSE) exec $(APP_CONTAINER) composer test:services

test-unit: ## Executa todos os testes unitários
	@echo "$(BLUE) Executando testes unitários...$(NC)"
	@$(DOCKER_COMPOSE) exec $(APP_CONTAINER) composer test:unit

test-feature: ## Executa testes de funcionalidades
	@echo "$(BLUE) Executando testes de feature...$(NC)"
	@$(DOCKER_COMPOSE) exec $(APP_CONTAINER) composer test:feature

test-coverage: ## Executa testes com cobertura
	@echo "$(BLUE) Executando testes com cobertura...$(NC)"
	@$(DOCKER_COMPOSE) exec $(APP_CONTAINER) ./vendor/bin/pest --coverage

test-watch: ## Executa testes em modo watch (observação)
	@echo "$(BLUE)👀 Executando testes em modo watch...$(NC)"
	@echo "$(YELLOW)💡 Pressione Ctrl+C para parar$(NC)"
	@$(DOCKER_COMPOSE) exec $(APP_CONTAINER) ./vendor/bin/pest --watch

## FRONTEND

npm-install: ## Instala dependências do Node.js
	@echo "$(BLUE)📦 Instalando dependências do Node.js...$(NC)"
	@npm install

frontend: ## Inicia servidor de desenvolvimento (Vite)
	@echo "$(BLUE)🚀 Iniciando servidor de desenvolvimento...$(NC)"
	@npm run dev

frontend-build: ## Compila assets para produção
	@echo "$(BLUE)🏗️  Compilando assets para produção...$(NC)"
	@npm run build

frontend-watch: ## Observa mudanças nos arquivos frontend
	@echo "$(BLUE)👀 Observando mudanças no frontend...$(NC)"
	@npm run dev

## FERRAMENTAS

health: ## Verifica saúde da API
	@echo "$(BLUE) Verificando saúde da API...$(NC)"
	@curl -s http://localhost:8000/api/health | python3 -m json.tool || curl http://localhost:8000/api/health

db-shell: ## Acessa MySQL shell
	@echo "$(BLUE) Acessando MySQL...$(NC)"
	@$(DOCKER_COMPOSE) exec $(DB_CONTAINER) mysql -u ticto -ppassword ticto

redis-shell: ## Acessa Redis shell
	@echo "$(BLUE) Acessando Redis...$(NC)"
	@$(DOCKER_COMPOSE) exec $(REDIS_CONTAINER) redis-cli

## LIMPEZA

clean: ## Para containers e remove volumes
	@echo "$(YELLOW) Limpando ambiente...$(NC)"
	@$(DOCKER_COMPOSE) down -v
	@echo "$(GREEN) Ambiente limpo!$(NC)"

reset: ## Reset completo (remove tudo e reinstala)
	@echo "$(RED)⚠️  ATENÇÃO: Isso vai apagar TODOS os dados!$(NC)"
	@read -p "Digite 'CONFIRMO' para continuar: " confirm; \
	if [ "$$confirm" = "CONFIRMO" ]; then \
		echo "$(YELLOW)🔄 Fazendo reset completo...$(NC)"; \
		$(DOCKER_COMPOSE) down -v --rmi all; \
		docker system prune -f; \
		echo "$(GREEN)✅ Reset completo realizado!$(NC)"; \
		echo "$(BLUE)📦 Execute 'make install' para reinstalar$(NC)"; \
	else \
		echo "$(GREEN)✅ Operação cancelada$(NC)"; \
	fi

## INFORMAÇÕES

info: ## Mostra informações do ambiente
	@echo "$(BLUE)ℹ️  Informações do Ambiente$(NC)"
	@echo "$(BLUE)========================$(NC)"
	@echo "🌐 $(GREEN)Aplicação:$(NC) http://localhost:8000"
	@echo "🏥 $(GREEN)Health Check:$(NC) http://localhost:8000/api/health"
	@echo "🗄️  $(GREEN)MySQL:$(NC) localhost:3306 (ticto/password)"
	@echo "🔴 $(GREEN)Redis:$(NC) localhost:6379"
	@echo ""
	@echo "📋 $(YELLOW)Versões:$(NC)"
	@$(DOCKER_COMPOSE) exec $(APP_CONTAINER) php --version | head -1
	@$(DOCKER_COMPOSE) exec $(APP_CONTAINER) php artisan --version
	@$(DOCKER_COMPOSE) exec $(DB_CONTAINER) mysql --version | head -1
	@echo ""

ports: ## Mostra portas em uso
	@echo "$(BLUE) Portas em uso:$(NC)"
	@echo "8000  -> Nginx (Aplicação)"
	@echo "3306  -> MySQL"
	@echo "6379  -> Redis"