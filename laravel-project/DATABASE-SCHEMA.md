# Database schema reference

Generated from the 26 migrations in `database/migrations/`. Oracle types are what `yajra/laravel-oci8` produces from each Laravel column type.

**This document is for review only.** The schema is created by `php artisan migrate`, which is the authoritative source. Do not hand-build tables from this file.

Columns marked ⚠ **CLINICAL** hold counseling and safety data. They are owned entirely by this application and must never be sent to, read from, cached by, or logged against the CMS. See the hard constraint in `CLAUDE.md` and section 5 of `API-REQUIREMENTS.md`.

---

## Application tables

### `counselors`

| Column | Oracle type | Constraints |
|---|---|---|
| `id` | NUMBER(20) | PRIMARY KEY |
| `counselor_code` | VARCHAR2(255) | UNIQUE, NOT NULL |
| `name` | VARCHAR2(255) | NOT NULL |
| `email` | VARCHAR2(255) | UNIQUE, NOT NULL |
| `password` | VARCHAR2(255) | NOT NULL |
| `active` | NUMBER(1) | NOT NULL, DEFAULT 1 |
| `remember_token` | VARCHAR2(100) | NULL |
| `created_at`, `updated_at` | TIMESTAMP | NULL |
| `cms_counselor_id` | VARCHAR2(255) | UNIQUE, NULL, _added after launch_ |

### `students`

| Column | Oracle type | Constraints |
|---|---|---|
| `id` | NUMBER(20) | PRIMARY KEY |
| `roll_number` | VARCHAR2(255) | UNIQUE, NOT NULL |
| `password` | VARCHAR2(255) | NOT NULL |
| `name` | VARCHAR2(255) | NOT NULL |
| `dob` | DATE | NULL |
| `phone` | VARCHAR2(255) | NULL |
| `department` | VARCHAR2(255) | NULL |
| `semester` | VARCHAR2(255) | NULL |
| **`session_status`** ⚠ CLINICAL | VARCHAR2(255) | NOT NULL, DEFAULT 'Ongoing' |
| **`suicide_intent`** ⚠ CLINICAL | NUMBER(1) | NOT NULL, DEFAULT 0 |
| **`suicide_alert`** ⚠ CLINICAL | NUMBER(1) | NOT NULL, DEFAULT 0 |
| **`next_session_date`** ⚠ CLINICAL | DATE | NULL |
| **`suggested_next_session_date`** ⚠ CLINICAL | DATE | NULL |
| **`next_session_date_status`** ⚠ CLINICAL | VARCHAR2(255) | NOT NULL, DEFAULT 'none' |
| `suggested_by_counselor_id` | NUMBER(20) | NULL, FK → `counselors`, ON DELETE SET NULL |
| **`nature_of_consent`** ⚠ CLINICAL | VARCHAR2(255) | NOT NULL, DEFAULT 'Standard' |
| `last_attended_session` | VARCHAR2(255) | NULL |
| **`agreement_accepted`** ⚠ CLINICAL | NUMBER(1) | NOT NULL, DEFAULT 0 |
| `cgpa` | NUMBER(3, 2) | NULL |
| **`presenting_concerns`** ⚠ CLINICAL | CLOB (JSON) | NULL |
| `remember_token` | VARCHAR2(100) | NULL |
| `created_at`, `updated_at` | TIMESTAMP | NULL |
| `cms_student_id` | VARCHAR2(255) | UNIQUE, NULL, _added after launch_ |

### `attachments`

| Column | Oracle type | Constraints |
|---|---|---|
| `id` | NUMBER(20) | PRIMARY KEY |
| `attachment_code` | VARCHAR2(255) | UNIQUE, NOT NULL |
| `student_id` | NUMBER(20) | NULL, FK → `students`, ON DELETE CASCADE, _relaxed to NULL later_ |
| `file_name` | VARCHAR2(255) | NOT NULL |
| `mime_type` | VARCHAR2(255) | NOT NULL |
| `url` | VARCHAR2(255) | NOT NULL |
| `uploaded_at` | TIMESTAMP | NULL |
| `created_at`, `updated_at` | TIMESTAMP | NULL |
| `faculty_staff_id` | NUMBER(20) | NULL, FK → `faculty_staff`, ON DELETE SET NULL, _added after launch_ |

### `appointments`

| Column | Oracle type | Constraints |
|---|---|---|
| `id` | NUMBER(20) | PRIMARY KEY |
| `request_code` | VARCHAR2(255) | UNIQUE, NOT NULL |
| `student_id` | NUMBER(20) | NULL, FK → `students`, ON DELETE CASCADE, _relaxed to NULL later_ |
| `student_name` | VARCHAR2(255) | NULL, _relaxed to NULL later_ |
| `roll_number` | VARCHAR2(255) | NULL, _relaxed to NULL later_ |
| `counselor_id` | NUMBER(20) | NOT NULL, FK → `counselors` |
| `counselor_name` | VARCHAR2(255) | NOT NULL |
| `date` | DATE | NOT NULL |
| `slot` | VARCHAR2(255) | NOT NULL |
| `status` | VARCHAR2(255) | NOT NULL, DEFAULT 'pending' |
| **`suicidal_thoughts`** ⚠ CLINICAL | NUMBER(1) | NOT NULL, DEFAULT 0 |
| **`mental_health_questions`** ⚠ CLINICAL | CLOB | NULL |
| **`presenting_concerns`** ⚠ CLINICAL | CLOB (JSON) | NULL |
| `support_group_topics` | CLOB (JSON) | NULL |
| `emergency_contact_name` | VARCHAR2(255) | NOT NULL |
| `emergency_contact_phone` | VARCHAR2(255) | NOT NULL |
| `notes` | CLOB | NULL |
| **`counsellor_notes`** ⚠ CLINICAL | CLOB | NULL |
| `created_at`, `updated_at` | TIMESTAMP | NULL |
| `faculty_staff_id` | NUMBER(20) | NULL, FK → `faculty_staff`, ON DELETE SET NULL, _added after launch_ |
| `faculty_staff_name` | VARCHAR2(255) | NULL, _added after launch_ |
| `employee_id` | VARCHAR2(255) | NULL, _added after launch_ |
| `pending_reschedule_date` | DATE | NULL, _added after launch_ |
| `pending_reschedule_slot` | VARCHAR2(255) | NULL, _added after launch_ |
| `reschedule_requested_by` | VARCHAR2(255) | NULL, _added after launch_ |
| `status_before_reschedule` | VARCHAR2(255) | NULL, _added after launch_ |

### `appointment_reschedules`

| Column | Oracle type | Constraints |
|---|---|---|
| `id` | NUMBER(20) | PRIMARY KEY |
| `appointment_id` | NUMBER(20) | NOT NULL, FK → `appointments`, ON DELETE CASCADE |
| `from_date` | DATE | NOT NULL |
| `from_slot` | VARCHAR2(255) | NOT NULL |
| `to_date` | DATE | NOT NULL |
| `to_slot` | VARCHAR2(255) | NOT NULL |
| `by` | VARCHAR2(255) | NOT NULL |
| `at` | TIMESTAMP | NOT NULL |

### `blocked_slots`

| Column | Oracle type | Constraints |
|---|---|---|
| `id` | NUMBER(20) | PRIMARY KEY |
| `blocked_code` | VARCHAR2(255) | UNIQUE, NOT NULL |
| `counselor_id` | NUMBER(20) | NOT NULL, FK → `counselors`, ON DELETE CASCADE |
| `date` | DATE | NOT NULL |
| `slot` | VARCHAR2(255) | NOT NULL |
| `reason` | VARCHAR2(255) | NULL |
| `created_at`, `updated_at` | TIMESTAMP | NULL |

### `session_logs`

| Column | Oracle type | Constraints |
|---|---|---|
| `id` | NUMBER(20) | PRIMARY KEY |
| `session_code` | VARCHAR2(255) | UNIQUE, NOT NULL |
| `student_id` | NUMBER(20) | NULL, FK → `students`, ON DELETE CASCADE, _relaxed to NULL later_ |
| `date` | DATE | NOT NULL |
| `time` | VARCHAR2(255) | NOT NULL |
| `notes` | CLOB | NULL |
| `status` | VARCHAR2(255) | NOT NULL, DEFAULT 'scheduled' |
| `started_at` | TIMESTAMP | NULL |
| `created_at`, `updated_at` | TIMESTAMP | NULL |
| `faculty_staff_id` | NUMBER(20) | NULL, FK → `faculty_staff`, ON DELETE SET NULL, _added after launch_ |

### `support_group_requests`

| Column | Oracle type | Constraints |
|---|---|---|
| `id` | NUMBER(20) | PRIMARY KEY |
| `request_code` | VARCHAR2(255) | UNIQUE, NOT NULL |
| `student_id` | NUMBER(20) | NULL, FK → `students`, ON DELETE CASCADE, _relaxed to NULL later_ |
| `student_name` | VARCHAR2(255) | NULL, _relaxed to NULL later_ |
| `roll_number` | VARCHAR2(255) | NULL, _relaxed to NULL later_ |
| `reason` | CLOB | NOT NULL |
| `topics` | CLOB (JSON) | NULL |
| `notes` | CLOB | NULL |
| `status` | VARCHAR2(255) | NOT NULL, DEFAULT 'pending' |
| `created_at`, `updated_at` | TIMESTAMP | NULL |
| `faculty_staff_id` | NUMBER(20) | NULL, FK → `faculty_staff`, ON DELETE SET NULL, _added after launch_ |
| `faculty_staff_name` | VARCHAR2(255) | NULL, _added after launch_ |
| `employee_id` | VARCHAR2(255) | NULL, _added after launch_ |

### `tasks`

| Column | Oracle type | Constraints |
|---|---|---|
| `id` | NUMBER(20) | PRIMARY KEY |
| `task_code` | VARCHAR2(255) | UNIQUE, NOT NULL |
| `title` | VARCHAR2(255) | NOT NULL |
| `description` | CLOB | NULL |
| `date` | DATE | NOT NULL |
| `time` | VARCHAR2(255) | NULL |
| `counselor_id` | NUMBER(20) | NULL, FK → `counselors`, ON DELETE SET NULL |
| `status` | VARCHAR2(255) | NOT NULL, DEFAULT 'pending' |
| `created_at`, `updated_at` | TIMESTAMP | NULL |

### `news`

| Column | Oracle type | Constraints |
|---|---|---|
| `id` | NUMBER(20) | PRIMARY KEY |
| `news_code` | VARCHAR2(255) | UNIQUE, NOT NULL |
| `title` | VARCHAR2(255) | NOT NULL |
| `body` | CLOB | NOT NULL |
| `event_date` | DATE | NULL |
| `counselor_id` | NUMBER(20) | NULL, FK → `counselors`, ON DELETE SET NULL |
| `counselor_name` | VARCHAR2(255) | NULL |
| `published` | NUMBER(1) | NOT NULL, DEFAULT 1 |
| `created_at`, `updated_at` | TIMESTAMP | NULL |

### `notifications`

| Column | Oracle type | Constraints |
|---|---|---|
| `id` | NUMBER(20) | PRIMARY KEY |
| `notification_code` | VARCHAR2(255) | UNIQUE, NOT NULL |
| `to_role` | VARCHAR2(255) | NOT NULL |
| `to_student_id` | NUMBER(20) | NULL, FK → `students`, ON DELETE CASCADE |
| `to_counselor_id` | NUMBER(20) | NULL, FK → `counselors`, ON DELETE CASCADE |
| `title` | VARCHAR2(255) | NOT NULL |
| `message` | CLOB | NOT NULL |
| `type` | VARCHAR2(255) | NOT NULL, DEFAULT 'info' |
| `read_at` | TIMESTAMP | NULL |
| `meta` | CLOB (JSON) | NULL |
| `created_at`, `updated_at` | TIMESTAMP | NULL |
| `to_faculty_staff_id` | NUMBER(20) | NULL, FK → `faculty_staff`, ON DELETE CASCADE, _added after launch_ |

### `policies`

| Column | Oracle type | Constraints |
|---|---|---|
| `id` | NUMBER(20) | PRIMARY KEY |
| `policy_code` | VARCHAR2(255) | UNIQUE, NOT NULL |
| `slug` | VARCHAR2(255) | UNIQUE, NOT NULL |
| `title` | VARCHAR2(255) | NOT NULL |
| `description` | VARCHAR2(255) | NULL |
| `body` | CLOB | NOT NULL |
| `counselor_id` | NUMBER(20) | NULL, FK → `counselors`, ON DELETE SET NULL |
| `counselor_name` | VARCHAR2(255) | NULL |
| `created_at`, `updated_at` | TIMESTAMP | NULL |

### `faculty_staff`

| Column | Oracle type | Constraints |
|---|---|---|
| `id` | NUMBER(20) | PRIMARY KEY |
| `employee_id` | VARCHAR2(255) | UNIQUE, NOT NULL |
| `cms_employee_id` | VARCHAR2(255) | UNIQUE, NULL |
| `password` | VARCHAR2(255) | NOT NULL |
| `name` | VARCHAR2(255) | NOT NULL |
| `designation` | VARCHAR2(255) | NULL |
| `department` | VARCHAR2(255) | NULL |
| `email` | VARCHAR2(255) | NULL |
| `phone` | VARCHAR2(255) | NULL |
| `remember_token` | VARCHAR2(100) | NULL |
| `created_at`, `updated_at` | TIMESTAMP | NULL |

### `student_referrals`

| Column | Oracle type | Constraints |
|---|---|---|
| `id` | NUMBER(20) | PRIMARY KEY |
| `referral_code` | VARCHAR2(255) | UNIQUE, NOT NULL |
| `faculty_staff_id` | NUMBER(20) | NOT NULL, FK → `faculty_staff`, ON DELETE CASCADE |
| `referred_by_name` | VARCHAR2(255) | NOT NULL |
| `referred_by_designation` | VARCHAR2(255) | NULL |
| `referred_by_department` | VARCHAR2(255) | NULL |
| `student_id` | NUMBER(20) | NULL, FK → `students`, ON DELETE SET NULL |
| `student_name` | VARCHAR2(255) | NOT NULL |
| `student_roll_number` | VARCHAR2(255) | NOT NULL |
| `reason` | CLOB | NOT NULL |
| `status` | VARCHAR2(255) | NOT NULL, DEFAULT 'pending' |
| `counselor_notes` | CLOB | NULL |
| `reviewed_by_counselor_id` | NUMBER(20) | NULL, FK → `counselors`, ON DELETE SET NULL |
| `created_at`, `updated_at` | TIMESTAMP | NULL |

---

## Framework tables

Created by Laravel's own migrations. Listed so a DBA reviewing the schema knows what they are and why they exist.

`sessions` and `cache` are load-bearing in this deployment, not optional: `SESSION_DRIVER=database` and `CACHE_STORE=database`. The one-time-use guarantee on SSO tokens is enforced through `cache`, so if the app runs on more than one web node, that table must be shared across all of them.

### `sessions`

| Column | Oracle type | Constraints |
|---|---|---|
| `id` | VARCHAR2(255) | NOT NULL |
| `user_id` | unsignedBigInteger | NULL |
| `user_type` | VARCHAR2(20) | NULL |
| `ip_address` | VARCHAR2(45) | NULL |
| `user_agent` | CLOB | NULL |
| `payload` | CLOB | NOT NULL |
| `last_activity` | NUMBER(10) | NOT NULL |

### `cache`

| Column | Oracle type | Constraints |
|---|---|---|
| `key` | VARCHAR2(255) | NOT NULL |
| `value` | mediumText | NOT NULL |
| `expiration` | NUMBER(10) | NOT NULL |

### `cache_locks`

| Column | Oracle type | Constraints |
|---|---|---|
| `key` | VARCHAR2(255) | NOT NULL |
| `owner` | VARCHAR2(255) | NOT NULL |
| `expiration` | NUMBER(10) | NOT NULL |

### `jobs`

| Column | Oracle type | Constraints |
|---|---|---|
| `id` | NUMBER(20) | PRIMARY KEY |
| `queue` | VARCHAR2(255) | NOT NULL |
| `payload` | CLOB | NOT NULL |
| `attempts` | unsignedTinyInteger | NOT NULL |
| `reserved_at` | NUMBER(10) | NULL |
| `available_at` | NUMBER(10) | NOT NULL |
| `created_at` | NUMBER(10) | NOT NULL |

### `job_batches`

| Column | Oracle type | Constraints |
|---|---|---|
| `id` | VARCHAR2(255) | NOT NULL |
| `name` | VARCHAR2(255) | NOT NULL |
| `total_jobs` | NUMBER(10) | NOT NULL |
| `pending_jobs` | NUMBER(10) | NOT NULL |
| `failed_jobs` | NUMBER(10) | NOT NULL |
| `failed_job_ids` | CLOB | NOT NULL |
| `options` | mediumText | NULL |
| `cancelled_at` | NUMBER(10) | NULL |
| `created_at` | NUMBER(10) | NOT NULL |
| `finished_at` | NUMBER(10) | NULL |

### `failed_jobs`

| Column | Oracle type | Constraints |
|---|---|---|
| `id` | NUMBER(20) | PRIMARY KEY |
| `uuid` | VARCHAR2(255) | UNIQUE, NOT NULL |
| `connection` | CLOB | NOT NULL |
| `queue` | CLOB | NOT NULL |
| `payload` | CLOB | NOT NULL |
| `exception` | CLOB | NOT NULL |
| `failed_at` | TIMESTAMP | NOT NULL |

---

## Removed

Created by an earlier migration and dropped again; not present in a current database.

- `counseling_requests`
