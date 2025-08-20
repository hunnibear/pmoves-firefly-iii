V2 Dashboard audit and plan

## Purpose

This document captures the current findings about the v2 dashboard, the goals for the v2 migration/enhancement, constraints (keep Firefly v1 working and updatable), and a concrete, minimal plan for moving forward. Create this doc before further development so we have a clear contract and a low-risk migration path.

---

## High-level summary (current state)

- The repository contains a v2 frontend (React/JSX) under `resources/assets/v2/src` with components for the main dashboard, `AiAgentWidget`, `WatchFolderWidget`, and `CouplesDashboard.jsx`.
- Compiled v2 assets are present in `public/build` and `public/build/manifest.json` references entries for the dashboard and couples pages.
- Blade views for v2 exist under `resources/views/v2` and the Laravel `config/view.php` will prefer `resources/views/v2` when `FIREFLY_III_LAYOUT=v2` is set.
- However, several controllers for Couples and AI still return legacy v1 views (Twig / `resources/views/couples/*.twig` and `resources/views/ai/*.twig`).
- There are legacy/backup assets in `resources/backup_old_dashboard` still in the repo.
- The result at runtime (what you reported): the v2 dashboard is visible but the Couples integration and AI integration are missing or non-functional; clicking certain links returns you to the old dashboard UI. This is consistent with controllers returning legacy views and/or stale assets being served.

---

## Goals (what the v2 dashboard should provide)

- A mobile-first, responsive dashboard with equal or better functionality than Firefly v1.
- Maintain the original Firefly app (v1) intact so upstream updates can be incorporated.
- Add new integrated features on top of Firefly: Couples interactive budgeting dashboard, Agentic AI dashboard, Watch Folders + LangExtract automatic processing.
- Make the v2 dashboard an opt-in (controlled via env var `FIREFLY_III_LAYOUT=v2`).
- Allow developers to pull updates from Firefly upstream and reapply or keep customizations in a small, well-documented surface area.

---

## Requirements checklist (traceable & testable)

1. v2 dashboard served when `FIREFLY_III_LAYOUT=v2` and app restarted. (Status: Partly validated — config present; runtime needs verification) — verification: request `/` and inspect HTML for `<div id="dashboard-root">`.
2. Couples interactive dashboard rendered via v2 (React), not legacy Twig, with working endpoints for data and receipts upload. (Status: Not yet; controllers return legacy view) — verification: `/couples/dashboard` should return v2 blade with `couples-dashboard-root` and load `src/pages/couples/dashboard.jsx` bundle.
3. AI dashboard (agent and agent UI) rendered via v2 and connected to AI endpoints. (Status: Not yet; controllers return legacy view) — verification: `/ai` or `/ai-agent/dashboard` should return v2 view and show v2 widgets; API endpoints `/api/v1/ai-agent/status` and `/api/...` must respond 200.
4. Watch folders + LangExtract processing integrated and accessible from v2 widgets. (Status: Source code exists; runtime wiring needs verification) — verification: widget fetches succeed for `/api/v1/watch-folders/status` and watch-folder flows run as expected.
5. Updatable from upstream: applying upstream Firefly changes should be simple; custom v2 additions should be confined to `resources/assets/v2`, `resources/views/v2`, and a minimal set of controller/view stubs. (Status: design constraint — plan to keep surface minimal.)

---

## Root causes (why the current experience is inconsistent)

- Some routes/controllers still return legacy v1 Twig views (e.g., `CouplesController::dashboard()` returns `view('couples.dashboard')` which maps to `resources/views/couples/dashboard.twig`).
- v2 views and bundles exist, but the server-side routing does not consistently return the v2 blade that mounts the React root elements.
- Browser/runtime: If a v1 view is served, the v2 bundles are not mounted and React components are not created, so the v2 widgets are missing. Additionally API calls from the front-end may fail due to CSRF/route differences, causing widget content to be empty.
- There may also be stale caches or old manifest references if the assets were rebuilt but the runtime still uses earlier files (less likely but possible).

---

## Constraints & assumptions

- Keep Firefly core behavior intact and allow upstream merges. Customizations should be additive and isolated where practical.
- The app's env var `FIREFLY_III_LAYOUT` will select `resources/views/v2` path first when set to `v2` (config is already present in `config/view.php`). But controllers may still explicitly return legacy view names.
- We must avoid breaking existing deployments — approach: non-destructive changes, add v2 blades, and switch controller view returns to v2 only after verifying the new blades work locally.

---

## Proposed plan (minimal, safe, step-by-step)

Phase A — Verify & document (this is what we just did):
- Confirm which routes/controllers return legacy vs v2 views (we have a partial map).
- Add this document to repo for team review (done).

Phase B — Non-breaking quick wins (small changes, reversible):
- Add v2 blades that mount the React roots for Couples and AI at `resources/views/v2/couples/dashboard.blade.php` and `resources/views/v2/ai/dashboard.blade.php` (both minimal: a root div and a @vite entry). Do NOT remove legacy templates.
- Add a feature-flagged controller change: update `CouplesController::dashboard()` to return `view('v2.couples.dashboard')` only when env FIREFLY_III_LAYOUT === 'v2'. Keep legacy view otherwise. This is reversible and explicit.
- Repeat similarly for the AI dashboard controller.
- Rebuild v2 assets and clear Laravel caches.

Phase C — Integration & validation:
- Ensure the v2 React components mount and make API calls successfully. Fix any CSRF or header issues for API calls used by v2 widgets (add `X-Requested-With` headers, include CSRF token where needed).
- Validate watch-folder and LangExtract flows from the v2 UI (upload receipt -> LangExtract process -> dashboard update).
- Add automated smoke tests (Selenium or Playwright) for these critical flows.

Phase D — Hardening & developer ergonomics:
- Add docs on where customizations live and a short upgrade guide for applying upstream merges.
- Optionally create a small shim layer (v2 view-return helper) so new v2 pages are returned centrally rather than changing many controllers.

---

## File-level mapping (where to look / change)

- Frontend (v2 sources): `resources/assets/v2/src/` (components & pages)
- v2 Blade entry points: `resources/views/v2/index.blade.php`, `resources/views/v2/dashboard.blade.php`
- Add: `resources/views/v2/couples/dashboard.blade.php` (mount point for couples React page)
- Add: `resources/views/v2/ai/dashboard.blade.php` (mount point for AI React page)
- Controllers to adjust (example):
  - `app/Http/Controllers/CouplesController.php` — `dashboard()` should be env-gated to return v2 view.
  - `app/Http/Controllers/AI/DashboardController.php` — `index()` should be env-gated to return v2 view when present.
- Backend API endpoints (already present but verify):
  - `GET /api/v1/couples/dashboard` — `CouplesController@dashboardData` or API route
  - `GET /api/v1/ai-agent/status` and others — `app/Api/V1/Controllers/*`
- Compiled assets / manifest: `public/build/manifest.json` and `public/build/assets/*`

---

## Verification & quick commands

(Use PowerShell. Run from project root.)

1. Clear view and config caches (safe):

```powershell
php artisan view:clear
php artisan config:clear
php artisan cache:clear
```

2. Rebuild v2 frontend assets (if you changed any JS):

```powershell
# if package.json at repo root or resources/assets/v2
cd resources/assets/v2
npm ci
npm run build
# or from repo root if scripts configured accordingly
```

3. Check a widget API endpoint (example):

```powershell
Invoke-WebRequest -Uri http://localhost:8080/api/v1/couples/dashboard -UseBasicParsing
Invoke-WebRequest -Uri http://localhost:8080/api/v1/ai-agent/status -UseBasicParsing
```

4. Inspect served HTML for v2 marker: fetch the route and search for `dashboard-root` or `couples-dashboard-root`.

```powershell
(Invoke-WebRequest -Uri http://localhost:8080/couples/dashboard -UseBasicParsing).Content | Select-String 'couples-dashboard-root'
```

---

## Edge cases & risks

- If code changes are made directly to many controllers, merging upstream changes later becomes hard. Keep changes minimal and place v2-only logic behind a single env flag.
- Make sure CSRF and auth flows are preserved for API calls invoked by the React frontend.
- When enabling the v2 layout, test thoroughly — users may lose access to certain v1 UI paths if controllers are re-pointed prematurely.

---

## Next actions (pick one)

- Option 1: I implement Phase B (create the v2 blades and add env-gated controller returns for Couples and AI). I will not remove legacy templates. I will then rebuild assets and run the verification commands above. (If you want this, reply: "Implement v2 view stubs + controller gating".)

- Option 2: You run the quick verification commands and report results (console logs, failing API endpoints). I’ll then implement any needed changes.

- Option 3: I produce PR-ready patches for the minimal Phase B changes and you review before merging.

Tell me which next action you prefer and I will proceed.

---

## Completion note

This document summarizes the audit and a conservative plan to get v2 fully functional while preserving v1. Once you pick next action I will proceed with changes or tests and report back with concrete diffs and verification results.
