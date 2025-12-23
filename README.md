# Insider Message Sending System

An automatic bulk message sending system built with Laravel 10, utilizing queues, Redis, and modern design patterns.

## 📚 Documentation

- **[Quick Start Guide](QUICKSTART.md)** - Get started in 5 minutes
- **[Architecture Documentation](ARCHITECTURE.md)** - Detailed architecture description
- **[API Examples](API_EXAMPLES.md)** - API usage examples
- **[Webhook Integration Guide](WEBHOOK_GUIDE.md)** - Webhook setup instructions
- **[Testing Guide](TESTING_PLAN.md)** - Testing plan and examples

## 📋 Project Description

This system is designed to send bulk messages to users in specific segments. The project implements:

- Message sending via webhook with rate limiting (2 messages every 5 seconds)
- Asynchronous processing through Laravel queues
- Caching of sent messages in Redis
- RESTful API for retrieving list of sent messages
- Complete API documentation via Swagger/OpenAPI

## 🏗️ Architecture

The project follows Clean Architecture principles and uses the following patterns:

- **Repository Pattern** - for data access abstraction
- **Service Layer** - for business logic
- **Dependency Injection** - for dependency management
- **Job/Queue Pattern** - for asynchronous processing
- **Command Pattern** - for CLI commands

### Layer Structure

```
app/
├── Console/Commands/      # CLI commands
├── Contracts/             # Interfaces for DI
├── Http/Controllers/Api/  # API controllers
├── Jobs/                  # Queue jobs
├── Models/                # Eloquent models
├── Repositories/          # Data repositories
└── Services/              # Business logic
```

## 🚀 Requirements

- Docker & Docker Compose
- Git

## 📦 Installation

### 1. Clone the repository

```bash
git clone <repository-url>
cd insider
```

### 2. Environment setup

```bash
cp .env.example .env
```

Edit the `.env` file and specify your webhook URL:

```env
WEBHOOK_URL=https://webhook.site/your-unique-id
WEBHOOK_AUTH_KEY=INS.me1x9uMcyYGlhKKQVPoc.bO3j9aZwRTOcA2Ywo
```

### 3. Start Docker containers

```bash
docker-compose up -d
```

### 4. Install dependencies

```bash
docker-compose exec app composer install
```

### 5. Generate application key

```bash
docker-compose exec app php artisan key:generate
```

### 6. Run migrations

```bash
docker-compose exec app php artisan migrate
```

### 7. Seed test data (optional)

```bash
docker-compose exec app php artisan db:seed
```

## 🎯 Usage

### Sending Messages

#### Step 1: Run message processing command

This command adds all unsent messages to the queue with rate limiting:

```bash
docker-compose exec app php artisan messages:process
```

Options:
- `--limit=N` - maximum number of messages to process (default: 100)

#### Step 2: Start queue worker

Queue worker is already running in a separate container, but you can run it manually:

```bash
docker-compose exec app php artisan queue:work
```

Or check the existing container logs:
```bash
docker-compose logs -f queue
```

### API Endpoints

#### Get list of sent messages

```bash
GET /api/v1/messages
```

**Request example:**

```bash
curl http://localhost:8081/api/v1/messages
```

**Response example:**

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "phone_number": "+905551111111",
      "content": "Insider - Project",
      "status": "sent",
      "message_id": "67f2f8a8-ea58-4ed0-a6f9-ff217df4d849",
      "sent_at": "2024-01-01T12:00:00Z",
      "created_at": "2024-01-01T11:00:00Z",
      "updated_at": "2024-01-01T12:00:00Z"
    }
  ],
  "count": 1
}
```

### Swagger API Documentation

Документация API доступна по адресу:

```
http://localhost:8081/api/documentation
```

Для генерации документации:

```bash
docker-compose exec app php artisan l5-swagger:generate
```

## 🧪 Тестирование

### Запуск всех тестов

```bash
docker-compose exec app php artisan test
```

### Запуск Unit тестов

```bash
docker-compose exec app php artisan test --testsuite=Unit
```

### Запуск Feature тестов

```bash
docker-compose exec app php artisan test --testsuite=Feature
```

### Запуск с покрытием

```bash
docker-compose exec app php artisan test --coverage
```

## 🔍 Инструменты качества кода

### Psalm (Статический анализ)

```bash
docker-compose exec app composer psalm
```

### PHP CS Fixer (Форматирование кода)

Проверка:
```bash
docker-compose exec app composer cs-fix -- --dry-run
```

Исправление:
```bash
docker-compose exec app composer cs-fix
```

###  (Проверка архитектурных зависимостей)

```bash
docker-compose exec app composer 
```

## 🗄️ База данных

### Структура таблицы messages

| Колонка      | Тип       | Описание                           |
|--------------|-----------|-------------------------------------|
| id           | bigint    | Первичный ключ                      |
| phone_number | string    | Номер телефона получателя          |
| content      | text      | Содержимое сообщения               |
| status       | enum      | Статус: pending, sent, failed      |
| message_id   | string    | ID сообщения из webhook (nullable)  |
| sent_at      | timestamp | Время отправки (nullable)          |
| created_at   | timestamp | Время создания                     |
| updated_at   | timestamp | Время обновления                   |

### Создание сообщения вручную

```bash
docker-compose exec app php artisan tinker
```

```php
App\Models\Message::create([
    'phone_number' => '+905551111111',
    'content' => 'Test message',
    'status' => 'pending'
]);
```

## 🔄 Workflow отправки сообщений

1. Сообщения создаются в БД со статусом `pending`
2. Команда `messages:process` читает pending сообщения и добавляет их в очередь Redis
3. Queue worker обрабатывает задачи из очереди
4. `SendMessageJob` отправляет сообщение через webhook
5. При успешной отправке:
   - Статус обновляется на `sent`
   - Сохраняется `message_id` из ответа webhook
   - Данные кэшируются в Redis
6. При ошибке статус обновляется на `failed` (с повторными попытками)

## 📊 Rate Limiting

Система использует rate limiting для контроля скорости отправки:

- **Лимит**: 2 сообщения каждые 5 секунд
- **Настройка**: в `.env` файле через `MESSAGE_RATE_LIMIT` и `MESSAGE_RATE_INTERVAL`

## 🐳 Docker Services

| Service | Description                    | Port  |
|---------|--------------------------------|-------|
| app     | PHP-FPM приложение             | -     |
| nginx   | Веб-сервер                     | 8080  |
| db      | MySQL 8.0                      | 33060 |
| redis   | Redis кэш и очереди            | 63790 |
| queue   | Queue worker                   | -     |

**Примечание:** Внешние порты изменены для избежания конфликтов с локальными сервисами.

## 🔧 Полезные команды

### Docker

```bash
# Остановить все контейнеры
docker-compose down

# Перезапустить контейнеры
docker-compose restart

# Просмотр логов
docker-compose logs -f

# Просмотр логов конкретного сервиса
docker-compose logs -f queue

# Зайти в контейнер
docker-compose exec app bash
```

### Laravel

```bash
# Очистить кэш
docker-compose exec app php artisan cache:clear

# Очистить конфигурацию
docker-compose exec app php artisan config:clear

# Просмотр очередей
docker-compose exec app php artisan queue:monitor

# Повторная попытка failed jobs
docker-compose exec app php artisan queue:retry all

# Очистка failed jobs
docker-compose exec app php artisan queue:flush
```

## 📝 Примеры использования

### Пример 1: Создание и отправка сообщений

```bash
# 1. Создать тестовые сообщения
docker-compose exec app php artisan tinker
>>> App\Models\Message::factory()->count(5)->create();

# 2. Обработать сообщения
docker-compose exec app php artisan messages:process

# 3. Проверить статус через API
curl http://localhost:8081/api/v1/messages
```

### Пример 2: Мониторинг отправки

```bash
# Терминал 1: Запуск обработки
docker-compose exec app php artisan messages:process

# Терминал 2: Мониторинг логов queue worker
docker-compose logs -f queue

# Терминал 3: Проверка Redis
docker-compose exec redis redis-cli
> KEYS insider_*
```

## 🧩 Конфигурация

### Основные настройки в .env

```env
# Database
DB_CONNECTION=mysql
DB_HOST=db
DB_DATABASE=insider
DB_USERNAME=insider
DB_PASSWORD=password

# Redis
REDIS_HOST=redis
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis

# Webhook
WEBHOOK_URL=https://webhook.site/your-id
WEBHOOK_AUTH_KEY=your-auth-key

# Message Settings
MESSAGE_RATE_LIMIT=2
MESSAGE_RATE_INTERVAL=5
MESSAGE_MAX_LENGTH=160
```

## 🔒 Безопасность

- Все пароли и ключи хранятся в `.env` файле
- Используется Docker для изоляции окружения
- Валидация входных данных на уровне Service Layer
- Ограничение длины сообщений
- Проверка формата номера телефона

## 📚 Технологический стек

- **Framework**: Laravel 10.x
- **PHP**: 8.2
- **Database**: MySQL 8.0
- **Cache/Queue**: Redis 7
- **Web Server**: Nginx
- **Containerization**: Docker & Docker Compose
- **Testing**: PHPUnit
- **Static Analysis**: Psalm
- **Code Style**: PHP-CS-Fixer
- **Architecture**: 
- **API Documentation**: L5-Swagger (OpenAPI)

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📄 License

MIT License

## 👥 Author

Insider Project Team

## 📞 Support

For support, email support@insider.com or create an issue in the repository.

