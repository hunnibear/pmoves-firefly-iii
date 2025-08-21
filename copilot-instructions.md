# Copilot instructions — pmoves-firefly-iii (v2)

Date: 2025-08-20

Purpose
- Provide the minimal context and developer/runbook that an automated assistant or contributor needs to make safe, useful edits to the v2 UI work in this repository.
- Point to the authoritative planning docs and summarize the most important commands, file locations, API contracts, and guardrails.

Primary reference documents
- `docs/V2_DASHBOARD_AUDIT_AND_PLAN.md` — audit, constraints, phased plan, verification commands, and file-level mapping.
- `docs/V2_UI_DESIGN.md` — v2 UI design, component map, API contracts, and short-term roadmap.

Where to look first (quick map)
- v2 React sources: `resources/assets/v2/src/`
- compiled v2 assets (bundles): `public/build/` and `public/build/manifest.json`
- v2 Blade entry points: `resources/views/v2/*.blade.php` and `resources/views/layout/v2/*`
- Watch-folder backend: `app/Http/Controllers/WatchFolderController.php`, `app/Services/WatchFolderService.php`, job `app/Jobs/ProcessWatchFolderDocument.php`
- Couples & AI controllers: `app/Http/Controllers/CouplesController.php`, `app/Http/Controllers/AI/*`

High-level goals and guardrails
- Keep v1 behavior intact. Any controller changes must be behind the `FIREFLY_III_LAYOUT === 'v2'` guard unless explicitly asked to replace v1 permanently.
- Prefer additive changes: add v2 blades and React components rather than removing legacy templates.
- Avoid running destructive container commands inside the workspace; prefer local builds and commits. When restarting containers is required, report steps and wait for user consent.

Important API endpoints (used by v2 UI)
- Watch folders status: `GET /api/v1/watch-folders/status` → returns `{ status:'success', data: { statistics: {...}, system_info: {...}, queue_status: {...} } }`
- Watch folders management: `GET/POST/DELETE /api/v1/watch-folders` and `POST /api/v1/watch-folders/trigger` and `POST /api/v1/watch-folders/test-path`.
- Couples receipt upload: `POST /api/v1/couples/upload-receipt` (multipart/form-data; returns extraction JSON)
- AI insights: `GET /api/v1/ai/insights` and agent endpoints (refer to `docs/V2_UI_DESIGN.md` for examples).

Developer commands (PowerShell / Windows)
- Clear Laravel caches (safe to run locally):
```powershell
php artisan view:clear
php artisan config:clear
php artisan cache:clear
```
- Build the v2 frontend (from repo root or `resources/assets/v2`):
```powershell
cd resources/assets/v2
npm ci
npm run build
```
- Run watch-folders processing once (dev):
```powershell
# run one pass
php artisan watch-folders:run --once
# continuous monitoring (background):
php artisan watch-folders:run --interval=30
```
- Check watch-folder status (example):
```powershell
curl -s http://localhost:8080/api/v1/watch-folders/status | jq
```

Testing guidance (Watch Folder end-to-end)
1. Drop a sample file into the `./watch-folders/incoming/` (or `./watch-folders` depending on your mount) or use `create_sample_data.py`.
2. Run `php artisan watch-folders:run --once` to process.
3. Confirm `GET /api/v1/watch-folders/status` shows updated `processed_files` count.
4. Verify the Firefly transaction list (UI) contains any created transactions.
5. In v2 UI (authenticated browser), confirm the Watch Folder widget shows updated counts and recent files.

Conventions for editing code
- When adding or changing controllers that affect routing, ensure constructors do not throw—controller instantiation occurs during artisan route:list and will block route discovery if it throws.
- When adding v2 Blade views, put them under `resources/views/v2/...` and prefer minimal shells that mount React roots. Example:
  - `resources/views/v2/couples/dashboard.blade.php` → contains `<div id="couples-dashboard-root"></div>` and includes built assets.
- Protect runtime behavior with the `FIREFLY_III_LAYOUT` env toggle. Example in controller:
```php
if (env('FIREFLY_III_LAYOUT') === 'v2') return view('v2.couples.dashboard');
return view('couples.dashboard');
```

Suggested small improvements (safe, high value)
- Extend `WatchFolderController::status()` to include `recent_files` (last 5 processed) with a small extraction preview — store previews as JSON next to processed files or in a small DB table.
- Make `ProcessWatchFolderDocument` job write a lightweight `extraction_preview.json` next to processed files (used by UI without expensive DB reads).
- Add client-side polling or event-based updates (Supabase/Redis) to avoid frequent polling.

PR & commit checklist
1. Run `npm run build` (if front-end changed). 2. Run `php artisan view:clear && php artisan cache:clear`. 3. Run quick smoke commands: `php artisan route:list` and `curl /api/v1/watch-folders/status`. 4. Include tests or a short manual test plan in PR description. 5. Keep changes behind `FIREFLY_III_LAYOUT` unless replacing v1 intentionally.

Where to find more docs in this repo
- `docs/V2_DASHBOARD_AUDIT_AND_PLAN.md` — migration plan and verification steps.
- `docs/V2_UI_DESIGN.md` — UI component mapping, contracts, and short-term roadmap.
- `WATCH_FOLDER_README.md` and `watch-folders/README.md` — operational notes about watch folder directories and mounts.
- `PROJECT_STATUS_LANGEXTRACT_COMPLETE.md` — details about LangExtractService and AI infra.

- Quick index of other useful plan/status docs:
  - `PHASE1_STEP1_PLAN.md` — Phase 1 execution checklist and tasks.
  - `PHASE2_IMPLEMENTATION_PLAN.md` — Phase 2 roadmap and milestones.
  - `PLAN_DOCUMENTS_UPDATED_SUMMARY.md` — summary of recent plan updates.
  - `PROJECT_STATUS_SUMMARY.md` — high-level project snapshot.
  - `PROJECT_STATUS_AGENTIC_READY.md` — agentic automation readiness notes.
  - `PROJECT_STATUS_ENHANCED_COMPLETE.md` — enhanced processing completion notes.
  - `COUPLES_INTEGRATION_STRATEGY_V2.md` and `COUPLES_DATA_INTEGRATION_STRATEGY.md` — Couples feature strategy and data notes.
  - `AI_LANGEXTRACT_INTEGRATION_PLAN.md`, `AI_PRODUCTION_SETUP.md`, `AI_SETUP_COMPLETE.md` — AI / LangExtract implementation and deployment guides.
  - `DATA_IMPORTER_SETUP_GUIDE.md` — bulk import and data-migration guides.
  - `FRONTEND_INTEGRATION_COMPLETE.md` — front-end build & integration notes.
  - `COUPLES_IMPLEMENTATION_COMPLETE.md` and `COUPLES_FEATURE_TEST_REPORT.md` — implementation status and test reports for Couples features.

Searchable Firefly reference (local)
----------------------------------

There is a large, searchable plain-text export of Firefly documentation and repository context at `docs/firefly-docs.txt`. This file is handy when you need quick, offline access to examples, API references, migration notes, and larger README content from the upstream Firefly repo.

Quick search examples:

- PowerShell (case-insensitive):

```powershell
(Select-String -Path .\docs\firefly-docs.txt -Pattern 'createUser' -CaseSensitive:$false).Line
```

- Recursive grep (Unix / WSL / Git Bash):

```bash
grep -nI "createUser" docs/firefly-docs.txt
```

Notes:

- The file can be large; prefer targeted keywords or short phrases. Use alternation in regex to search multiple terms at once (e.g., `createUser|RefreshDatabase`).
- If you want help extracting a specific example or API contract from that file, tell me the search term and I will extract the relevant snippets and add them to the repo docs where appropriate.

If you're an automated assistant (Copilot) running in this repo
- Only modify code in small, reversible increments. Commit to `v2` branch and push. Provide a concise summary of changes and verification steps in the commit message.
- Never run container restarts or destructive compose commands without explicit user consent.
- If you need to run tests or builds, run them locally (npm build, php artisan commands). Report outputs and any failures.

Questions & contact points
- If a controller throws during route discovery, inspect constructors for non-deterministic operations (DB reads, file access) and move them out of the constructor.
- For missing controllers referenced by routes, add minimal shim controllers returning 503/placeholder views so route:list succeeds, then implement full logic.

---
Small print: This file is an operational convenience for contributors and automated assistants. Keep it short and keep it in-sync with `docs/V2_DASHBOARD_AUDIT_AND_PLAN.md` and `docs/V2_UI_DESIGN.md`.
