# BNU Counselor System — CMS Integration Architecture
**Status:** Draft for review — architecture to be agreed before any implementation, per your Section 22.
**Based on:** the uploaded `counselor.zip` codebase, plus a review of `cms.bnu.edu.pk/login` and its "Forgot Password" target.

This covers Phase 1 (existing system analysis) and a first pass at Phases 2–4 (integration requirements, architecture, auth). It's scoped to the parts that actually drive the CMS-integration decision, not a line-by-line audit of every controller and view.

> **Hard constraint — applies to every task in this document:** `suicide_intent`, `suicide_alert`, `presenting_concerns`, `nature_of_consent`, `agreement_accepted`, `session_status`, `next_session_date`, `suggested_next_session_date`, and `next_session_date_status` on `students` must never appear in any request, response, cache entry, or log line that touches the CMS integration. If a task below seems to require exposing one of these to CMS, stop and flag it instead of implementing it.

---

## 1. Current architecture (as built)

### 1.1 Stack
| Layer | What's actually there |
|---|---|
| Backend | Laravel 11.31, PHP 8.2 |
| Database | Oracle, via `yajra/laravel-oci8` — `.env` currently points at a local dev instance (`FREEPDB1`) |
| Frontend | Server-rendered Blade (33 views) + Tailwind + Vite. No Vue/React/Inertia/Livewire — a classic monolith, not an SPA |
| Auth | Two independent Eloquent session guards: `student` and `counselor` |
| API layer | None — `routes/api.php` doesn't exist |
| SSO/OAuth packages | None installed — no Sanctum, no Passport |

### 1.2 Auth flow today
Both students and counselors self-register directly in the Counselor System with their own bcrypt-hashed password (`StudentAuthController@register`, `CounselorAuthController@register`), stored in `students.password` / `counselors.password`. Login is `Auth::guard('student')->attempt(['roll_number' => ..., 'password' => ...])` for students, email+password for counselors. This is exactly the "separate account" pattern the CMS integration needs to remove.

### 1.3 Data model
| Table | Owns |
|---|---|
| `students` | Identity fields (roll_number, name, dob, phone, department, semester, cgpa) **mixed with** clinical/counseling fields (`suicide_intent`, `suicide_alert`, `presenting_concerns`, `nature_of_consent`, `agreement_accepted`, `session_status`, `next_session_date`) |
| `counselors` | counselor_code, name, email, password, active — fully local, no external linkage |
| `appointments` | Booking data; already **snapshots** `student_name` + `roll_number` at booking time alongside the `student_id` foreign key |
| `attachments`, `blocked_slots`, `session_logs`, `support_group_requests`, `tasks`, `news`, `notifications`, `policies` | Counseling-operational — clearly stays owned by the Counselor System |

There's also an unused default `users` table/migration (Laravel boilerplate, never referenced in `app/` or `routes/`) — harmless, safe to drop whenever convenient.

### 1.4 Authorization
Spot-checked `Student\AppointmentController`: it does enforce ownership (`abort_unless($appointment->student_id === $student->id, 403)` on show/cancel/reschedule) — that's the IDOR protection Section 11 asks about, and it's already there. I only reviewed this one controller in depth; worth auditing the rest of the student- and counselor-facing controllers for the same pattern before go-live rather than assuming it's uniform everywhere.

### 1.5 The finding that should drive the redesign
`students` conflates two very different kinds of data in one table: CMS-owned identity/academic fields, and highly sensitive counseling data — including **suicide risk flags**. These need to be architecturally separated, not just conceptually. Identity fields become CMS-sourced (§3). The clinical fields must stay local to the Counselor System, under tighter access control than the rest of the schema, and must **never** be included in any payload sent to or synced with the CMS — a general university system used by registrar/fees/attendance staff is the wrong audience for that data. This isn't in your original spec, but it's the single most consequential decision in this integration, so I'm flagging it before anything else.

Also: there's no `email` field on `students` today, only `roll_number` — relevant to how identity gets keyed once CMS is the source of truth (§4).

One more thing worth a look before this goes anywhere near production: `.env` carries a real (if clearly local-dev) Oracle password. It's correctly listed in `.gitignore`, so this isn't a repo-hygiene problem — but the zip archive itself carries it, so treat any copy of this project directory as sensitive, and don't carry this credential forward once you're pointed at a real environment.

---

## 2. What the live CMS shows (external recon — not a substitute for BNU IT confirming this directly)

- `cms.bnu.edu.pk/login` is a plain username + password form. No visible "Sign in with…" / SAML / OAuth option.
- Its "Forgot Password?" link points to `secure.bnu.edu.pk`, which turns out to be a **Windows Server RD Web Access password-change page tied to an Active Directory domain** — accounts shown in `bnu\<registration-number>` format.

This suggests BNU's actual identity backbone is Active Directory, with the CMS most likely validating credentials against it rather than owning an independent password store. That doesn't change your integration path in practice — you'll only ever talk to the CMS, never to BNU's AD directly — but it's a concrete question worth putting to BNU IT: *do they run ADFS or any SAML/OIDC provider in front of AD?* If yes, there's a stronger enterprise SSO option available. If no — likely, for a university IT department this size — the pragmatic path in §4 is the right default regardless.

---

## 3. Known / Assumption / Needed-from-BNU-IT

| | Item | Status |
|---|---|---|
| Known | CMS backend / database | Laravel / Oracle |
| Known | CMS login URL | `cms.bnu.edu.pk/login` |
| Known | CMS auth surface | Username + password, no visible federation |
| Known | Likely identity backbone | Active Directory (via `secure.bnu.edu.pk`) |
| Known | Data policy | Everything — including counselor details — should be fetched from CMS, not duplicated |
| Assumption | CMS exposes (or can expose) an internal API | Not confirmed — almost certainly doesn't exist today and would need to be built |
| Assumption | CMS tracks "assigned counselor" as structured data | **Genuinely uncertain** — see flag below |
| Assumption | Laravel packages available on the CMS side | Unknown — matters for whether a signing/API capability is easy to add |
| Needed | Exact student identifier CMS uses, and its format | Confirm directly — don't build against the AD `registration-number` pattern as ground truth, it may not match what the CMS itself calls the field |
| Needed | Where counselor assignment actually lives | Confirm before §5 can be finalized |
| Needed | BNU IT's capacity to add an endpoint/signing route to the CMS app | This is the real dependency gating everything else |
| Needed | A staging/sandbox CMS to integrate against before production | Confirm |

**On counselor assignment specifically:** the instruction was that counselor details should come from CMS too — but a course/attendance/fees-oriented CMS quite often has no formal "assigned counselor" concept at all; that relationship frequently lives in a spreadsheet, a separate advising process, or nowhere structured. If that turns out to be true here, the Counselor System has to keep owning that assignment regardless of the general policy, and CMS only supplies raw student identity. This is the fact most likely to change the design, so it's worth confirming with BNU IT first, before anything else on the list.

---

## 4. Recommended identity/SSO architecture

Given: Laravel-to-Laravel, no OAuth infrastructure on either side today, a server-rendered (non-SPA) Counselor System, and the requirement that a bare `?studentId=12345` isn't acceptable identity (Section 7).

| | Signed handoff token (Laravel-native) | Laravel Passport (full OAuth2 server) |
|---|---|---|
| What it is | CMS mints a short-lived signed token the instant the student clicks "Counselor"; Counselor System verifies it, then calls CMS server-to-server for the full profile | CMS becomes a real OAuth2 authorization server; Counselor System is a registered client using the Authorization Code grant |
| Build cost on CMS side | Small — one route, a signing key, a few lines | Substantial — install Passport, run its migrations against Oracle (not a well-trodden combination), manage clients/scopes, build a consent-skip flow |
| Fits the stack | Yes — both apps are Laravel and already use session guards; this is one more way to arrive at "logged in" | Partially — Passport's tooling assumes typical Laravel/MySQL conventions; Oracle via `yajra/laravel-oci8` is untested territory for it |
| Feels seamless to the student | Yes, if built correctly — redirect, verify, log in | Only if BNU IT explicitly builds consent-skipping for a first-party client; otherwise the student sees an OAuth consent screen mid-click |
| Standards-based | No — custom, so implementation care matters more | Yes — the right call if BNU wants multiple systems consuming CMS identity long-term |

**Recommendation: the signed handoff token**, for now. It's the smaller ask to put in front of BNU IT, matches the stack they actually run, and avoids the untested Passport+Oracle combination. Passport becomes the right call if BNU is separately planning real OAuth infrastructure for reasons beyond this one integration — worth asking about, but not what I'd design around by default.

### Proposed flow
```text
Student (already authenticated in the CMS session)
        │ clicks "Counselor"
        ▼
CMS: GET /counselor/redirect
        │ mints a token: { sub: cms_student_id, iat, exp (~60s), aud: "counselor-system", jti: nonce }
        │ signs with RS256 — CMS holds the private key, Counselor System only ever holds the public key
        ▼
Browser → POST https://counselor.bnu.edu.pk/sso/callback
        │ (token in the POST body, not a query string — keeps it out of browser history,
        │  referrer headers, and access logs)
        ▼
Counselor System:
   1. verify signature against CMS's public key
   2. verify exp, aud, and that jti hasn't been seen before (cache it for the token's lifetime — replay protection)
   3. server-to-server call: GET {CMS_API}/student/me
      Bearer: a separate, longer-lived service credential — not the one-shot handoff token
      → full profile, including counselor assignment if CMS has it
   4. find-or-create the local Student row by cms_student_id; update identity fields only —
      never touch suicide_intent / suicide_alert / presenting_concerns / consent / session_status
   5. Auth::guard('student')->login($student); session()->regenerate(); redirect to dashboard
```

This slots into the existing `student` guard almost unchanged — a new way to arrive at "logged in," not a rewrite of the guard itself.

Use **RS256, not HS256**: CMS keeps the private key, Counselor System only holds the public key. If Counselor System is ever compromised, an attacker still can't forge CMS-signed tokens — with a shared HS256 secret, they could.

---

## 5. Data ownership (revised for "fetch everything, including counselor details")

| Data | System of record | How Counselor System gets it |
|---|---|---|
| Student ID, name, DOB, phone, department, semester, CGPA | BNU CMS | Fetched on SSO handoff; short-TTL server-side cache (5–15 min) so a CMS blip doesn't take the dashboard down with it — not a permanent local copy |
| Counselor assignment | BNU CMS, **if confirmed to exist there** — otherwise Counselor System, by necessity | Same fetch, if it exists |
| Suicide risk flags, presenting concerns, consent status, session status, next-session scheduling | Counselor System, always — never CMS | Local only; never included in any payload sent to CMS |
| Appointments, notes, attachments, support group requests, tasks, notifications | Counselor System | Local, as today — keep the existing pattern of snapshotting `student_name`/`roll_number` on `appointments`, and extend it to `department`/`semester` at time of session, so a counseling record reflects what was true then, not whatever CMS says now |

On "everything, every time, live": worth not taking that fully literally. A short server-side cache keeps CMS authoritative without making the counseling dashboard's uptime fully dependent on CMS's. Worth a deliberate decision rather than defaulting to either extreme.

---

## 6. What actually gates progress right now

1. Can the CMS Laravel app take on one signing route plus one or two read-only API endpoints?
2. Does "assigned counselor" exist as structured data anywhere BNU controls — CMS or otherwise?
3. What's the real student-identity field/format inside CMS? (Don't assume it matches the AD pattern observed externally.)
4. Is there a staging CMS to integrate against before production?

---

## Implementation

Decision: build speculatively rather than wait for BNU IT. The task-by-task execution plan now lives in **`CLAUDE.md`** (same output folder as this doc) — that's the operational version, kept deliberately separate from this file so the two don't drift out of sync as the project moves. Drop both files into the project root; Claude Code reads `CLAUDE.md` automatically.

Short version of what's in there: build a mock CMS client first (there's nothing real to call yet), add `cms_student_id` to `students` without touching any clinical column, build the SSO callback against the flow in §4, leave counselor auth untouched until BNU IT confirms whether CMS has any concept of counselor identity. Everything is explicitly provisional pending §3 and §6.
