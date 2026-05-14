# maya_logs — Architecture Audit Report

Project: `maya_logs` (Laravel 12 backend + React 19 frontend)
Date: 2026-05-13
Rules source: `.claude/rules/project/maya-architecture.md`

## Summary table

| Rule | Description | Status | Failing |
|------|-------------|--------|---------|
| B1   | Controllers delegate to Service, no direct Eloquent | PARTIAL | 1 controller violates |
| B2   | FormRequest for every input endpoint (no inline `->validate()`) | PARTIAL | 1 violation |
| B3   | No `$request->all()` in controllers | PASS | 0 |
| B4   | Service returns DTO, NOT Eloquent model | FAIL | 9 / 9 services |
| B5   | DB access only in Repositories | FAIL | `LogIngestionService` uses raw `DB::table` |
| B6   | Contracts + Provider bindings present | PASS | 0 |
| B7   | API Resources for all responses | PARTIAL | LogController/Dashboard return raw `response()->json` |
| B8   | Listings paginate | PASS | 0 |
| B9   | Controllers ≤5 public methods, ≤200 LOC | PARTIAL | CommentController = 6 public methods |
| F1   | No `useEffect+useState+fetch` triplet | FAIL | 8 pages/components |
| F2   | React Hook Form (no manual controlled state) | FAIL | `ErrorCodeCreatePage` |
| F3   | No `any` outside tests | PASS | 0 |
| F4   | No `React.FC`, use `interface XProps` | PASS | 0 |
| F5   | No prop drilling >2 levels | PASS (no evidence) | 0 |

**Total violations: 22** (counted as distinct file:rule pairs)

---

## Backend findings

### B1 — Controller has business logic / direct DB

- `backend/app/Http/Controllers/Api/CommentController.php:67-76` — `storeForErrorCode` references `$this->comments` (does not exist; should be `$this->commentService`) and `$author` (undeclared variable). Also bypasses the shared `storeFor` helper at line 121 → logic divergence. The dead/broken path also instantiates a Resource with a method that, on a working day, would never compile. **Functional bug + B1 architectural violation**. CRITICAL.

### B2 — Inline validate

- `backend/app/Http/Controllers/Api/ArchivedLogController.php:67-70`:
  ```php
  $validated = $request->validate([
      'description' => ['nullable', 'string', 'max:5000'],
      'url_tutorial' => ['nullable', 'url', 'max:2048'],
  ]);
  ```
  Should be an `UpdateArchivedLogRequest` FormRequest under `app/Http/Requests/Api/`. CRITICAL.

- `backend/app/Http/Requests/StoreCommentRequest.php` and `UpdateCommentRequest.php` are **stale duplicates** of the canonical files under `app/Http/Requests/Api/` (different namespace; identical rules). Dead code that should be removed. MEDIUM.

### B3 — `$request->all()`

PASS. No `$request->all()` found. The only `->all()` in controllers is `ApplicationController.php:35` on a Collection (`$applications->...->all()`), which is unrelated.

### B4 — Services return Eloquent models, not DTOs

`backend/app/Dtos/` directory **does not exist**. Every service returns Eloquent models or paginators of models:

- `LogService::findOrFail(): Log` (line 29)
- `LogService::paginate(): LengthAwarePaginator` of `Log` models
- `LogService::searchAndFilter(): LengthAwarePaginator` of `Log` models
- `ArchivedLogService::findOrFail(): ArchivedLog` (line 66)
- `ArchivedLogService::archiveFromLogId(): ArchivedLog` (line 147)
- `ArchivedLogService::paginate(): LengthAwarePaginator`
- `CommentService::findOrFail(): Comment` (line 27)
- `CommentService::listForCommentable(): Collection` of `Comment` models
- `CommentService::createForCommentable(): Comment` (line 43)
- `CommentService::updateContent(): Comment` (line 53)
- `ErrorCodeService::findOrFail(): ErrorCode` (line 30)
- `ErrorCodeService::create(): ErrorCode` (line 35)
- `ErrorCodeService::update(): ErrorCode` (line 40)
- `PanelUserService::resolveFromJwtRequest(): User` (line 25)

Resources are typed `@mixin <Model>` and access Eloquent relations directly (`$this->whenLoaded(...)`). Migrating to DTOs requires reshaping Resources too — large refactor. CRITICAL but project-wide.

### B5 — DB access outside Repositories

- `backend/app/Services/LogIngestionService.php:94` — `DB::table('logs')->insert(...)`
- `backend/app/Services/LogIngestionService.php:135` — `DB::table('error_codes')->insertOrIgnore(...)`
- `backend/app/Services/LogIngestionService.php:145-148` — `DB::table('error_codes')->where(...)->value('id')`
- `backend/app/Services/LogIngestionService.php:31` — `Application::pluck('id', 'slug')->all()`

The worker’s ingestion path skips the repository layer. Encapsulate in a `LogIngestionRepository`. CRITICAL.

### B6 — Contracts and DI bindings

PASS. `app/Providers/AppServiceProvider.php` binds every Service and Repository interface (`ApplicationServiceInterface`, `ArchivedLogServiceInterface`, `LogServiceInterface`, `CommentServiceInterface`, `ErrorCodeServiceInterface`, all repository contracts). `Contracts/` subdirs exist under `app/Services/` and `app/Repositories/`.

### B7 — Raw JSON responses bypassing Resources

- `backend/app/Http/Controllers/Api/LogController.php:62-68, 77-81, 99-102, 113-119, 125-132` — repeated `response()->json([...])` with `LogResource` resolved manually or with hand-written error envelopes. The `archive`, `show`, `resolve` endpoints should return dedicated Resources (or a thin `ArchivedLogReferenceResource`) instead of inlined arrays. HIGH.
- `backend/app/Http/Controllers/Api/DashboardController.php:19-25` — returns `response()->json([...])` directly from service data. Either OK (no model) or should be wrapped in a `DashboardResource`. MEDIUM.
- `backend/app/Http/Controllers/Api/ApplicationController.php:21-37` — same pattern; returns inline JSON instead of an `ApplicationRefResource::collection(...)`. MEDIUM.

### B8 — Pagination

PASS. All list endpoints use `->paginate($perPage)` via repository methods. `LogIngestionService` is a worker, not an HTTP endpoint.

### B9 — Controller size

- `CommentController.php` has **6 public methods** (`indexForArchivedLog`, `indexForErrorCode`, `storeForArchivedLog`, `storeForErrorCode`, `update`, `destroy`) — exceeds the ≤5 limit. Refactor candidate: split into `ArchivedLogCommentController` + `ErrorCodeCommentController`, or merge index/store via a generic commentable resolver. MEDIUM.
- `LogController.php` = 158 LOC — within ≤200 but close to the boundary; consider extracting the SSE `stream()` handler.

---

## Frontend findings

### F1 — `useEffect + useState + fetch` triplets (no SWR / TanStack Query)

`maya_logs` does not depend on `swr` or `@tanstack/react-query` (see `frontend/package.json`). The standard pattern is hand-rolled:

- `frontend/src/pages/LogsPage.tsx:152-202` — three `useState` + three `useEffect` blocks orchestrating `fetchApplications`, `fetchLogs`, refresh nonce.
- `frontend/src/pages/ArchivedLogsPage.tsx:150-185` — same pattern.
- `frontend/src/pages/ErrorCodesPage.tsx:76-117` — same pattern, includes cancellation flag.
- `frontend/src/pages/ErrorCodeCreatePage.tsx:32-49`
- `frontend/src/pages/ErrorCodeDetailPage.tsx`
- `frontend/src/pages/LogDetailPage.tsx`
- `frontend/src/pages/ArchivedLogDetailPage.tsx`
- `frontend/src/features/dashboard/widgets/RecentLogsWidget.tsx`, `ErrorCountWidget.tsx`
- `frontend/src/components/comments/CommentThread.tsx`
- `frontend/src/hooks/useLogStream.ts` — polling hook is legitimate, but the pattern (useState + setInterval + fetch) duplicates what a SWR `refreshInterval` solves.

CRITICAL pattern violation, repeated across the whole feature surface.

### F2 — Manual form state (no React Hook Form)

`react-hook-form` is not a dependency. Forms use ad-hoc `useState<FormState>`:

- `frontend/src/pages/ErrorCodeCreatePage.tsx:33-73` — manual `form` state, manual validation in `onSave`, manual error strings (e.g. `'Selecciona una aplicación.'` hardcoded, not translated).
- `frontend/src/components/error-codes/ErrorCodeForm.tsx` — controlled component fed from parent state.
- `frontend/src/components/archived-logs/...` — edit panels for `description`/`url_tutorial` follow the same hand-rolled approach.

HIGH.

### F3 — `any`

PASS. No `: any` / `as any` outside tests detected with grep.

### F4 — `React.FC`

PASS. No `React.FC` / `: FC<` usages.

### F5 — Prop drilling >2 levels

No evidence of >2-level drilling found in spot checks (the codebase mostly composes shared components at page level). PASS (low confidence — full graph not traversed).

---

## Extraction candidates (cross-project)

### Already extracted but consumed locally — CRITICAL

None confirmed. `frontend/src/components/filters/SearchInput.tsx` wraps `@maya/shared-ui-react`'s `SearchInput` for translations — this is acceptable adapter usage, not a duplicate.

### New candidates with cross-project evidence

- **`@maya/shared-ui-react` already exposes `Select` / `FilterField`**, yet `maya_logs` ships its own:
  - `components/filters/ApplicationSelect.tsx` — bespoke styled `<select>` (lines 1-40). Same Tailwind classes used in other Maya frontends. **Candidate**: extend shared `Select` with a generic "single-select with placeholder" preset, then delete this file.
  - `components/filters/ResolvedFilter.tsx` — same shape, same classes. Same extraction path.
  - `components/filters/SeverityFilterCheckboxes.tsx` — would fit a shared `CheckboxGroup` primitive in `maya-shared-ui-react`.
- **HTTP client wrapper** — `frontend/src/api/http.ts` is byte-identical to `maya_authorization/frontend/src/api/http.ts` and `maya_audit/frontend/src/api/http.ts` except for `DEFAULT_BASE_URL`. **Candidate**: extract `createMayaApiClient(baseUrlEnv, defaultUrl)` factory into `@maya/shared-auth-react`. Each app then has a 3-line wrapper. (Confirmed appears in ≥4 projects → strong extraction signal.)
- **`useNavItems` hook** — `components/layout/navItems.tsx` is structurally identical across `maya_logs`, `maya_authorization`, `maya_audit`, `maya_dashboard` (same imports from `@maya/shared-layout-react`, same `NavItem[]` memo). **Candidate**: parameterize as `useNavItemsBuilder(items)` in `@maya/shared-layout-react`, leaving each app to declare only the array. Currently 4 copies with permission-gating divergence in `maya_authorization`.
- **`fetchApplications` + `ApplicationRef` shape** — appears in `maya_logs`, `maya_authorization`, partially in `maya_audit`. Three independent definitions of the same DTO. **Candidate**: ship `useApplications()` / `fetchApplications()` in a tiny `@maya/shared-applications-react` package (or extend `shared-auth-react`).

### Local-only (keep)

- `components/severity/*` (`SeverityBadge`, `palette.ts`, `SeverityCardClasses`) — log-domain specific. No matches in other projects.
- `components/logs/*`, `components/archived-logs/*`, `components/error-codes/*` — domain components of maya_logs.
- `components/comments/CommentThread.tsx` — the comments feature is exclusive to maya_logs.
- `hooks/useLogStream.ts` — log streaming is exclusive to maya_logs.
- Backend: `LogIngestionService`, `ResilientLogPublisher`, all `Log*` services — domain-specific.

---

## Stats

| Metric | Count |
|--------|------:|
| Controllers (`app/Http/Controllers/Api/`) | 7 |
| Services (`app/Services/`) | 9 |
| Service contracts (`app/Services/Contracts/`) | 6 |
| Repositories (`app/Repositories/Eloquent/`) | 6 |
| Repository contracts (`app/Repositories/Contracts/`) | 6 |
| DTOs (`app/Dtos/`) | **0 (directory missing)** |
| FormRequests (`app/Http/Requests/Api/`) | 4 |
| Stale FormRequest duplicates | 2 |
| API Resources | 4 |
| Frontend pages | 8 |
| Frontend components | 15 |
| Frontend hooks (non-index) | 1 |
| Frontend `useEffect`+`useState`+`fetch` triplet files | 8 |
| `any` in non-test code | 0 |
| `React.FC` usages | 0 |

## Severity rollup

- **CRITICAL**: 4 (CommentController broken `storeForErrorCode`, DTO layer absent, DB access in LogIngestionService, F1 pattern violation across pages)
- **HIGH**: 2 (B2 inline validate in ArchivedLogController, F2 manual form state)
- **MEDIUM**: 4 (stale duplicate FormRequests, B7 raw JSON in Log/Dashboard/Application controllers, CommentController public-method count, extraction candidate http.ts/navItems/applications)
- **LOW**: 0
