# Graph Report - api-elysia  (2026-07-10)

## Corpus Check
- 51 files · ~14,292 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 235 nodes · 501 edges · 14 communities
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
- media.service.ts
- index.ts
- MemberProfileData

## God Nodes (most connected - your core abstractions)
1. `prisma` - 18 edges
2. `AuthService` - 13 edges
3. `AddressDetailData` - 11 edges
4. `PaginatedResponse` - 11 edges
5. `AddressService` - 9 edges
6. `JWTService` - 9 edges
7. `AuthResponse` - 9 edges
8. `compilerOptions` - 9 edges
9. `⚡ Claude Code System Directive: HK GOLD VIP Backend (apps/api-elysia)` - 9 edges
10. `UpdateAddressRequest` - 8 edges

## Surprising Connections (you probably didn't know these)
- `UpdateMemberProfileRequest` --references--> `UpdateAddressRequest`  [EXTRACTED]
  src/modules/member/types/member.types.ts → src/modules/address/types/address.types.ts
- `MemberProfileData` --references--> `AddressDetailData`  [EXTRACTED]
  src/modules/member/types/member.types.ts → src/modules/address/types/address.types.ts
- `AddressService` --implements--> `IAddressService`  [EXTRACTED]
  src/modules/address/services/address.service.ts → src/modules/address/interfaces/address.interface.ts
- `AuthService` --implements--> `IAuthService`  [EXTRACTED]
  src/modules/auth/services/auth.service.ts → src/modules/auth/interfaces/auth.interface.ts
- `BranchService` --implements--> `IBranchService`  [EXTRACTED]
  src/modules/branch/services/branch.service.ts → src/modules/branch/interfaces/branch.interface.ts

## Import Cycles
- None detected.

## Communities (14 total, 0 thin omitted)

### Community 0 - "branch.service.ts"
Cohesion: 0.13
Nodes (20): IBranchService, BranchService, BranchDetailData, BranchDetailResponse, BranchImageData, BranchListItemData, BranchListResponse, GetBranchesParams (+12 more)

### Community 1 - "auth.service.ts"
Cohesion: 0.14
Nodes (17): prisma, IAuthService, authRoutes, AuthService, generateMemberNumber(), normalizePhoneNumber(), validateEmail(), AuthResponse (+9 more)

### Community 2 - "index.ts"
Cohesion: 0.50
Nodes (3): IHealthService, HealthService, HealthCheckResponse

### Community 3 - "content.service.ts"
Cohesion: 0.16
Nodes (14): IAddressService, addressInclude, AddressService, AddressWithRelations, toDetail(), AddressDetailData, AddressDetailResponse, AddressRegionData (+6 more)

### Community 4 - "package.json"
Cohesion: 0.11
Nodes (17): dependencies, @aws-sdk/client-s3, elysia, @elysiajs/cookie, @elysiajs/jwt, mysql2, @prisma/client, devDependencies (+9 more)

### Community 5 - "auth.middleware.ts"
Cohesion: 0.40
Nodes (4): JWTService, parseExpiry(), JWTPayload, TokenPair

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

### Community 11 - "media.service.ts"
Cohesion: 0.20
Nodes (12): IMediaService, generateFileName(), getS3Client(), getS3Config(), MediaService, parseFileFromRequest(), S3Config, toMediaData() (+4 more)

### Community 12 - "index.ts"
Cohesion: 0.15
Nodes (12): app, AuthContext, authMiddleware, requireActiveUser, requireAuth, requireNotSuspended, addressRoutes, branchRoutes (+4 more)

### Community 13 - "MemberProfileData"
Cohesion: 0.38
Nodes (4): IMemberService, MemberService, MemberProfileData, UpdateMemberProfileRequest

## Knowledge Gaps
- **64 isolated node(s):** `name`, `version`, `test`, `test:watch`, `dev` (+59 more)
  These have ≤1 connection - possible missing edges or undocumented components.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `prisma` connect `auth.service.ts` to `branch.service.ts`, `index.ts`, `content.service.ts`, `media.service.ts`, `index.ts`?**
  _High betweenness centrality (0.136) - this node is a cross-community bridge._
- **Why does `JWTService` connect `auth.middleware.ts` to `auth.service.ts`, `index.ts`?**
  _High betweenness centrality (0.032) - this node is a cross-community bridge._
- **What connects `name`, `version`, `test` to the rest of the system?**
  _64 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `branch.service.ts` be split into smaller, more focused modules?**
  _Cohesion score 0.1282051282051282 - nodes in this community are weakly interconnected._
- **Should `auth.service.ts` be split into smaller, more focused modules?**
  _Cohesion score 0.1379800853485064 - nodes in this community are weakly interconnected._
- **Should `package.json` be split into smaller, more focused modules?**
  _Cohesion score 0.1111111111111111 - nodes in this community are weakly interconnected._
- **Should `index.ts` be split into smaller, more focused modules?**
  _Cohesion score 0.14619883040935672 - nodes in this community are weakly interconnected._