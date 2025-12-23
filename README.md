# Insider Message Sending System


Automatic bulk message sending system with Laravel 10, Redis queue, and rate limiting.

## Features

- Bulk message sending via webhook
- Rate limiting: 2 messages per 5 seconds
- Asynchronous queue processing (Redis)
- Message status tracking (pending/sent/failed)
- Redis caching for sent messages
- RESTful API endpoints
- Repository Pattern + Service Layer
- Full test coverage (Unit + Feature)
- Code quality tools (Psalm, PHP-CS-Fixer, Deptrac)

## Quick API Examples

```bash
# Create a new message
curl -X POST http://localhost:8081/api/v1/messages \
  -H "Content-Type: application/json" \
  -d '{"phone_number": "+905551111111", "content": "Test message"}'

# Get all sent messages
curl -X GET http://localhost:8081/api/v1/messages


# Process pending messages (trigger queue)
make process
# or
docker-compose exec app php artisan messages:process

# Run all tests and quality checks
make test-all
```

## Quick Start

```bash
# Clone and setup
git clone https://github.com/orhan17/insider && cd insider
cp .env.example .env

# Start Docker
docker-compose up -d

# Install & migrate
docker-compose exec app composer install
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate

# Configure webhook in .env
WEBHOOK_URL=https://webhook.site/your-unique-id
WEBHOOK_AUTH_KEY=your-auth-key-here
```

## API Endpoints

### Create Message
```bash
# cURL
curl -X POST http://localhost:8081/api/v1/messages \
  -H "Content-Type: application/json" \
  -d '{
    "phone_number": "+905551111111",
    "content": "Your message text here"
  }'

# Response (201 Created)
{
  "success": true,
  "message": "Message created successfully",
  "data": {
    "id": 1,
    "phone_number": "+905551111111",
    "content": "Your message text here",
    "status": "pending",
    "created_at": "2024-01-01T12:00:00Z"
  }
}
```

### Get Sent Messages
```bash
# cURL
curl -X GET http://localhost:8081/api/v1/messages

# Response (200 OK)
{
  "success": true,
  "data": [
    {
      "id": 1,
      "phone_number": "+905551111111",
      "content": "Your message text here",
      "status": "sent",
      "message_id": "67f2f8a8-ea58-4ed0-a6f9-ff217df4d849",
      "sent_at": "2024-01-01T12:00:05Z",
      "created_at": "2024-01-01T12:00:00Z",
      "updated_at": "2024-01-01T12:00:05Z"
    }
  ],
  "count": 1
}
```


## Usage

```bash
# Process pending messages
docker-compose exec app php artisan messages:process

# Queue worker runs automatically in separate container
docker-compose logs -f queue
```

## Architecture

- **Repository Pattern** - Data access abstraction
- **Service Layer** - Business logic (MessageService, WebhookService, CacheService)
- **Dependency Injection** - Clean dependencies
- **Queue/Job** - Async processing with retry logic
- **Commands** - CLI for message processing

## Testing

```bash
# All tests
docker-compose exec app php artisan test
# or
make test

# Run all tests + quality checks at once
make test-all

# Individual quality checks
make psalm                                        # Static analysis
make fix-cs                                       # Code style fix
make check-cs                                     # Code style check
make deptrac                                      # Architecture validation

# Or manually:
docker-compose exec app vendor/bin/psalm          # Static analysis
docker-compose exec app vendor/bin/php-cs-fixer fix  # Code style
docker-compose exec app composer deptrac          # Architecture validation
```

**Test Results:** 27 tests, 116 assertions, 100% passing

## Tech Stack

- Laravel 10.x
- PHP 8.2
- MySQL 8.0
- Redis 7
- Docker & Docker Compose


## Docker Services

| Service | Port  | Description          |
|---------|-------|----------------------|
| nginx   | 8081  | Web server           |
| db      | 33060 | MySQL 8.0            |
| redis   | 63790 | Cache & Queue        |
| app     | -     | PHP-FPM application  |
| queue   | -     | Queue worker         |

## Database Schema

**messages table:**
- `id` - Primary key
- `phone_number` - Recipient phone (E.164 format)
- `content` - Message text (max 160 chars)
- `status` - pending/sent/failed
- `message_id` - External webhook message ID
- `sent_at` - Timestamp when sent
- `created_at`, `updated_at` - Timestamps

## Useful Commands

```bash
# Quick start with Makefile
make up              # Start containers
make migrate         # Run migrations
make seed            # Seed database
make process         # Process pending messages
make test-all        # Run all tests + quality checks

# View logs
docker-compose logs -f queue

# Run tests
docker-compose exec app php artisan test
make test

# Run all tests and quality checks
make test-all

# Individual quality checks
docker-compose exec app vendor/bin/psalm              # Static analysis
docker-compose exec app vendor/bin/php-cs-fixer fix   # Code style
docker-compose exec app composer deptrac              # Architecture validation

# Clear cache
docker-compose exec app php artisan cache:clear

# Tinker
docker-compose exec app php artisan tinker
```

## Environment Variables

```env
# Database
DB_HOST=db
DB_DATABASE=insider_db
DB_USERNAME=insider_user
DB_PASSWORD=insider_pass

# Redis
REDIS_HOST=redis
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis

# Webhook
WEBHOOK_URL=https://webhook.site/your-unique-id
WEBHOOK_AUTH_KEY=your-auth-key-here

# Message Settings (optional)
MESSAGE_RATE_LIMIT=2
MESSAGE_RATE_INTERVAL=5
MESSAGE_MAX_LENGTH=160
```

## Requirements Met

All Insider assessment requirements implemented:
- Repository Pattern + Service Layer
- Queue/Job/Worker structures
- Rate limiting (2 messages/5 seconds)
- Redis caching (messageId + timestamp)
- RESTful API standards
- Unit & Integration tests
- Clean Architecture & SOLID principles
- Laravel 10.x best practices
- Docker containerization
- Complete documentation

## License

MIT License

---

## Additional Information

![Insider Assessment](img.png)

