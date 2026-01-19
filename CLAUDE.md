# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands
- **Start Server (Local)**: `php -S localhost:8000 -t public`
- **Start Server (Docker)**: `docker-compose up -d --build`
- **Init Database**: `php db/init_db.php` (or `docker-compose exec app php db/init_db.php`)
- **Import Questions**: `php tools/import_questions.php`
- **Test API**: Use `curl` (see README.md for examples)
- **Check Syntax**: `find src -name "*.php" -exec php -l {} \;`

## Architecture
- **Type**: Native PHP 8.3 REST API (No Framework).
- **Entry Point**: `public/index.php` handles all requests via Global Try-Catch.
- **Dependency Injection**: `src/Bootstrap.php` acts as the container.
  - Controls `REPO_TYPE` switch (MySQL vs Memory).
- **Layered Structure**:
  - `Controllers/`: HTTP I/O, Validation, call Services.
  - `Services/`: Business Logic, call Repositories.
  - `Repositories/`: Data Access (Interface-based).
    - `Interfaces/`: Contracts.
    - `MySql/`: Real DB implementation (PDO).
    - `Memory/`: Mock implementation for testing/prototyping.
- **Middleware Pipeline**: `Router` -> `Pipeline` -> `Cors` -> `RequestId` -> `Auth` -> `Controller`.
- **Database**: Raw PDO Singleton (`src/Storage/Db.php`).

## Code Style & Conventions
- **PHP Version**: 8.3 Strict Types.
- **API Style**: JSON response, Snake Case fields (`user_id`, `created_at`).
- **Error Handling**: Throw `AppException` (or subclasses `ValidationException`, `AuthException`).
  - Global handler formats to JSON: `{"error": {"code": "...", "message": "..."}}`.
- **Auth**: Stateless JWT (HS256).
- **Principles**: KISS, No ORM, Manual DI.
