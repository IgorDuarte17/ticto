#!/bin/bash

# Obter UID do usuário atual
USER_ID=$(id -u)

# Construir e executar os containers
echo "🚀 Construindo containers..."
docker-compose build --build-arg user=docker --build-arg uid=$USER_ID

echo "🐳 Iniciando containers..."
docker-compose up -d

echo "⏳ Aguardando containers iniciarem..."
sleep 10

# Verificar se o Laravel já está instalado
if [ ! -f "composer.json" ]; then
    echo "📦 Instalando Laravel..."
    docker-compose exec app composer create-project laravel/laravel .
    
    echo "🔧 Configurando permissões..."
    docker-compose exec app chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
    docker-compose exec app chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
fi

echo "🔑 Gerando chave da aplicação..."
docker-compose exec app php artisan key:generate

echo "⚙️ Configurando variáveis de ambiente..."
# Criar .env se não existir
if [ ! -f ".env" ]; then
    docker-compose exec app cp .env.example .env
fi

# Atualizar configurações do banco de dados no .env
docker-compose exec app sed -i 's/DB_CONNECTION=sqlite/DB_CONNECTION=mysql/' .env
docker-compose exec app sed -i 's/DB_HOST=127.0.0.1/DB_HOST=mysql/' .env
docker-compose exec app sed -i 's/DB_PORT=3306/DB_PORT=3306/' .env
docker-compose exec app sed -i 's/DB_DATABASE=laravel/DB_DATABASE=ticto/' .env
docker-compose exec app sed -i 's/DB_USERNAME=root/DB_USERNAME=ticto/' .env
docker-compose exec app sed -i 's/DB_PASSWORD=/DB_PASSWORD=password/' .env

# Configurar Redis
docker-compose exec app sed -i 's/REDIS_HOST=127.0.0.1/REDIS_HOST=redis/' .env

echo "🗄️ Executando migrações..."
docker-compose exec app php artisan migrate --force

echo "✅ Setup completo!"
echo ""
echo "🌐 Aplicação disponível em: http://localhost:8000"
echo "🗄️ MySQL disponível em: localhost:3306"
echo "🔴 Redis disponível em: localhost:6379"
echo ""