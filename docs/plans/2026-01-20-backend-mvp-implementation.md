# Backend MVP Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development to implement this plan task-by-task.

**Goal:** Build a PHP 8.3 native backend API for "hmt-admissions" that runs on memory data but simulates full behavior (auth, validation, errors) compliant with OpenAPI spec.

**Architecture:** Pure PHP, No Framework, Dependency Injection via Bootstrap, Repository Pattern (Memory/MySQL switchable), Middleware Pipeline.

**Tech Stack:** PHP 8.3, Built-in Server (for dev), JWT (HS256).

---

### Task 1: Project Skeleton & Environment

**Files:**
- Create: `public/index.php`
- Create: `.env.example`
- Create: `src/Config.php`
- Create: `src/Bootstrap.php`
- Create: `src/Utils/Uuid.php` (Helper for request IDs)

**Step 1: Create .env.example**
Create `.env.example` with:
```ini
APP_ENV=local
APP_DEBUG=true
JWT_SECRET=changeme_in_production_at_least_32_chars
REPO_TYPE=memory
```

**Step 2: Create Config Loader**
Create `src/Config.php` to load `.env` variables into `$_ENV` if not present. Simple key=value parsing.

**Step 3: Create Bootstrap Class**
Create `src/Bootstrap.php` with an `init()` method that loads Config and sets up error reporting.

**Step 4: Create Entry Point**
Create `public/index.php`.
- Require `src/Bootstrap.php`.
- Run `Bootstrap::init()`.
- Output "Hello World" for now.

**Step 5: Verify**
Run: `php -S localhost:8000 -t public`
Visit: `http://localhost:8000`
Expected: "Hello World"

---

### Task 2: Core Infrastructure (Router & Middleware)

**Files:**
- Create: `src/Request.php`
- Create: `src/Response.php`
- Create: `src/Router.php`
- Create: `src/Middleware/Pipeline.php`
- Create: `src/Middleware/CorsMiddleware.php`
- Create: `src/Middleware/RequestIdMiddleware.php`

**Step 1: Request & Response**
- `Request`: Wrapper for `$_SERVER`, `$_GET`, `$_POST`. Add `getParams()`, `getMethod()`, `getPath()`.
- `Response`: `json($data, $status=200)` and `error(...)` methods.

**Step 2: Middleware Infrastructure**
- `Pipeline`: Ability to pipe `$request` through an array of callables.
- `CorsMiddleware`: Handle `OPTIONS` request, add headers `Access-Control-Allow-Origin: *`.
- `RequestIdMiddleware`: Check `HTTP_X_REQUEST_ID` or generate new UUID. Set it on Response headers too.

**Step 3: Router**
- `Router`: Map `METHOD /path` to `[Controller::class, 'method']`.
- Support middleware stack per route or global.

**Step 4: Integration in index.php**
- Update `public/index.php` to instantiate `Router` and dispatch request.
- Define a test route `GET /test`.

**Step 5: Verify**
Run server.
Curl: `curl -v http://localhost:8000/test`
Expected: JSON response, CORS headers present, `X-Request-Id` present.

---

### Task 3: Error Handling & Standardization

**Files:**
- Create: `src/Exceptions/AppException.php`
- Create: `src/Exceptions/ValidationException.php` (Code: 400)
- Create: `src/Exceptions/AuthException.php` (Code: 401)
- Create: `src/Exceptions/NotFoundException.php` (Code: 404)
- Modify: `public/index.php`

**Step 1: Define Exceptions**
Create exception classes extending `Exception` but holding `errorCode` (string) and `details` (array).

**Step 2: Global Try-Catch**
Wrap `index.php` logic in `try { ... } catch (Throwable $e)`.
- Catch `AppException`: use its status code and payload.
- Catch other `Throwable`: return 500 `INTERNAL_ERROR`.

**Step 3: Verify**
Throw `new NotFoundException("Route not found")` in a test route.
Expected JSON:
```json
{
  "error": { "code": "NOT_FOUND", "message": "Route not found" },
  "request_id": "..."
}
```

---

### Task 4: Authentication (JWT & AuthController)

**Files:**
- Create: `src/Utils/Jwt.php` (HS256 implementation)
- Create: `src/Controllers/AuthController.php`
- Create: `src/Services/AuthService.php`
- Create: `src/Middleware/AuthMiddleware.php`
- Modify: `src/Router.php` (Register routes)

**Step 1: JWT Utils**
Implement `encode($payload)` and `decode($token)`. Use `hash_hmac('sha256')`.

**Step 2: AuthService**
- `login($email)`: Return token array.
- `anonymous()`: Return token array for new UUID user.

**Step 3: AuthController**
- `POST /v1/auth/anonymous`: Call Service, return JSON.
- `POST /v1/auth/login`: Validate email, Call Service.

**Step 4: AuthMiddleware**
- Check `Authorization: Bearer <token>`.
- Decode JWT.
- If invalid, throw `AuthException(AUTH_INVALID_TOKEN)`.
- If valid, attach `user_id` to `$request`.

**Step 5: Verify**
- `POST /v1/auth/anonymous` -> Get Token.
- Access protected route with Token -> 200 OK.
- Access with bad Token -> 401 Unauthorized.

---

### Task 5: Lesson Pack Feature (Repositories)

**Files:**
- Create: `src/Repositories/Interfaces/LessonPackRepositoryInterface.php`
- Create: `src/Repositories/Memory/MemoryLessonPackRepository.php`
- Create: `src/Services/LessonPackService.php`
- Create: `src/Controllers/LessonPackController.php`
- Create: `src/Utils/Validator.php`

**Step 1: Interface & Memory Repo**
- `getToday($examPath, $track, $subject)`
- `MemoryRepo`: Read `../hmt-admissions-spec/examples/lesson-pack-today.response.json`. Decode, maybe override `exam_path` to match request to simulate dynamic behavior.

**Step 2: Service & Controller**
- Controller calls Service -> Service calls Repo.
- Controller validates `exam_path` is required.

**Step 3: Register Route**
- `GET /v1/lesson-packs/today` (Apply AuthMiddleware).

**Step 4: Verify**
Curl with Token: `GET /v1/lesson-packs/today?exam_path=undergrad_joint`
Expected: The JSON content from the spec example.

---

### Task 6: Attempts Feature

**Files:**
- Create: `src/Repositories/Interfaces/AttemptRepositoryInterface.php`
- Create: `src/Repositories/Memory/MemoryAttemptRepository.php`
- Create: `src/Services/AttemptService.php`
- Create: `src/Controllers/AttemptsController.php`

**Step 1: Repo & Service**
- `MemoryRepo`: Just return a success structure (mocking the DB save).
- `Service`:
  - Receive answer.
  - Return fixed result from `attempt.response.json` OR simple logic: if `answer == "B"` (mock correct) then `correct=true`.

**Step 2: Controller**
- `POST /v1/attempts`
- Validate `question_id`, `answer`, `elapsed_ms`.

**Step 3: Verify**
Curl POST with Token.
Expected: `AttemptResult` JSON.

---

### Task 7: Progress & Review Features

**Files:**
- Create: `src/Repositories/Interfaces/ProgressRepositoryInterface.php`
- Create: `src/Repositories/Memory/MemoryProgressRepository.php`
- Create: `src/Controllers/ProgressController.php`
- Create: `src/Controllers/ReviewController.php` (and related Repos)

**Step 1: Progress**
- `GET /v1/progress`.
- MemoryRepo returns `progress.response.json`.

**Step 2: Review**
- `GET /v1/review/queue`.
- `POST /v1/review/complete`.
- Wire up to use `review-queue.response.json` etc.

**Step 3: Health Check**
- `GET /health` -> `{"status": "ok"}`.

---

### Task 8: Documentation & Cleanup

**Files:**
- Modify: `README.md`
- Modify: `src/Bootstrap.php` (Finalize DI container)

**Step 1: Bootstrap DI**
Ensure all Repos are loaded based on `REPO_TYPE`.

**Step 2: README**
- How to start: `php -S 0.0.0.0:8000 -t public`
- Curl examples for: Auth, Lesson Pack, Attempt.
- Explanation of Memory Mode.

**Step 3: Import Tool (Skeleton)**
- Create `tools/import_questions.php` skeleton (empty for now, as Phase 2 task).

**Step 4: Final Verification**
Run full user flow:
1. Get Anon Token.
2. Get Today's Pack.
3. Submit Attempt.
4. Check Progress.
