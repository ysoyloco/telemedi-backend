# Call Center Schedule Manager API

Symfony API do zarządzania grafikami call center - pierwszy działający projekt Symfony! 🎉

## 🚀 Quick Start

### Wymagania
- PHP 8.1+
- Composer
- MySQL/MariaDB
- Git

### Instalacja

```bash
# Klonuj projekt
git clone <your-repo-url>
cd api_project

# Zainstaluj dependencies
composer install

# Skopiuj konfigurację
cp .env .env.local
```

### Konfiguracja bazy danych

Edytuj `.env.local`:
```env
DATABASE_URL="mysql://username:password@127.0.0.1:3306/call_center_db?serverVersion=8.0"
```

### Setup bazy danych

```bash
# Utwórz bazę danych
php bin/console doctrine:database:create

# Uruchom migracje
php bin/console doctrine:migrations:migrate

# Wgraj przykładowe dane
php bin/console doctrine:fixtures:load
```

### Uruchom serwer

```bash
# Development server
symfony server:start
# LUB
php -S localhost:8000 -t public/
```

API dostępne na: `http://localhost:8000/api`

## 🧪 Testy

### Setup bazy testowej
```bash
# Utwórz bazę testową
php bin/console doctrine:database:create --env=test

# Migracje dla testów
php bin/console doctrine:migrations:migrate --no-interaction --env=test

# Fixtures dla testów
php bin/console doctrine:fixtures:load --no-interaction --env=test
```

### Uruchom testy
```bash
# Wszystkie testy
php bin/phpunit

# Tylko testy kontrolerów
php bin/phpunit tests/Controller/ --testdox

# Reset bazy testowej (gdy testy się psują)
./reset-test-db.sh
```

### Reset Test Database Script
```bash:reset-test-db.sh
#!/bin/bash
php bin/console doctrine:database:drop --force --env=test
php bin/console doctrine:database:create --env=test
php bin/console doctrine:migrations:migrate --no-interaction --env=test
php bin/console doctrine:fixtures:load --no-interaction --env=test
echo "✅ Test database reset complete!"
```

```bash
chmod +x reset-test-db.sh
```

## 📡 API Endpoints

### Kolejki (Queues)
```bash
GET /api/queues                    # Lista kolejek
```

### Agenci (Agents)  
```bash
GET /api/agents                    # Lista agentów
GET /api/agents/{id}               # Szczegóły agenta
POST /api/agents                   # Utwórz agenta
PUT /api/agents/{id}               # Aktualizuj agenta
DELETE /api/agents/{id}            # Usuń agenta
GET /api/agents/{id}/queues        # Kolejki agenta
```

### Grafik (Schedules)
```bash
GET /api/schedules                 # Lista wpisów w grafiku
GET /api/calendar-view             # Widok kalendarza
GET /api/slot-proposals            # Propozycje agentów dla slotu
POST /api/schedules                # Utwórz wpis w grafiku
PUT /api/schedules/{id}            # Aktualizuj wpis
DELETE /api/schedules/{id}         # Usuń wpis
```

### Logi aktywności
```bash
GET /api/agent-activity-logs       # Logi aktywności agentów
POST /api/agent-activity-logs      # Utwórz log
```

### Wyjątki dostępności
```bash
GET /api/agent-availability-exceptions    # Wyjątki dostępności
POST /api/agent-availability-exceptions   # Utwórz wyjątek
```

## 🏗️ Architektura

### Entities
- `Agent` - Agenci call center
- `Queue` - Kolejki obsługi  
- `Schedule` - Wpisy w grafiku
- `AgentActivityLog` - Logi aktywności
- `AgentAvailabilityException` - Wyjątki dostępności
- `QueueLoadTrend` - Trendy obciążenia kolejek

### Services
- `AgentService` - Logika biznesowa agentów
- `QueueService` - Zarządzanie kolejkami
- `ScheduleService` - Operacje na grafiku
- `SlotProposalService` - Propozycje przydziału agentów

### Controllers
- `AgentController` - CRUD agentów
- `QueueController` - Operacje na kolejkach
- `ScheduleController` - Zarządzanie grafikiem
- `AgentActivityLogController` - Logi aktywności
- `AgentAvailabilityExceptionController` - Wyjątki dostępności

## 🔧 Przydatne komendy

```bash
# Cache
php bin/console cache:clear
php bin/console cache:clear --env=test

# Doctrine
php bin/console doctrine:schema:validate
php bin/console doctrine:mapping:info
php bin/console make:entity

# Debug
php bin/console debug:router
php bin/console debug:container
php bin/console debug:autowiring

# Fixtures
php bin/console doctrine:fixtures:load --append
```

## 🐛 Troubleshooting

### Problem z testami
```bash
# Reset bazy testowej
./reset-test-db.sh
```

### Problem z cache
```bash
php bin/console cache:clear
php bin/console cache:clear --env=test
```

### Problem z uprawnieniami
```bash
chmod -R 777 var/
```

## 📚 Symfony Cheat Sheet

### Annotacje/Atrybuty
```php
#[ORM\Entity]                      # Oznacza entity
#[ORM\Table(name: 'agents')]       # Nazwa tabeli
#[ORM\Id]                          # Klucz główny
#[ORM\GeneratedValue]              # Auto increment
#[ORM\Column]                      # Kolumna w bazie
#[Groups(['agent:read'])]          # Grupy serializacji
#[Route('/api/agents')]            # Routing
```

### Dependency Injection
```php
public function __construct(
    private AgentRepository $agentRepository,
    private EntityManagerInterface $entityManager
) {}
```

### Query Builder
```php
$queryBuilder = $this->createQueryBuilder('a')
    ->where('a.isActive = :active')
    ->setParameter('active', true)
    ->getQuery()
    ->getResult();
```

---