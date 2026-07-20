# CodeIgniter 4  Production Grade Kit
![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=flat-square&logo=php&logoColor=white)
![CodeIgniter](https://img.shields.io/badge/CodeIgniter-4.x-EF4223?style=flat-square&logo=codeigniter&logoColor=white)
![Shield](https://img.shields.io/badge/Shield-Auth-22c55e?style=flat-square)
![License](https://img.shields.io/badge/license-MIT-blue?style=flat-square)
![Status](https://img.shields.io/badge/status-stable-brightgreen?style=flat-square)
![Version](https://img.shields.io/badge/version-2.0.0-blue?style=flat-square)

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
- [File Uploads](#file-uploads)
- [Transformers](#transformers)
- [Audit Trail](#audit-trail)
- [SSO Layer](#sso-layer)
- [PDF Export](#pdf-export)
- [Compatibility Matrix](#compatibility-matrix)
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

Click **Use this template** → **Create a new repository**, then:

```bash
# 1. Enter the project
cd my-project

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
                                           ↓
                                     Transformer
```

| Layer | Responsibility |
|---|---|
| **Controller** | Receives the request, delegates to the Service, returns a JSON response. Never accesses a Model directly. |
| **Service** | Holds business logic, validates input, orchestrates Model calls. |
| **Transformer** | Shapes and sanitizes response payloads before they reach the API response layer. |
| **Model** | App models extend `BaseModel` (soft delete, search/dateRange scopes). Shield-based models extend `ShieldUserModel` directly. Both are compatible with `BaseService`. |

### Optional Layers (v2.0)

| Layer | Files | Notes |
|---|---|---|
| **SSO Layer** | `SSOConfig`, `JWTService`, `SSOFilter` | JWT RS256 auth for cross-app requests. Disabled by default (`SSO_ENABLED=false`). |
| **PDF Export** | `BasePdfExporter` | Abstract base for mPDF-based PDF generation. Extend per module. |

### Lifecycle Hooks

Override any of these in your Service to react to CRUD events without touching `BaseService`:

```php
protected function afterCreate(int|string $id, array $data): void
protected function afterUpdate(int|string $id, array $data): void
protected function afterDelete(int|string $id, array $oldData): void
```

Hook failures are non-blocking — they log to `log_message()` and never break the main operation.

---

## Project Structure

```
app/
├── Config/
│   ├── AppConstants.php      # HTTP status codes and app-wide constants
│   ├── Filters.php           # Filter aliases and route bindings
│   ├── Routes.php            # Route definitions
│   └── SSOConfig.php         # SSO toggle + RSA key config (v2.0)
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
│   ├── JsonBodyFilter.php    # Rejects non-JSON bodies on POST/PUT/PATCH
│   └── SSOFilter.php         # JWT Bearer token verification for SSO (v2.0)
├── Helpers/
│   └── response_helper.php   # api_success() / api_error() for filter context
├── Contracts/
│   └── StorageDriverInterface.php  # Abstraction for pluggable storage backends
├── Libraries/
│   ├── AppLogger.php         # Static facade for structured JSON logging
│   ├── BasePdfExporter.php   # Abstract base for PDF export via mPDF (v2.0)
│   ├── FileUploader.php      # Standardized upload handler for module files
│   ├── JWTService.php        # JWT RS256 sign and verify (v2.0)
│   └── Storage/
│       ├── LocalDriver.php   # Default local filesystem storage driver
│       └── S3Driver.php      # Optional S3-compatible storage driver
├── Models/
│   ├── BaseModel.php         # Timestamps, soft delete, search/dateRange scopes
│   └── UserModel.php         # Extends Shield's UserModel + QueryScopesTrait
├── Services/
│   ├── BaseService.php       # CRUD + pagination + validation wiring
│   └── UserService.php       # User resource — full reference implementation
├── Traits/
│   ├── ApiResponseTrait.php  # success(), error(), created(), paginate(), noContent()
│   ├── AuditTrailTrait.php   # auditCreate(), auditUpdate(), auditDelete(), auditRestore()
│   ├── LoggableTrait.php     # logInfo(), logWarning(), logError() with JSON payload
│   └── QueryScopesTrait.php  # search(), dateRange(), active() — used by BaseModel and Shield-based models
├── Transformers/
│   └── BaseTransformer.php   # Abstract base for sanitizing and shaping API payloads
├── Validation/
│   └── BaseValidator.php     # Thin wrapper around CI4 Validation service
└── Views/
    └── exports/              # PDF export templates — plain HTML, no layout (v2.0)
```

> Files marked `(v2.0)` are additive. Projects using v1.x are unaffected.

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
Request → CorsFilter → JsonBodyFilter → ApiKeyFilter / SSOFilter / AuthFilter → Controller
```

| Filter | Applied To | Purpose |
|---|---|---|
| `CorsFilter` | `api/*` (before + after) | Injects CORS headers; handles OPTIONS preflight with `204` |
| `JsonBodyFilter` | `api/*` (before) | Rejects POST/PUT/PATCH without `Content-Type: application/json` |
| `ApiKeyFilter` | `api/*` protected group | Validates Bearer token via Shield AccessTokens |
| `SSOFilter` | `api/*` protected group (opt-in) | Verifies JWT Bearer token via RS256. Pass-through when `SSO_ENABLED=false`. |
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

## File Uploads

The kit now includes a reusable uploader service in [app/Libraries/FileUploader.php](app/Libraries/FileUploader.php) for handling module uploads consistently.

It supports:
- configurable max size and allowed extensions
- UUID-based filenames by default
- structured storage under writable/uploads/{module}/{year}/{month}/
- pluggable storage drivers with Local as the default and optional S3-compatible support
- deletion of old files when replacing uploads

Example usage:

```php
$uploader = new \App\Libraries\FileUploader();
$result = $uploader->upload($file, 'avatar');
```

Optional S3 usage:

```php
$uploader = new \App\Libraries\FileUploader([], new \App\Libraries\Storage\S3Driver([
    'bucket' => env('S3_BUCKET'),
    'region' => env('S3_REGION'),
    'key'    => env('S3_KEY'),
    'secret' => env('S3_SECRET'),
]));
```

---

## Transformers

Extend `BaseTransformer` to sanitize and shape model data before it reaches the API response layer — strip sensitive fields, rename keys, or add computed values.

```php
// app/Transformers/UserTransformer.php
class UserTransformer extends BaseTransformer
{
    public function transform(array $item): array
    {
        return $this->only($item, ['id', 'name', 'email']) + [
            'joined_at' => $item['created_at'] ?? null,
        ];
    }
}
```

Usage in a Controller:

```php
$result = $this->userService->findAll($filters);
$transformer = new UserTransformer();
return $this->success($transformer->collection($result['data']));
```

Helper methods available: `only(array $data, array $keys)`, `except(array $data, array $keys)`.

---

## Audit Trail

This kit now includes an optional audit trail layer for important create/update/delete operations.
It is wired at the service layer, so controllers stay clean and audit logging is transparent.

### What gets recorded

Each audit log entry stores:
- actor information (`user_id`, `user_type`)
- action (`create`, `update`, `delete`, `restore`)
- target model and record id
- old/new values
- request metadata (`ip_address`, `user_agent`)
- creation timestamp

### Files involved

- [app/Database/Migrations/20260718120000_CreateAuditLogsTable.php](app/Database/Migrations/20260718120000_CreateAuditLogsTable.php)
- [app/Models/AuditLogModel.php](app/Models/AuditLogModel.php)
- [app/Traits/AuditTrailTrait.php](app/Traits/AuditTrailTrait.php)
- [app/Services/BaseService.php](app/Services/BaseService.php)

### Run the migration

```bash
php spark migrate
```

### Notes

- `auditUpdate()` only records fields that actually changed, so the log stays compact and useful.
- Audit logging is non-blocking by design; failures are logged and do not break normal request flow.

---

## SSO Layer

The kit includes an optional JWT RS256-based Single Sign-On layer for cross-application authentication. It is **disabled by default** — set `SSO_ENABLED=true` to activate.

### How it works

```
SSO Server                    Resource Server
──────────────                ──────────────────────────────
POST /api/login               Authorization: Bearer <JWT>
    ↓                                  ↓
JWTService::sign()            SSOFilter::before()
    ↓                                  ↓
JWT (RS256) → client          JWTService::verify() — offline, no HTTP call
                                       ↓
                              Valid → $request->ssoUser injected
                              Invalid → 401 Unauthorized
```

### Setup

**1. Generate an RSA key pair** (run once on the SSO Server):

```bash
openssl genrsa -out private.pem 2048
openssl rsa -in private.pem -pubout -out public.pem
```

- `private.pem` — stays on the SSO Server only. Never committed to version control.
- `public.pem` — distributed to all Resource Server apps via `.env`.

**2. Configure `.env`**

On the **SSO Server** (signs tokens):

```
SSO_ENABLED=true
SSO_PRIVATE_KEY="-----BEGIN RSA PRIVATE KEY-----\n<key>\n-----END RSA PRIVATE KEY-----"
SSO_TOKEN_TTL=3600
```

On each **Resource Server** (verifies tokens):

```
SSO_ENABLED=true
SSO_PUBLIC_KEY="-----BEGIN PUBLIC KEY-----\n<key>\n-----END PUBLIC KEY-----"
```

**3. Apply the filter to routes**

```php
$routes->group('api', ['filter' => 'ssoFilter'], static function ($routes) {
    $routes->get('profile', 'Api\ProfileController::index');
});
```

`ssoFilter` and `apiKeyFilter` can be used on different route groups in the same app.

### Issue a token (SSO Server)

```php
// app/Controllers/Api/AuthController.php
public function login(): ResponseInterface
{
    // 1. Validate credentials (via Shield or custom logic)
    // 2. Sign JWT
    $token = (new \App\Libraries\JWTService())->sign([
        'sub'   => (string) $user->id,
        'email' => $user->email,
    ]);

    return $this->success(['token' => $token]);
}
```

### Access the payload (Resource Server)

```php
// In any controller on a ssoFilter-protected route
$ssoUser = $this->request->ssoUser; // array: ['sub' => '1', 'email' => '...', 'iat' => ..., 'exp' => ...]
```

### Install

```bash
composer require firebase/php-jwt:^7.0
```

> `firebase/php-jwt` is already in `require` since v2.0. No extra install needed if you cloned this kit.

### Notes

- The `sub` claim is required in every token. `JWTService::sign()` will throw if absent.
- Default TTL is 3600 seconds (1 hour). Override via `SSO_TOKEN_TTL`.
- When `SSO_ENABLED=false`, `SSOFilter` is a complete pass-through — zero overhead.
- Refresh tokens are not included in v2.0. Implement in your own app if needed.

---

## PDF Export

The kit includes `BasePdfExporter`, an abstract base class for generating and streaming PDFs via [mPDF](https://mpdf.github.io/). It is **optional** — install mPDF only when your project needs PDF export.

### Install

```bash
composer require mpdf/mpdf:^8.2
```

### Create an exporter

One subclass per resource — do not create one exporter for all modules.

```php
// app/Libraries/UserPdfExporter.php
namespace App\Libraries;

class UserPdfExporter extends BasePdfExporter
{
    protected function buildHtml(array $data): string
    {
        return view('exports/users_pdf', ['users' => $data], ['saveData' => true]);
    }
}
```

For landscape or custom paper size, override the properties:

```php
class ReportPdfExporter extends BasePdfExporter
{
    protected string $orientation = 'L'; // Landscape
    protected string $paperSize   = 'A3';
}
```

For advanced layouts (letterhead, watermark, custom fonts), override `__construct()` — always call `parent::__construct()` first.

### Use in a controller

```php
public function exportPdf(): ResponseInterface
{
    $data = (new UserService())->findAll([])['data'];

    try {
        (new UserPdfExporter())->export($data, 'users-' . date('Ymd') . '.pdf');
        exit; // PDF streams directly — bypass CI4 response pipeline
    } catch (\RuntimeException $e) {
        AppLogger::error('pdf.export.failed', [], $e);
        return $this->error('Failed to generate PDF', 500);
    }
}
```

### Create a PDF template

Templates live in `app/Views/exports/`. They are plain HTML — **never extend any layout**.

```php
// app/Views/exports/users_pdf.php
<!DOCTYPE html>
<html>
<head>
  <style>
    body { font-family: sans-serif; font-size: 12px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #ddd; padding: 6px 8px; }
    th { background: #f3f4f6; }
  </style>
</head>
<body>
  <h2>Users</h2>
  <table>
    <thead><tr><th>No</th><th>Name</th><th>Email</th></tr></thead>
    <tbody>
      <?php foreach ($users as $i => $user): ?>
      <tr>
        <td><?= $i + 1 ?></td>
        <td><?= esc($user['name']) ?></td>
        <td><?= esc($user['email']) ?></td>
      </tr>
      <?php endforeach ?>
    </tbody>
  </table>
</body>
</html>
```

> **Always use `esc()`** for user-controlled data in templates — even inside PDFs.

### Notes

- One exporter subclass per resource. Do not build a generic "export anything" class.
- Templates in `app/Views/exports/` must not extend any CI4 layout — mPDF renders raw HTML.
- mPDF supports UTF-8 and a CSS subset — sufficient for tables and standard reports.
- For very large datasets, implement chunking in your subclass before calling `$this->pdf->WriteHTML()`.

---

## Compatibility Matrix

v2.0 is a **strict superset** of v1.x. No migrations required.

| Feature | v1.x | v2.0 |
|---|---|---|
| BE Layer (API, Service, Model) | ✅ | ✅ |
| Shield Auth (session + token) | ✅ | ✅ |
| Audit Trail | ✅ | ✅ |
| File Upload + Storage Drivers | ✅ | ✅ |
| Structured JSON Logging | ✅ | ✅ |
| Transformers | ✅ | ✅ |
| **SSO Layer (JWT RS256)** | ❌ | ✅ optional |
| **PDF Export (mPDF)** | ❌ | ✅ optional |

### Upgrading from v1.x

No migration needed. Copy these files into your existing v1.x project:

| File | Purpose |
|---|---|
| `app/Config/SSOConfig.php` | SSO configuration |
| `app/Libraries/JWTService.php` | JWT sign / verify |
| `app/Filters/SSOFilter.php` | SSO filter |
| `app/Libraries/BasePdfExporter.php` | PDF export base |

Then register `ssoFilter` in `app/Config/Filters.php`:

```php
'ssoFilter' => \App\Filters\SSOFilter::class,
```

Add the SSO keys to your `.env` (copy from `.env.example`).

Both features are disabled / unused until you explicitly activate them.

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
    protected $table                  = 'posts';
    protected $allowedFields          = ['title', 'body', 'user_id'];
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
    $routes->get('posts',           'Api\PostController::index');
    $routes->post('posts',          'Api\PostController::create');
    $routes->get('posts/(:num)',    'Api\PostController::show/$1');
    $routes->put('posts/(:num)',    'Api\PostController::update/$1');
    $routes->delete('posts/(:num)', 'Api\PostController::delete/$1');
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
| `curl` | Required if using `S3Driver` |
| `openssl` | Required for generating RSA key pairs (SSO setup, run once) |

---

## License

This project is open-sourced under the [MIT License](LICENSE).
