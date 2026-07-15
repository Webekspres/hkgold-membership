# Graph Report - api-elysia  (2026-07-15)

## Corpus Check
- 80 files · ~29,964 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 404 nodes · 866 edges · 17 communities
- Extraction: 100% EXTRACTED · 0% INFERRED · 0% AMBIGUOUS · INFERRED: 1 edges (avg confidence: 0.5)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `451e5cdb`
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
- reward.service.ts
- tier.service.ts
- promotion-banner.service.ts

## God Nodes (most connected - your core abstractions)
1. `prisma` - 27 edges
2. `RewardService` - 15 edges
3. `AuthService` - 13 edges
4. `AddressDetailData` - 11 edges
5. `PaginatedResponse` - 11 edges
6. `⚡ Claude Code System Directive: HK GOLD VIP Backend (apps/api-elysia)` - 11 edges
7. `AddressService` - 9 edges
8. `JWTService` - 9 edges
9. `AuthResponse` - 9 edges
10. `compilerOptions` - 9 edges

## Surprising Connections (you probably didn't know these)
- `AddressService` --implements--> `IAddressService`  [EXTRACTED]
  src/modules/address/services/address.service.ts → src/modules/address/interfaces/address.interface.ts
- `UpdateMemberProfileRequest` --references--> `UpdateAddressRequest`  [EXTRACTED]
  src/modules/member/types/member.types.ts → src/modules/address/types/address.types.ts
- `MemberProfileData` --references--> `AddressDetailData`  [EXTRACTED]
  src/modules/member/types/member.types.ts → src/modules/address/types/address.types.ts
- `AuthService` --implements--> `IAuthService`  [EXTRACTED]
  src/modules/auth/services/auth.service.ts → src/modules/auth/interfaces/auth.interface.ts
- `BranchService` --implements--> `IBranchService`  [EXTRACTED]
  src/modules/branch/services/branch.service.ts → src/modules/branch/interfaces/branch.interface.ts

## Import Cycles
- None detected.

## Communities (17 total, 0 thin omitted)

### Community 0 - "branch.service.ts"
Cohesion: 0.11
Nodes (27): IBranchService, addressInclude, branchListInclude, BranchService, mapBranch(), mapCitySubdistrict(), BranchCityOption, BranchDetailData (+19 more)

### Community 1 - "auth.service.ts"
Cohesion: 0.10
Nodes (18): prisma, IAuthService, authMemberSelect, AuthService, authUserSelect, generateMemberNumber(), normalizePhoneNumber(), validateEmail() (+10 more)

### Community 2 - "index.ts"
Cohesion: 0.42
Nodes (4): IHealthService, healthRoutes, HealthService, HealthCheckResponse

### Community 3 - "content.service.ts"
Cohesion: 0.11
Nodes (19): requireActiveUser, IAddressService, addressInclude, AddressService, AddressWithRelations, toDetail(), AddressDetailData, AddressDetailResponse (+11 more)

### Community 4 - "package.json"
Cohesion: 0.09
Nodes (21): dependencies, @aws-sdk/client-s3, elysia, @elysiajs/cookie, @elysiajs/jwt, ioredis, mysql2, @prisma/client (+13 more)

### Community 5 - "auth.middleware.ts"
Cohesion: 0.10
Nodes (24): createMemoryRedis(), createRedisClient(), getRedis(), RedisLike, setRedisForTests(), requireInternalSecret, otpRoutes, FonnteError (+16 more)

### Community 6 - "⚡ Claude Code System Directive: HK GOLD VIP Backend (apps/api-elysia)"
Cohesion: 0.14
Nodes (13): 🏗️ 1. Lingkungan Infrastruktur & Konektivitas Jaringan, 🏛️ 2. Pola Arsitektur: Modular Monolith & Service Aggregator (Wajib), 🧠 3. Kebijakan Autentikasi & Keamanan (JWT Stateless Strategy), 🪙 4. Manajemen Riwayat Transaksi & Point Ledger (Real-time Query), 🎁 5. Siklus Hidup Hybrid Redeem Reward Engine (Token Code Generator), 🚨 6. Logika Pengawasan Keamanan Dual-Layer (`is_active` vs `is_suspended`), 📋 7. Standar Penulisan Kode & Kualitas (Bun + ElysiaJS + Prisma), 🧩 8. Gap Schema vs Kontrak Mobile (jangan asumsikan kolom ada) (+5 more)

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
Cohesion: 0.11
Nodes (16): app, AuthContext, authMiddleware, requireAuth, addressRoutes, authRoutes, JWTService, parseExpiry() (+8 more)

### Community 13 - "MemberProfileData"
Cohesion: 0.11
Nodes (24): requireNotSuspended, IRedeemService, redeemRoutes, generateUniqueTokenCode(), invoiceInclude, InvoiceWithRelations, mapInvoice(), mapToken() (+16 more)

### Community 14 - "reward.service.ts"
Cohesion: 0.14
Nodes (14): IRewardService, RewardService, decodeCursor(), encodeCursor(), GetRewardsParams, PaginationResponse, RewardBranchStockData, RewardCatalogItemData (+6 more)

### Community 15 - "tier.service.ts"
Cohesion: 0.22
Nodes (10): ITierService, TIER_COLORS, TIER_NAMES, TierService, ConversionRuleData, GetMemberTierResponse, GetTierLevelsResponse, MemberTierData (+2 more)

### Community 16 - "promotion-banner.service.ts"
Cohesion: 0.35
Nodes (4): IPromotionBannerService, promotionBannerRoutes, PromotionBannerService, PromotionBannerData

## Knowledge Gaps
- **90 isolated node(s):** `name`, `version`, `test`, `test:watch`, `dev` (+85 more)
  These have ≤1 connection - possible missing edges or undocumented components.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `prisma` connect `auth.service.ts` to `branch.service.ts`, `index.ts`, `content.service.ts`, `auth.middleware.ts`, `media.service.ts`, `index.ts`, `MemberProfileData`, `reward.service.ts`, `tier.service.ts`, `promotion-banner.service.ts`?**
  _High betweenness centrality (0.240) - this node is a cross-community bridge._
- **Why does `RewardService` connect `reward.service.ts` to `auth.service.ts`, `index.ts`?**
  _High betweenness centrality (0.038) - this node is a cross-community bridge._
- **Why does `JWTService` connect `index.ts` to `auth.service.ts`?**
  _High betweenness centrality (0.021) - this node is a cross-community bridge._
- **What connects `name`, `version`, `test` to the rest of the system?**
  _90 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `branch.service.ts` be split into smaller, more focused modules?**
  _Cohesion score 0.10917874396135266 - nodes in this community are weakly interconnected._
- **Should `auth.service.ts` be split into smaller, more focused modules?**
  _Cohesion score 0.09528214616096208 - nodes in this community are weakly interconnected._
- **Should `content.service.ts` be split into smaller, more focused modules?**
  _Cohesion score 0.11463414634146342 - nodes in this community are weakly interconnected._