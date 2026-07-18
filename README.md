# CI4 Production Grade Kit

![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=flat-square&logo=php&logoColor=white)
![CodeIgniter](https://img.shields.io/badge/CodeIgniter-4.x-EF4223?style=flat-square&logo=codeigniter&logoColor=white)
![Shield](https://img.shields.io/badge/Shield-Auth-22c55e?style=flat-square)
![License](https://img.shields.io/badge/license-MIT-blue?style=flat-square)
![Status](https://img.shields.io/badge/status-stable-brightgreen?style=flat-square)

A production-ready CodeIgniter 4 starter kit with structured API responses, Shield authentication, layered filter stack, service layer architecture, and structured JSON logging — ready to clone and extend.

---

## Contents

- [Requirements](#requirements)
- [Quick Start](#quick-start)
- [Architecture Overview](#architecture-overview)
- [Project Structure](#project-structure)
- [API Response Envelope](#api-response-envelope)
- [Filter Stack](#filter-stack)
- [Logging](#logging)
- [How to Add a New Resource](#how-to-add-a-new-resource)
- [Server Requirements](#server-requirements)

---

## Requirements

| Dependency | Version |
|---|---|
| PHP | 8.2+ |
| Composer | latest |
| MySQL | 8.0+ |
| MariaDB | 10.5+ *(alternative)* |
| Web Server | Apache / Nginx / `php spark serve` |

---

## Quick Start

```bash
# 1. Clone and enter the project
git clone <repo-url> my-project && cd my-project

# 2. Copy environment template and fill in DB credentials
cp .env.example .env

# 3. Install dependencies
composer install

# 4. Run all migrations (CI4 + Shield tables)
php spark migrate --all

# 5. Start the development server
php spark serve

# 6. Verify the setup
curl http://localhost:8080/api/ping
# Expected: {"status":true,"code":200,"message":"pong","data":null}
```

---

## Architecture Overview

```
Request → Filter Stack → Controller → Service → Model → Database
```

| Layer | Responsibility |
|---|---|
| **Controller** | Receives the request, delegates to the Service, returns a JSON response. Never accesses a Model directly. |
| **Service** | Holds business logic, validates input, orchestrates Model calls. |
| **Model** | Extends `BaseModel`, handles DB queries and soft deletes. |

---

## Project Structure

```
app/
├── Config/
│   ├── AppConstants.php      # HTTP status codes and app-wide constants
│   ├── Filters.php           # Filter aliases and route bindings
│   └── Routes.php            # Route definitions
├── Controllers/
│   ├── BaseController.php    # Base for all controllers (traits wired here)
│   └── Api/
│       ├── BaseApiController.php   # Forces JSON response, populates $apiUser
│       ├── PingController.php      # Health check endpoints
│       └── UserController.php      # Full CRUD reference implementation
├── Exceptions/
│   ├── ServiceException.php        # General service-layer exception
│   └── ValidationException.php     # Wraps validation errors (422)
├── Filters/
│   ├── ApiKeyFilter.php      # Validates Bearer token via Shield AccessTokens
│   ├── AuthFilter.php        # Session auth guard for web routes
│   ├── CorsFilter.php        # CORS headers + OPTIONS preflight
│   └── JsonBodyFilter.php    # Rejects non-JSON bodies on POST/PUT/PATCH
├── Helpers/
│   └── response_helper.php   # api_success() / api_error() for filter context
├── Libraries/
│   └── AppLogger.php         # Static facade for structured JSON logging
├── Models/
│   ├── BaseModel.php         # Timestamps, soft delete, search/dateRange scopes
│   └── UserModel.php         # Extends Shield's UserModel
├── Services/
│   ├── BaseService.php       # CRUD + pagination + validation wiring
│   └── UserService.php       # User resource — full reference implementation
├── Traits/
│   ├── ApiResponseTrait.php  # success(), error(), created(), paginate(), noContent()
│   └── LoggableTrait.php     # logInfo(), logWarning(), logError() with JSON payload
└── Validation/
    └── BaseValidator.php     # Thin wrapper around CI4 Validation service
```

---

## API Response Envelope

All responses follow a consistent JSON structure:

**Success**
```json
{
  "status": true,
  "code": 200,
  "message": "Success",
  "data": { }
}
```

**Error / Validation**
```json
{
  "status": false,
  "code": 422,
  "message": "Validation failed",
  "errors": { "email": "The email field is required." }
}
```

**Paginated**
```json
{
  "status": true,
  "code": 200,
  "message": "Success",
  "data": [ ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 100,
    "total_pages": 7
  }
}
```

---

## Filter Stack

```
Request → CorsFilter → JsonBodyFilter → ApiKeyFilter / AuthFilter → Controller
```

| Filter | Applied To | Purpose |
|---|---|---|
| `CorsFilter` | `api/*` (before + after) | Injects CORS headers; handles OPTIONS preflight with `204` |
| `JsonBodyFilter` | `api/*` (before) | Rejects POST/PUT/PATCH without `Content-Type: application/json` |
| `ApiKeyFilter` | `api/*` protected group | Validates Bearer token via Shield AccessTokens |
| `AuthFilter` | web routes | Checks session login; redirects to `/login` if missing |

Filter registration: `app/Config/Filters.php`

---

## Logging

`AppLogger` can be called statically from anywhere:

```php
use App\Libraries\AppLogger;

AppLogger::info('payment.success', ['amount' => 50000, 'user_id' => 12]);
AppLogger::warning('rate.limit.hit', ['ip' => $ip]);
AppLogger::error('webhook.failed', ['payload' => $raw], $exception);
```

Inside a Controller via `LoggableTrait`:

```php
$this->logInfo('user.created', ['id' => $userId]);
$this->logError('user.create.failed', [], $e);
```

Every log entry is a structured JSON line written to `writable/logs/`:

```json
{
  "timestamp": "2026-07-18T08:44:00+07:00",
  "level": "INFO",
  "action": "user.created",
  "user_id": 3,
  "ip": "127.0.0.1",
  "context": { "id": 42 }
}
```

> **Warning:** Never pass sensitive data (passwords, tokens, PII) in the `$context` array.

---

## How to Add a New Resource

Example: adding a `Post` resource.

**1. Create the migration**
```bash
php spark make:migration CreatePostsTable
php spark migrate
```

**2. Create the Model** — `app/Models/PostModel.php`
```php
class PostModel extends BaseModel
{
    protected $table            = 'posts';
    protected $allowedFields    = ['title', 'body', 'user_id'];
    protected array $searchableFields = ['title', 'body'];
}
```

**3. Create the Service** — `app/Services/PostService.php`
```php
class PostService extends BaseService
{
    protected string $modelClass = PostModel::class;
}
```

**4. Create the Controller** — `app/Controllers/Api/PostController.php`
```php
class PostController extends BaseApiController
{
    public function index(): ResponseInterface
    {
        $result = (new PostService())->findAll($this->request->getGet() ?? []);
        return $this->success($result['data']);
    }
}
```

**5. Register routes** in `app/Config/Routes.php`
```php
$routes->group('api', ['filter' => 'apiKeyFilter'], static function ($routes) {
    $routes->get('posts',          'Api\PostController::index');
    $routes->post('posts',         'Api\PostController::create');
    $routes->get('posts/(:num)',   'Api\PostController::show/$1');
    $routes->put('posts/(:num)',   'Api\PostController::update/$1');
    $routes->delete('posts/(:num)','Api\PostController::delete/$1');
});
```

See `app/Services/UserService.php` and `app/Controllers/Api/UserController.php` for a complete reference implementation.

---

## Server Requirements

PHP 8.2 or higher with the following extensions:

| Extension | Notes |
|---|---|
| `intl` | Required |
| `mbstring` | Required |
| `json` | Enabled by default |
| `mysqlnd` | Required for MySQL |
| `libcurl` | Required if using `HTTP\CURLRequest` |

---

## License

This project is open-sourced under the [MIT License](LICENSE).
