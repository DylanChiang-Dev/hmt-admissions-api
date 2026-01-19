# Backend MVP Design Document
Date: 2026-01-20
Project: hmt-admissions-api
Status: Approved

## 1. Overview
Build a PHP 8.3 native backend API for "Hong Kong, Macao, and Taiwan Admissions" (hmt-admissions).
The goal is a Phase 1 MVP that runs on memory/mock data but simulates full API behavior (auth, validation, error handling) compliant with the `hmt-admissions-spec` OpenAPI definition. Phase 2 will introduce MySQL without changing Controller logic.

## 2. Architecture Principles
- **No Framework**: Pure PHP 8.3.
- **Dependency Injection**: Manual DI in `Bootstrap.php` to switch between Memory and MySQL repositories.
- **Spec First**: Strict adherence to `openapi.yaml`.
- **Stateless**: JWT (HS256) for authentication.

## 3. Directory Structure
```
hmt-admissions-api/
  public/
    index.php          # Entry point, Global Try-Catch
  src/
    Bootstrap.php      # DI Container, Environment setup
    Router.php         # Request routing
    Response.php       # JSON standardization
    Request.php        # Input abstraction
    Config.php         # Env loader
    Models/            # DTOs/Entities (Question, Attempt)
    Utils/
      Uuid.php
      Jwt.php
      Validator.php
    Middleware/
      Pipeline.php     # Chain runner
      CorsMiddleware.php
      RequestIdMiddleware.php
      JsonBodyMiddleware.php
      AuthMiddleware.php
    Controllers/       # Handle HTTP I/O
      AuthController.php
      LessonPackController.php
      AttemptsController.php
      ProgressController.php
      ReviewController.php
      HealthController.php
    Services/          # Business Logic
      AuthService.php
      LessonPackService.php
      AttemptService.php
    Repositories/      # Data Access
      Interfaces/      # Contracts (e.g., UserRepositoryInterface)
      Memory/          # Phase 1: Returns JSON from spec examples
      MySql/           # Phase 2: Database implementation
  db/
    migrations/
    seeds/
  tests/
  .env.example
  composer.json
  README.md
```

## 4. Core Components

### 4.1. Bootstrap & DI
- `Bootstrap::init()` loads environment.
- `Bootstrap::getContainer()` returns an array or object registry.
- **Switching Logic**:
  ```php
  $repoType = getenv('REPO_TYPE') ?: 'memory';
  if ($repoType === 'mysql') {
      $container['question_repo'] = new MySqlQuestionRepository($pdo);
  } else {
      $container['question_repo'] = new MemoryQuestionRepository(); // Loads from json files
  }
  ```

### 4.2. Middleware Pipeline
Execution order:
1.  **CorsMiddleware**: Handles `OPTIONS`, sets `Access-Control-Allow-Origin`.
2.  **RequestIdMiddleware**: Checks `X-Request-Id` or generates UUID v4.
3.  **JsonBodyMiddleware**: Parses `php://input` to `$_POST` or Request object.
4.  **AuthMiddleware**:
    - Applied only to protected routes.
    - Decodes JWT.
    - Sets `$request->user_id`.
    - Throws `AuthException` (401) on failure.

### 4.3. Error Handling
Global `try-catch` in `index.php` catches:
- `AppException` (Base custom exception)
- `ValidationException` (400)
- `AuthException` (401)
- `NotFoundException` (404)
- `Throwable` (500)

Output format must match spec:
```json
{
  "error": {
    "code": "AUTH_INVALID_TOKEN",
    "message": "Token expired or invalid",
    "details": {}
  },
  "request_id": "uuid..."
}
```

## 5. Phase 1 Implementation Details

### 5.1. Auth Strategy
- **JWT**: Implement a simple `HS256` encoder/decoder in `src/Utils/Jwt.php`.
- **Login**: Accept any valid email format. Return a *valid* signed JWT.
- **Anonymous**: `/v1/auth/anonymous` generates a random UUID user_id and signs it.

### 5.2. Data Simulation (Memory Repositories)
- `MemoryLessonPackRepository` reads from `../hmt-admissions-spec/examples/lesson-pack-today.response.json`.
- `MemoryAttemptRepository` does not persist but returns successful `AttemptResult` based on input.

### 5.3. Validation
- `Validator` class checks:
  - `exam_path` against enum list.
  - `email` format.
  - `required` fields.

## 6. Phase 2 Transition (Future)
- Create `MySql*Repository` classes implementing the same interfaces.
- Add `db/schema.sql` and migrations.
- Switch `REPO_TYPE=mysql` in `.env`.
