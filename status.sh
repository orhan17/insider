#!/bin/bash

echo "================================"
echo "Insider Message Sender - Status"
echo "================================"
echo ""

echo "📦 Docker Containers:"
docker-compose ps
echo ""

echo "📊 Database Status:"
docker-compose exec -T db mysql -u insider -ppassword insider -e "SELECT status, COUNT(*) as count FROM messages GROUP BY status;" 2>/dev/null || echo "Database not accessible"
echo ""

echo "🔴 Redis Queue Status:"
docker-compose exec -T redis redis-cli LLEN insider_:queue:default 2>/dev/null || echo "Redis not accessible"
echo ""

echo "💾 Redis Cache Keys:"
docker-compose exec -T redis redis-cli KEYS "insider_sent_message:*" 2>/dev/null | head -5 || echo "No cache keys or Redis not accessible"
echo ""

echo "📝 Recent Logs:"
docker-compose logs --tail=10 queue
echo ""

echo "🌐 Endpoints:"
echo "  - Application: http://localhost:8081"
echo "  - API: http://localhost:8081/api/v1/messages"
echo "  - Swagger: http://localhost:8081/api/documentation"
echo ""

echo "✅ Quick Actions:"
echo "  make process  - Process pending messages"
echo "  make test     - Run tests"
echo "  make logs     - View all logs"
echo ""

