# Graph Report - api-elysia  (2026-07-21)

## Corpus Check
- 89 files · ~39,876 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 463 nodes · 999 edges · 19 communities (18 shown, 1 thin omitted)
- Extraction: 100% EXTRACTED · 0% INFERRED · 0% AMBIGUOUS · INFERRED: 1 edges (avg confidence: 0.5)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `907c5d39`
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
- redeem-routes.test.ts
- test-setup.ts

## God Nodes (most connected - your core abstractions)
1. `prisma` - 31 edges
2. `RewardService` - 15 edges
3. `AuthService` - 14 edges
4. `AddressService` - 12 edges
5. `AddressDetailData` - 11 edges
6. `PaginatedResponse` - 11 edges
7. `⚡ Claude Code System Directive: HK GOLD VIP Backend (apps/api-elysia)` - 11 edges
8. `JWTService` - 10 edges
9. `MemberProfileData` - 10 edges
10. `RedeemService` - 10 edges

## Surprising Connections (you probably didn't know these)
- `UpdateMemberProfileRequest` --references--> `CreateAddressRequest`  [EXTRACTED]
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

## Communities (19 total, 1 thin omitted)

### Community 0 - "branch.service.ts"
Cohesion: 0.10
Nodes (27): IBranchService, addressInclude, branchListInclude, BranchService, mapBranch(), mapCitySubdistrict(), BranchCityOption, BranchDetailData (+19 more)

### Community 1 - "auth.service.ts"
Cohesion: 0.11
Nodes (19): prisma, IAuthService, authMemberSelect, AuthService, authUserSelect, generateMemberNumber(), normalizePhoneNumber(), validateEmail() (+11 more)

### Community 2 - "index.ts"
Cohesion: 0.29
Nodes (6): IHealthService, healthRoutes, HealthService, HealthCheckResponse, ApiResponse, ErrorResponse

### Community 3 - "content.service.ts"
Cohesion: 0.14
Nodes (15): IAddressService, addressInclude, AddressService, AddressWithRelations, toDetail(), AddressCascadeLevel, AddressCascadeOptionData, AddressCascadeOptionsResponse (+7 more)

### Community 4 - "package.json"
Cohesion: 0.09
Nodes (22): dependencies, @aws-sdk/client-s3, elysia, @elysiajs/cookie, @elysiajs/jwt, ioredis, mysql2, @prisma/client (+14 more)

### Community 5 - "auth.middleware.ts"
Cohesion: 0.11
Nodes (23): createMemoryRedis(), createRedisClient(), getRedis(), RedisLike, setRedisForTests(), requireInternalSecret, FonnteError, FonnteService (+15 more)

### Community 6 - "⚡ Claude Code System Directive: HK GOLD VIP Backend (apps/api-elysia)"
Cohesion: 0.10
Nodes (19): 🏗️ 1. Lingkungan Infrastruktur & Konektivitas Jaringan, 🏛️ 2. Pola Arsitektur: Modular Monolith & Service Aggregator (Wajib), 🧠 3. Kebijakan Autentikasi & Keamanan (JWT Stateless Strategy), 🪙 4. Manajemen Riwayat Transaksi & Point Ledger (Real-time Query), 5.1 Registrasi Device Push Token (FCM), 5.2 Cancel & status token (member), 🎁 5. Siklus Hidup Hybrid Redeem Reward Engine (Token Code Generator), 🚨 6. Logika Pengawasan Keamanan Dual-Layer (`is_active` vs `is_suspended`) (+11 more)

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
Cohesion: 0.18
Nodes (14): IMediaService, compressToWebp(), generateFileName(), getS3Client(), getS3Config(), keyFromUrl(), MediaService, parseFileFromRequest() (+6 more)

### Community 12 - "index.ts"
Cohesion: 0.09
Nodes (20): app, AuthContext, authMiddleware, requireActiveUser, requireAuth, requireNotSuspended, addressRoutes, authRoutes (+12 more)

### Community 13 - "MemberProfileData"
Cohesion: 0.09
Nodes (24): IRedeemService, generateUniqueTokenCode(), invoiceInclude, InvoiceWithRelations, mapInvoice(), mapToken(), randomTokenCode(), RedeemService (+16 more)

### Community 14 - "reward.service.ts"
Cohesion: 0.11
Nodes (19): IRewardService, BranchStockLike, filterInStockBranchStocks(), getAvailableStock(), hasAvailableStock(), sumAvailableStock(), RewardService, decodeCursor() (+11 more)

### Community 15 - "tier.service.ts"
Cohesion: 0.20
Nodes (11): ITierService, TIER_COLORS, TIER_NAMES, TierService, ConversionRuleData, GetMemberTierResponse, GetTierLevelsResponse, MemberTierData (+3 more)

### Community 16 - "promotion-banner.service.ts"
Cohesion: 0.21
Nodes (10): IMemberService, GENDERS, MemberService, parseBirthDate(), parseGender(), MemberGender, MemberProfileData, MemberProfileResponse (+2 more)

### Community 17 - "redeem-routes.test.ts"
Cohesion: 0.20
Nodes (6): JWTService, parseExpiry(), JWTPayload, TokenPair, redeemRoutes, app

## Knowledge Gaps
- **101 isolated node(s):** `name`, `version`, `test`, `test:watch`, `dev` (+96 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **1 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `prisma` connect `auth.service.ts` to `branch.service.ts`, `index.ts`, `content.service.ts`, `auth.middleware.ts`, `media.service.ts`, `index.ts`, `MemberProfileData`, `reward.service.ts`, `tier.service.ts`, `promotion-banner.service.ts`, `redeem-routes.test.ts`?**
  _High betweenness centrality (0.237) - this node is a cross-community bridge._
- **Why does `RewardService` connect `reward.service.ts` to `auth.service.ts`, `index.ts`?**
  _High betweenness centrality (0.028) - this node is a cross-community bridge._
- **Why does `JWTService` connect `redeem-routes.test.ts` to `auth.service.ts`, `index.ts`?**
  _High betweenness centrality (0.019) - this node is a cross-community bridge._
- **What connects `name`, `version`, `test` to the rest of the system?**
  _101 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `branch.service.ts` be split into smaller, more focused modules?**
  _Cohesion score 0.10372340425531915 - nodes in this community are weakly interconnected._
- **Should `auth.service.ts` be split into smaller, more focused modules?**
  _Cohesion score 0.10628019323671498 - nodes in this community are weakly interconnected._
- **Should `content.service.ts` be split into smaller, more focused modules?**
  _Cohesion score 0.1431451612903226 - nodes in this community are weakly interconnected._