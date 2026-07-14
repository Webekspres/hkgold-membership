# Graph Report - api-elysia  (2026-07-10)

## Corpus Check
- 36 files · ~9,551 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 167 nodes · 319 edges · 11 communities
- Extraction: 100% EXTRACTED · 0% INFERRED · 0% AMBIGUOUS
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `ba50b8d0`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- branch.service.ts
- auth.service.ts
- index.ts
- content.service.ts
- package.json
- auth.middleware.ts
- ⚡ Claude Code System Directive: HK GOLD VIP Backend (apps/api-elysia)
- Auth Module
- compilerOptions
- Health Module
- Elysia with Bun runtime

## God Nodes (most connected - your core abstractions)
1. `prisma` - 13 edges
2. `PaginatedResponse` - 11 edges
3. `AuthService` - 10 edges
4. `JWTService` - 9 edges
5. `AuthResponse` - 9 edges
6. `compilerOptions` - 9 edges
7. `⚡ Claude Code System Directive: HK GOLD VIP Backend (apps/api-elysia)` - 9 edges
8. `IAuthService` - 7 edges
9. `JWTPayload` - 7 edges
10. `BranchService` - 7 edges

## Surprising Connections (you probably didn't know these)
- `GetContentsParams` --inherits--> `CursorPaginationParams`  [EXTRACTED]
  src/modules/content/types/content.types.ts → src/shared/types/pagination.types.ts
- `AuthService` --implements--> `IAuthService`  [EXTRACTED]
  src/modules/auth/services/auth.service.ts → src/modules/auth/interfaces/auth.interface.ts
- `BranchService` --implements--> `IBranchService`  [EXTRACTED]
  src/modules/branch/services/branch.service.ts → src/modules/branch/interfaces/branch.interface.ts
- `GetBranchesParams` --inherits--> `CursorPaginationParams`  [EXTRACTED]
  src/modules/branch/types/branch.types.ts → src/shared/types/pagination.types.ts
- `ContentService` --implements--> `IContentService`  [EXTRACTED]
  src/modules/content/services/content.service.ts → src/modules/content/interfaces/content.interface.ts

## Import Cycles
- None detected.

## Communities (11 total, 0 thin omitted)

### Community 0 - "branch.service.ts"
Cohesion: 0.18
Nodes (13): prisma, IBranchService, BranchService, BranchDetailData, BranchDetailResponse, BranchImageData, BranchListItemData, BranchListResponse (+5 more)

### Community 1 - "auth.service.ts"
Cohesion: 0.18
Nodes (14): IAuthService, AuthService, generateMemberNumber(), normalizePhoneNumber(), validateEmail(), AuthResponse, ChangePasswordRequest, ChangePasswordResponse (+6 more)

### Community 2 - "index.ts"
Cohesion: 0.17
Nodes (10): app, authRoutes, branchRoutes, contentRoutes, IHealthService, healthRoutes, HealthService, HealthCheckResponse (+2 more)

### Community 3 - "content.service.ts"
Cohesion: 0.22
Nodes (8): IContentService, ContentService, ContentCoverImageData, ContentDetailData, ContentDetailResponse, ContentListItemData, ContentListResponse, GetContentsParams

### Community 4 - "package.json"
Cohesion: 0.12
Nodes (16): dependencies, elysia, @elysiajs/cookie, @elysiajs/jwt, mysql2, @prisma/client, devDependencies, bun-types (+8 more)

### Community 5 - "auth.middleware.ts"
Cohesion: 0.21
Nodes (9): AuthContext, authMiddleware, requireActiveUser, requireAuth, requireNotSuspended, JWTService, parseExpiry(), JWTPayload (+1 more)

### Community 6 - "⚡ Claude Code System Directive: HK GOLD VIP Backend (apps/api-elysia)"
Cohesion: 0.17
Nodes (11): 🏗️ 1. Lingkungan Infrastruktur & Konektivitas Jaringan, 🏛️ 2. Pola Arsitektur: Modular Monolith & Service Aggregator (Wajib), 🧠 3. Kebijakan Autentikasi & Keamanan (JWT Stateless Strategy), 🪙 4. Manajemen Riwayat Transaksi & Point Ledger (Real-time Query), 🎁 5. Siklus Hidup Hybrid Redeem Reward Engine (Token Code Generator), 🚨 6. Logika Pengawasan Keamanan Dual-Layer (`is_active` vs `is_suspended`), 📋 7. Standar Penulisan Kode & Kualitas (Bun + ElysiaJS + Prisma), 🛠️ 8. Perintah Eksekusi Pertama Anda (+3 more)

### Community 7 - "Auth Module"
Cohesion: 0.20
Nodes (9): Auth Module, Business Rules, Endpoints, Environment Variables, POST /api/auth/change-password, POST /api/auth/login, POST /api/auth/refresh, POST /api/auth/register (+1 more)

### Community 8 - "compilerOptions"
Cohesion: 0.20
Nodes (9): compilerOptions, esModuleInterop, forceConsistentCasingInFileNames, module, moduleResolution, skipLibCheck, strict, target (+1 more)

### Community 9 - "Health Module"
Cohesion: 0.40
Nodes (4): Endpoints, GET /api/health, Health Module, Struktur

### Community 10 - "Elysia with Bun runtime"
Cohesion: 0.50
Nodes (3): Development, Elysia with Bun runtime, Getting Started

## Knowledge Gaps
- **57 isolated node(s):** `name`, `version`, `test`, `test:watch`, `dev` (+52 more)
  These have ≤1 connection - possible missing edges or undocumented components.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `prisma` connect `branch.service.ts` to `auth.service.ts`, `index.ts`, `content.service.ts`, `auth.middleware.ts`?**
  _High betweenness centrality (0.103) - this node is a cross-community bridge._
- **Why does `JWTService` connect `auth.middleware.ts` to `auth.service.ts`?**
  _High betweenness centrality (0.041) - this node is a cross-community bridge._
- **Why does `AuthService` connect `auth.service.ts` to `branch.service.ts`?**
  _High betweenness centrality (0.018) - this node is a cross-community bridge._
- **What connects `name`, `version`, `test` to the rest of the system?**
  _57 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `package.json` be split into smaller, more focused modules?**
  _Cohesion score 0.11764705882352941 - nodes in this community are weakly interconnected._