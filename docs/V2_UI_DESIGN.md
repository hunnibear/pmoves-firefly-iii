# V2 UI Design & Implementation Plan

Date: 2025-08-20
Branch: `v2`

This document captures a concise design and implementation plan for the v2 UI (Couples, AI, LangExtract, Watch Folder, Supabase integration), maps UI pieces to code locations, lists data/route contracts and acceptance criteria, and lays out the next engineering steps.

Goals
- Provide a modern overlay UI that mirrors Firefly III functionality but focuses on Couples workflows and AI-assisted automation.
- Keep v2 opt-in (controlled by `FIREFLY_III_LAYOUT=v2`) and non-destructive to v1.
- Make incremental, testable deliverables: mount React roots in Blade, wire API endpoints, then replace placeholder controllers with full logic.

Key principles
- Progressive rollout: detect missing controllers or assets and fail gracefully.
- Keep server-rendered Blade shells for auth and initial state; React handles interactive dashboards.
- Maintain security: API endpoints require standard Firefly auth tokens / session middleware.
- Reuse existing backend services (LangExtractService, Agentic components) — connect front-end to them.

Top-level layout
- Single master v2 layout: `resources/views/layout/v2/app.blade.php` (shell + script tags from `public/build/manifest.json`)
- Dashboard index: `resources/views/v2/dashboard.blade.php` → React root `#dashboard-root`
- Couples dashboard: `resources/views/v2/couples/dashboard.blade.php` → React root `#couples-dashboard-root`
- AI dashboard: `resources/views/v2/ai/dashboard.blade.php` → React root `#ai-dashboard-root`
- Shared error layout: `resources/views/layout/v2/error.blade.php` (use `url('/')` fallback)

Frontend (React + Vite)
- Location: `resources/assets/v2/` (source) → `public/build` (bundles via Vite)
- Important components to create/extend:
  - `src/pages/Dashboard.jsx` — global v2 home
  - `src/pages/CouplesDashboard.jsx` — couples overview, partner switcher, upload zone
  - `src/pages/AIDashboard.jsx` — AI insights, agent controls, logs
  - `src/components/ReceiptUploadZone.jsx` — drag/drop, camera support (connects to `/api/v1/couples/upload-receipt`)
  - `src/components/ProcessingIndicator.jsx`, `ExtractionResults.jsx`, `ConfidenceIndicator.jsx`
  - `src/lib/api.js` — fetch wrappers for CSRF/session and bearer tokens
  - Shadcn UI components are already integrated (see project docs)

Backend mapping (Laravel)
- Blade views:
  - `resources/views/v2/*.blade.php` — React mount points and server-provided initial props
- Controllers (examples):
  - `app/Http/Controllers/CouplesController.php` — `dashboard()`, `uploadReceipt()`, `processBankStatement()`
  - `app/Http/Controllers/AI/DashboardController.php` — `index()`, `testConnectivity()`, `getInsights()`
  - `app/Http/Controllers/WatchFolder/*` — monitor and processing endpoints
- Services:
  - `app/Services/LangExtractService.php` — receipt / statement processing API (already implemented)
  - Agentic services (`AgentController`, Python FastAPI endpoints) — for auto-action and subagents

API contracts (minimal examples)
- POST /api/v1/couples/upload-receipt
  - Request: multipart/form-data { file, partner_override?, create_transaction? }
  - Response: { success: true, extractions: [...], normalized: {...}, processing_meta: {...} }

- POST /api/v1/couples/process-bank-statement
  - Request: multipart/form-data { file, account_id }
  - Response: { success, transactions: [...], processing_meta }

- GET /api/v1/ai/insights
  - Response: { insights: [...], model: 'gemma3:270m', latency_ms }

Data contracts (front-end expects)
- Extraction: { merchant, total, date, items: [{name, price}], confidence }
- Transaction: { date, amount, description, category_id, account_id }

Auth & security
- Use existing Laravel session for browser-based calls. For API calls from mobile or external apps, require Bearer tokens.
- Frontend must include CSRF token for POSTs. Provide helper `window.__FIREBASE_V2__` or inline `data-initial` for initial state.

Edge cases & error handling
- Show fallbacks when LangExtract times out or returns low confidence.
- Long-running processing: use polling or WebSocket/SSE for progress updates (Supabase or Redis-backed notifications).
- File size/format validation enforced client-side and server-side (50MB max by existing docs).

Developer checklist (short-term roadmap)
1. Replace placeholder controllers with real implementations or restore originals from git history. (High)
2. Commit current work to `v2` and push snapshot (this was done). (Done / performed now)
3. Rebuild v2 frontend assets (run Vite build locally): `cd resources/assets/v2 && npm ci && npm run build`. (Next)
4. Set `FIREFLY_III_LAYOUT=v2` in runtime and restart containers (dev: `docker-compose -f docker-compose.local.yml up -d --build`).
5. Run browser-based verification for authenticated user. Verify React mount and API calls. (Next)
6. Replace stub controllers and add unit-tests for endpoints (CouplesController tests, AI endpoints tests). (Medium)

Acceptance criteria (for MVP v2 rollout)
- Authenticated user navigating to `/` sees v2 dashboard layout and React bundle loaded.
- `/couples/dashboard` renders couples React UI and allows receipt upload that triggers `/api/v1/couples/upload-receipt` and returns normalized extraction JSON.
- `/ai` shows AI dashboard and can run connectivity test to LangExtract and Ollama.
- No fatal exceptions on view rendering (error layouts resilient).

Next steps for me (if you want me to proceed):
- Commit & push WIP to `v2` (I will do that now per your instruction).
- Optionally run local Vite build (outside containers) and commit built `public/build` files if you prefer them stored in repo.
- Create simple end-to-end smoke tests (PHPUnit + a lightweight Cypress or Playwright script) for key pages.

Notes
- LangExtractService is already implemented and tested (see `PROJECT_STATUS_LANGEXTRACT_COMPLETE.md` attached in repo).
- Shadcn components and React v2 assets are present (see `resources/assets/v2`) — build step required.
- Many placeholder controllers were created earlier to unblock route registration; those need proper replacement before production.


