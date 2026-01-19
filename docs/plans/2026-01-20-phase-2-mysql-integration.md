# Phase 2: MySQL Integration Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development to implement this plan task-by-task.

**Goal:** Transition the backend from Memory Mode to MySQL Mode by implementing database schemas, PDO connectivity, and persistent repositories, enabling full data persistence.

**Architecture:** Repository Pattern (swapping Memory for MySQL implementations), Raw PDO (No ORM), Migration-based Schema Management.

**Tech Stack:** PHP 8.3, MySQL 8.0, PDO.

---

### Task 1: Database Infrastructure

**Files:**
- Create: `src/Storage/Db.php`
- Create: `db/schema.sql`
- Create: `db/init_db.php` (Simple script to run schema)
- Modify: `.env.example`
- Modify: `src/Bootstrap.php`

**Step 1: DB Configuration**
Add MySQL vars to `.env.example`:
```ini
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=hmt_admissions
DB_USER=root
DB_PASS=root
```

**Step 2: Db Class (Singleton PDO)**
Create `src/Storage/Db.php`:
- `getInstance()`: Returns PDO connection.
- Use `Config::get()` to load credentials.
- Set `PDO::ATTR_ERRMODE` to `EXCEPTION`.
- Set `PDO::ATTR_DEFAULT_FETCH_MODE` to `ASSOC`.

**Step 3: Schema Definition**
Create `db/schema.sql` with tables:
- `users` (id, email, created_at)
- `questions` (id, exam_path, track, subject, question_type, stem, options_json, difficulty, tags_json, created_at...)
- `attempts` (id, user_id, question_id, answer_json, correct, elapsed_ms, created_at)
- `user_progress` (user_id, streak_current, subject_mastery_json, last_activity_at...)
- `review_queue` (user_id, question_id, due_at, ease_factor...)

**Step 4: Init Script**
Create `db/init_db.php` to read `schema.sql` and execute it via `Db::getInstance()`.

**Step 5: Verify**
Run `php db/init_db.php`. Check MySQL to see if tables exist.

---

### Task 2: User Persistence (Auth)

**Files:**
- Create: `src/Repositories/Interfaces/UserRepositoryInterface.php`
- Create: `src/Repositories/MySql/MySqlUserRepository.php`
- Modify: `src/Services/AuthService.php`
- Modify: `src/Bootstrap.php`

**Step 1: User Repo**
- Interface: `save(User $user)`, `findById($id)`, `findByEmail($email)`.
- MySqlRepo: Implement SQL INSERT/SELECT.

**Step 2: Update AuthService**
- Inject `UserRepositoryInterface`.
- `anonymous()`: Create User entity -> Repo->save() -> Issue Token.
- `login($email)`: Repo->findByEmail() -> if null create new -> Issue Token.

**Step 3: Verify**
- `POST /v1/auth/anonymous` -> Token.
- Check `users` table -> New row should exist.

---

### Task 3: Question Import & Repository

**Files:**
- Create: `src/Repositories/Interfaces/QuestionRepositoryInterface.php`
- Create: `src/Repositories/MySql/MySqlQuestionRepository.php`
- Modify: `tools/import_questions.php`

**Step 1: Question Repo**
- `save(array $data)`: INSERT INTO questions.
- `findByFilters($examPath, $track, $subject, $limit)`: SELECT ... ORDER BY RAND().

**Step 2: Import Tool**
- Implement `tools/import_questions.php`.
- Read `../hmt-admissions-spec/examples/lesson-pack-today.response.json` (extract items) OR a new `sample_questions.json`.
- Loop through items -> Repo->save().
- Handle JSON fields (`options`, `tags`) using `json_encode`.

**Step 3: Verify**
- Run `php tools/import_questions.php`.
- Check `questions` table -> Data should exist.

---

### Task 4: Lesson Pack (MySQL)

**Files:**
- Create: `src/Repositories/MySql/MySqlLessonPackRepository.php`
- Modify: `src/Bootstrap.php`

**Step 1: MySqlLessonPackRepository**
- Inject `QuestionRepositoryInterface`.
- `getToday(...)`:
  - Call `QuestionRepository->findByFilters(...)` to get 10 random questions.
  - Construct `LessonPack` array structure.

**Step 2: Switch Repo**
- Update `Bootstrap.php`: If `REPO_TYPE=mysql`, use `MySqlLessonPackRepository`.

**Step 3: Verify**
- `GET /v1/lesson-packs/today` -> Should return questions from DB.

---

### Task 5: Attempts & Progress (MySQL)

**Files:**
- Create: `src/Repositories/MySql/MySqlAttemptRepository.php`
- Create: `src/Repositories/MySql/MySqlProgressRepository.php`
- Modify: `src/Services/AttemptService.php` (Update logic to use Repo for correctness check if needed)

**Step 1: Attempt Repo**
- `save($attempt)`: INSERT INTO attempts.

**Step 2: Progress Repo**
- `getByUserId($id)`: SELECT FROM user_progress.
- `save($progress)`: INSERT ON DUPLICATE KEY UPDATE.

**Step 3: Logic Update**
- Ensure `AttemptService` calculates `correct` based on Question data (fetch Question from DB to compare answer).
- Update Progress after Attempt (Service logic).

**Step 4: Verify**
- `POST /v1/attempts` -> Check `attempts` table.
- `GET /v1/progress` -> Check `user_progress` table.

---

### Task 6: Review Queue (MySQL)

**Files:**
- Create: `src/Repositories/MySql/MySqlReviewRepository.php`

**Step 1: Repo**
- `getQueue($userId)`: SELECT ... FROM review_queue WHERE due_at <= NOW().
- `completeReview($userId, $items)`: Update `review_queue` (delete or update due_at).

**Step 2: Verify**
- Manually insert review item in DB.
- `GET /v1/review/queue` -> Should see item.

---

### Task 7: Cleanup & Final Switch

**Files:**
- Modify: `.env` (Set REPO_TYPE=mysql)
- Modify: `README.md` (Add DB setup instructions)

**Step 1: Final Config**
- Default `REPO_TYPE` to `mysql` in `.env.example`? Or keep memory default but document switch.

**Step 2: Documentation**
- Add "Database Setup" section to README.
- `mysql -u root -p < db/schema.sql`
- `php tools/import_questions.php`

**Step 3: Final Test**
- Run full flow against local MySQL.
