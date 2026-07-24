# Execution Plan for CLI Agent — `essasabbgah/laravel-chat`

This is a build script for a coding agent (Claude Code or similar) working phase-by-phase. Each phase has: setup commands, what to build, tests to write **first or alongside**, and docs to update. Nothing ships without its tests and its doc page — that's the rule for every phase, not a final cleanup step.

---

## 0. Gaps in the original plan worth closing before coding starts

1. **No testing strategy was specified at all.** For a Composer package this has to be Orchestra Testbench from commit #1, not added later — retrofitting tests onto broadcasting/queue code is painful.
2. **Presence storage (DB table vs Redis) is undecided.** Don't hardcode either — introduce a `PresenceDriver` contract (mirrors the existing `TenantResolver` pattern) with a DB-backed default and an optional Redis driver. Decide the interface in Milestone 1, not Milestone 5.
3. **No CI pipeline mentioned.** Add GitHub Actions running the matrix (PHP 8.2/8.3 × Laravel 10/11) from the first commit so regressions surface immediately.
4. **Package skeleton tooling** — consider `spatie/laravel-package-tools` for the service provider/config/migration publishing boilerplate; saves a lot of hand-written provider code and is a de facto standard reviewers will recognize.
5. **Open questions from the original plan should be resolved before the milestones that depend on them**, not left dangling:
   - Group-creation permissions → needed before Milestone 6 (rooms).
   - FCM: package sends directly vs. fires a generic event → needed before Milestone 12, but the *contract* (an event Anthropic-style "hook") should be decided in Milestone 1 so `FcmNotifier` isn't a rewrite later.
   - Tenancy resolver: stay generic (recommended) — don't couple to `stancl/tenancy` in v1; document how to adapt it instead.
6. **Missing from the schema**: a unique constraint on `chat_message_reads (message_id, participantable_type, participantable_id)`, and an index on `chat_messages (conversation_id, created_at, id)` for cursor pagination.
7. **README vs docs/ split** — Packagist README is the landing page (badges, quick install, quick example); `docs/` is the manual. Keep them separate from the start so the README doesn't become a dumping ground.
8. **License/versioning** — MIT license file, semantic versioning from `v0.1.0`, and a `CHANGELOG.md` (Keep a Changelog format) should exist before the first tagged release, not added retroactively.

---

## 1. Repo bootstrap (Milestone 0 — do this before any feature work)

```bash
composer create-project --stability=dev orchestra/testbench-core:^9 tmp-scratch --no-install # reference only, not for repo root
```

Actual steps for the agent:

1. Init `packages/laravel-chat` as its own composer package (`composer init` → name `essasabbgah/laravel-chat`, type `library`).
2. Require dev deps: `orchestra/testbench`, `phpunit/phpunit` (or `pestphp/pest` + `pest-plugin-laravel` if the team prefers Pest), `larastan/larastan`, `laravel/pint`.
3. Add `laravel-package-tools` as a runtime dep to scaffold `ChatServiceProvider`.
4. Create `phpunit.xml` (or `Pest.php`) pointing at `tests/`, with a Testbench `TestCase` that boots a minimal Laravel app, registers `ChatServiceProvider`, and runs package migrations in-memory (sqlite `:memory:`).
5. Add `.github/workflows/tests.yml`: matrix PHP 8.2/8.3 × Laravel 10/11, steps: checkout → setup-php → composer install → `vendor/bin/pint --test` → `vendor/bin/phpstan analyse` → `vendor/bin/phpunit`.
6. Add `LICENSE` (MIT), `CHANGELOG.md` (empty `## [Unreleased]` section), `.editorconfig`, `CONTRIBUTING.md`.
7. Write `README.md` skeleton: badges (placeholders), one-paragraph pitch, quick install snippet, link to `docs/`.

**Definition of done:** `composer test` runs a green (trivial) test suite in CI on a fresh clone. Nothing feature-related yet — this phase only proves the harness works.

---

## 2. Milestone 1 — Core schema, models, contracts, plain REST CRUD

**Build:**
- Migrations: `chat_conversations`, `chat_participants`, `chat_messages`, `chat_attachments`, `chat_reactions`, `chat_message_reads`, `chat_user_status`, `chat_blocks` — with the indexes/constraints noted in gap #6.
- Eloquent models with polymorphic relations (`morphTo`/`morphedByMany` for participants and senders).
- Contracts: `TenantResolver` (+ no-op default), `PresenceDriver` (+ DB-backed default).
- Config file `config/chat.php` with participant-model bindings, tenancy block, storage block, feature toggles.
- Model factories for every table.
- Basic REST CRUD controllers for conversations/messages (no broadcasting yet).

**Tests (write alongside, not after):**
- Testbench feature tests hitting each REST endpoint against an in-memory sqlite DB with two different polymorphic participant types (e.g. dummy `TestCustomer`/`TestAgent` models registered only in tests) to prove the polymorphic design actually works across types.
- Unit tests for `TenantResolver` default (returns null/0) and for a fake custom resolver.
- Migration test: run migrations up/down cleanly.

**Docs:**
- `docs/installation.md` (composer require, publish config/migrations, `php artisan migrate`).
- `docs/configuration.md` — every key added so far.

**Definition of done:** CI green; a developer can `composer require`, publish, migrate, and CRUD a conversation via HTTP in a scratch Laravel app.

---

## 3. Milestone 2 — Reverb broadcasting, tenant-aware channels, auth

**Build:**
- Events: `MessageSent`, `ConversationUpdated`.
- Channel classes with tenant-prefixed naming pulling from `TenantResolver`.
- `routes/channels.php` broadcasting auth checks (participant must belong to conversation).
- Wire host app's existing `broadcasting.php` reverb connection (don't duplicate credentials).

**Tests:**
- `Event::fake()` assertions that the right event fires with the right payload on message creation.
- Broadcasting auth tests: authorized participant passes, non-participant gets 403, and tenant-mismatched participant gets 403 (this is the one most worth getting right — write it first).

**Docs:**
- `docs/events-and-broadcasting.md` — event list, payload shapes, channel naming scheme.
- `docs/multi-tenancy.md` — first draft, custom `TenantResolver` example.

**Definition of done:** two browser tabs (or two Testbench-simulated clients) see a message appear in near-real-time in a demo app; broadcasting-auth tests cover the tenant-isolation case explicitly.

---

## 4. Milestone 3 — Attachments + voice messages

**Build:**
- Upload endpoint, `chat_attachments` writes, per-type metadata extraction (OG scrape for URLs, oEmbed for YouTube, lat/lng payload for location).
- Storage disk from config (default `local`), mime/size validation via config.

**Tests:**
- `Storage::fake()` per configured disk; assert file lands on the right disk.
- `Http::fake()` for OG-scrape and YouTube oEmbed calls — never hit real network in tests.
- Validation tests: oversized file rejected, disallowed mime rejected, per config.

**Docs:**
- Update `docs/configuration.md` (storage/mime/size keys).
- Add attachment-type examples to `docs/extending.md` (how to add a new type).

---

## 5. Milestone 4 — Replies + reactions

**Build:** `reply_to_id` handling, denormalized snippet; reactions table + endpoint.

**Tests:** feature tests for reply chains (including replying to a deleted message — decide and test the behavior explicitly), reaction add/remove/toggle-same-emoji.

**Docs:** short additions to `docs/events-and-broadcasting.md` if new events are introduced.

---

## 6. Milestone 5 — Presence + delivered/seen receipts

**Build:** implement the `PresenceDriver` contract's DB-backed default fully (or Redis if you've decided to ship that as default — pick one and document the other as swappable); `MessageStatusUpdated` event; `chat_message_reads` writes.

**Tests:**
- State-machine tests: sent → delivered → seen, asserting no skipped/duplicate status broadcasts.
- Presence join/leave updates `chat_user_status` and fires `UserPresenceChanged` exactly once per transition.
- Unique-constraint test on `chat_message_reads` (double-read doesn't duplicate rows).

**Docs:** document the delivery/seen state machine in `docs/events-and-broadcasting.md` with a small diagram or table.

---

## 7. Milestone 6 — Multi-user rooms + roles

**Resolve first:** group-creation permission (open question #4 from the original plan) — make it a config flag (`chat.groups.who_can_create: any|admin_role`) rather than hardcoding, so both answers are supported.

**Build:** group type conversations, participant roles, add/remove member endpoints.

**Tests:** permission tests for both config states; role-based access tests (member vs admin actions).

**Docs:** update `docs/admin-guide.md` and `docs/configuration.md`.

---

## 8. Milestone 7 — Cursor pagination polish

**Build:** confirm cursor pagination (`created_at`+`id`) on messages and conversations lists; infinite scroll both directions.

**Tests:** pagination correctness under concurrent inserts (new message arrives while a client is mid-scroll — assert no duplicate/skip).

**Docs:** pagination section in `docs/blade-integration.md` and `docs/flutter-integration.md` (how the infinite scroll wiring works client-side).

---

## 9. Milestone 8 — Blade UI (Alpine.js + Tailwind)

**Build:** publishable Blade components (`<x-chat::bubble />`, `<x-chat::window />`), Echo/Reverb JS bootstrap asset.

**Tests:** Dusk or Testbench view-rendering smoke tests (component renders without error, publishes correctly via `vendor:publish`).

**Docs:** `docs/blade-integration.md` fully fleshed out with copy-pasteable snippets.

---

## 10. Milestone 9 — Admin services/API (no dashboard)

**Build:** `AdminChatService` (block, force-offline, delete, status change), gated by the `allow_admin_*` config flags.

**Tests:** each admin action tested both enabled and disabled via config; authorization tests (only admin-role callers succeed).

**Docs:** `docs/admin-guide.md` finished for v1 scope; note the planned Filament module explicitly so users don't expect a UI yet.

---

## 11. Milestone 10 — Flutter package

Separate track, same discipline: `flutter_chat` repo/dir gets its own tests (`flutter test`) for repositories/streams before widgets are built on top. Example app is the acceptance test — if it can send/receive/react without state-management glue code, the "state-agnostic" goal is met.

**Docs:** `docs/flutter-integration.md`.

---

## 12. Milestone 11 — Multi-tenancy resolver docs + hardening

Write the actual `docs/multi-tenancy.md` walkthrough using a real example (e.g. a `team_id` column resolver), plus a test proving two tenants' channel names and queries never leak into each other.

## 13. Milestone 12 — FCM (conditional)

**Resolve first:** direct-send vs. generic-event-only (open question #3). Recommendation: fire a generic `ChatEventOccurred`-style event always, and let an *optional* built-in `FcmNotifier` listener subscribe to it only when `fcm.enabled` — this way host apps that want their own FCM setup can just not enable the built-in listener, and you're not maintaining two send paths later.

**Tests:** `Http::fake()`/Firebase-SDK-fake for push calls; assert silent no-op when config absent.

**Docs:** `docs/push-notifications.md`.

## 14. Milestone 13 — Full docs pass + demo app

Build the end-to-end demo Laravel app referenced in the original plan; use it as a living integration test (boot it in CI headlessly if feasible). Proofread all `docs/*.md` against the actual final API — this is where doc drift usually hides.

## 15. Milestone 14 (later, separate release) — Filament admin dashboard module

Ship as its own Composer package (`essasabbgah/laravel-chat-filament` or similar) so v1 users aren't forced into a Filament dependency.

---

## Standing rules for the agent across every milestone

- **No feature PR without its tests in the same commit.** Testbench feature tests for anything hitting HTTP/broadcasting/DB; unit tests for services/contracts.
- **No feature PR without its doc page updated in the same commit.** Docs drift is the #1 way packages rot.
- **Run `vendor/bin/pint`, `vendor/bin/phpstan analyse`, and `vendor/bin/phpunit` before every commit** — wire this as a local pre-commit hook, not just CI.
- **Every open question gets resolved as a config flag, not a hardcoded assumption**, when there's a plausible case for either answer (group creation, FCM send path, presence driver).
- **CHANGELOG.md gets an entry per milestone**, not just at release time.
