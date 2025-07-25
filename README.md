

# Ticto - Sistema de Controle de Ponto

Sistema completo de controle de ponto eletrônico com **API Laravel** e **Frontend Vue.js**.

## 🚀 Stack Tecnológica

### Backend (API)
- **Laravel 12** - Framework PHP
- **PHP 8.4** - Linguagem
- **MySQL 8.4** - Banco de dados
- **Redis 7** - Cache e sessões
- **Laravel Sanctum** - Autenticação API
- **Docker** - Containerização

### Frontend (SPA)
- **Vue 3** - Framework JavaScript
- **Pinia** - Gerenciamento de estado
- **Vue Router** - Roteamento
- **TailwindCSS 4** - Styling
- **Heroicons** - Ícones
- **Axios** - Cliente HTTP
- **Vite** - Build tool

## 🎯 Funcionalidades

### 🔐 Autenticação & Autorização
- Login/logout seguro
- Roles: Admin, Manager, Employee
- Proteção de rotas por permissões
- API com tokens JWT

### 👥 Gestão de Funcionários
- CRUD completo de funcionários
- Busca automática de endereço via CEP
- Filtros e busca avançada
- Interface responsiva

### ⏰ Controle de Ponto
- Registro de entrada/saída/pausa
- Validações de horários
- Histórico de registros
- Dashboard em tempo real

### 📊 Interface Moderna
- Design responsivo (mobile-first)
- Notificações em tempo real
- Loading states
- Componentes reutilizáveis

## 🚀 Início Rápido

### 1. Instalação Completa
```bash
make install
```

### 2. Acessar a Aplicação
- **Frontend**: http://localhost
- **API**: http://localhost/api

### 3. Login Padrão
- **Gerente**: admin@ticto.com / password
- **Funcionário**: employee@ticto.com / password



## 🏗️ Arquitetura

### Backend (API)
```
app/
├── Http/Controllers/Api/  # Controllers da API
├── Models/               # Models Eloquent
├── Services/            # Services (business logic)
├── Repositories/        # Repositories (data access)
└── Contracts/          # Interfaces
```

### Frontend (SPA)
```
resources/js/
├── components/          # Componentes reutilizáveis
├── pages/              # Páginas da aplicação
├── stores/             # Stores Pinia (estado)
├── services/           # Services HTTP
├── router/             # Configuração de rotas
└── App.vue            # Componente raiz
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

### 🐳 Docker & Aplicação
```bash
make help          # Ver todos os comandos
make start         # Iniciar aplicação completa
make stop          # Parar todos os serviços
make restart       # Reiniciar aplicação
make status        # Ver status dos containers
make logs          # Ver logs de todos os serviços
```

### 🎨 Frontend (Vue.js)
```bash
make frontend          # Servidor de desenvolvimento Vite
make frontend-build    # Build para produção
make frontend-watch    # Watch mode para desenvolvimento
make npm-install      # Instalar dependências npm
```

### 🗄️ Backend (Laravel)
```bash
make migrate          # Executar migrações
make migrate-fresh    # Resetar banco e executar migrações
make seed            # Executar seeders
make migrate-seed    # Migrar + seed (setup completo)
make artisan cmd="route:list"  # Comandos Artisan
make tinker          # Laravel Tinker
```

### 🧪 Testes
```bash
make test            # Executar todos os testes
make test-pest       # Executar testes Pest
make test-services   # Testar apenas services
make test-unit       # Testes unitários
make test-feature    # Testes de feature
make test-coverage   # Testes com coverage
make test-watch      # Testes em watch mode
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
