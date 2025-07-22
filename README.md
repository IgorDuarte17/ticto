# Ticto - Laravel API

Este é um projeto Laravel configurado com Docker, PHP 8.4, MySQL 8.4 e Redis 7.

## 🚀 Início Rápido

### Primeira execução:
```bash
make install
```

### Comandos principais:
```bash
make help        # Ver todos os comandos disponíveis
make start       # Iniciar containers
make stop        # Parar containers
make status      # Ver status dos containers
make logs        # Ver logs
make shell       # Acessar container PHP
```

### Desenvolvimento Laravel:
```bash
make migrate                           # Executar migrações
make artisan cmd="route:list"         # Comandos Artisan
make make-controller name="ApiController"  # Criar controller
make make-model name="User"           # Criar model
make composer cmd="require package"   # Comandos Composer
make test                             # Executar testes
```

## 🐳 Serviços Docker

- **App (PHP 8.4-FPM)**: Aplicação Laravel
- **Nginx**: Servidor web proxy reverso
- **MySQL 8.4**: Banco de dados
- **Redis 7**: Cache e sessões

## 🌐 Acessos

- **Aplicação**: http://localhost:8000
- **MySQL**: localhost:3306
  - Database: `ticto`
  - Username: `ticto`
  - Password: `password`
- **Redis**: localhost:6379

## 📋 Comandos Úteis

### Makefile (recomendado)
Todos os comandos estão disponíveis através do Makefile:
```bash
make help          # Ver todos os comandos
make examples      # Ver exemplos de uso
make info          # Informações do ambiente
```

### Comandos Docker manuais (se necessário)
```bash
# Usar arquivo docker-compose específico
docker-compose -f .setup/docker-compose.yml up -d
docker-compose -f .setup/docker-compose.yml ps
docker-compose -f .setup/docker-compose.yml logs
```

### Laravel/Artisan
```bash
# Usando Makefile (recomendado)
make migrate
make artisan cmd="make:controller ApiController"
make artisan cmd="route:list"
make tinker

# Comando direto (alternativo)
docker-compose -f .setup/docker-compose.yml exec app php artisan [comando]
```

### Composer
```bash
# Usando Makefile (recomendado)
make composer-install
make composer cmd="require laravel/sanctum"
make composer-update

# Comando direto (alternativo)
docker-compose -f .setup/docker-compose.yml exec app composer [comando]
```

### Banco de dados
```bash
# Usando Makefile (recomendado)
make migrate
make migrate-fresh
make seed
make migrate-seed
make db-shell

# Comandos diretos (alternativos)
docker-compose -f .setup/docker-compose.yml exec app php artisan migrate
docker-compose -f .setup/docker-compose.yml exec app php artisan migrate:fresh --seed
```

### Cache e otimização
```bash
# Limpar todos os caches
docker-compose exec app php artisan optimize:clear

# Otimizar para produção
docker-compose exec app php artisan optimize

# Cache de configuração
docker-compose exec app php artisan config:cache

# Cache de rotas
docker-compose exec app php artisan route:cache
```

## 🔧 Configuração

As principais configurações estão no arquivo `.env` que é criado automaticamente pelo script de setup.

### Variáveis importantes:
- `APP_URL=http://localhost:8000`
- `DB_HOST=mysql`
- `DB_DATABASE=ticto`
- `DB_USERNAME=ticto`
- `DB_PASSWORD=password`
- `REDIS_HOST=redis`

## 📁 Estrutura do Projeto

```
.
├── app/                    # Código da aplicação Laravel
├── database/               # Migrações, seeders e factories
├── routes/                 # Definições de rotas
├── resources/              # Views, assets e lang
├── .setup/                 # Configurações Docker e scripts
│   ├── nginx/              # Configuração Nginx
│   ├── php/                # Configuração PHP e Dockerfile
│   └── scripts/            # Scripts de automação
├── Makefile               # Comandos centralizados de desenvolvimento
└── README.md              # Este arquivo
```

## 🛠️ Desenvolvimento

### Criando uma API simples

1. **Criar um controller:**
   ```bash
   docker-compose exec app php artisan make:controller Api/UserController --api
   ```

2. **Criar um model com migration:**
   ```bash
   docker-compose exec app php artisan make:model Task -m
   ```

3. **Adicionar rotas em `routes/api.php`:**
   ```php
   Route::apiResource('users', UserController::class);
   ```

4. **Executar migrations:**
   ```bash
   docker-compose exec app php artisan migrate
   ```

### Testando a API

```bash
# Listar rotas
docker-compose exec app php artisan route:list

# Testar com curl
curl http://localhost:8000/api/users
```

## 🚨 Troubleshooting

### Problemas de permissão
```bash
docker-compose exec app chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
docker-compose exec app chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
```

### Recriar containers
```bash
docker-compose down
docker-compose build --no-cache
docker-compose up -d
```

### Limpar dados do banco
```bash
docker-compose down
docker volume rm ticto_mysql_data ticto_redis_data
docker-compose up -d
# Aguardar ~15 segundos
docker-compose exec app php artisan migrate --force
```

### Erro de conexão com MySQL
- Aguarde alguns segundos após `docker-compose up -d`
- Verifique os logs: `docker-compose logs mysql`
- Verifique se as configurações do `.env` estão corretas

### Container não inicia
```bash
# Ver logs específicos
docker-compose logs [nome-do-container]

# Verificar status
docker-compose ps
```
