<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.5
- filament/filament (FILAMENT) - v5
- laravel/framework (LARAVEL) - v13
- laravel/prompts (PROMPTS) - v0
- livewire/livewire (LIVEWIRE) - v4
- laravel/boost (BOOST) - v2
- laravel/mcp (MCP) - v0
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- pestphp/pest (PEST) - v4
- phpunit/phpunit (PHPUNIT) - v12
- tailwindcss (TAILWINDCSS) - v4

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Always use `search-docs` before making code changes. Do not skip this step. It returns version-specific docs based on installed packages automatically.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Follow existing application Enum naming conventions.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

## Pest

- This project uses Pest for testing. Create tests: `php artisan make:test --pest {name}`.
- The `{name}` argument should not include the test suite directory. Use `php artisan make:test --pest SomeFeatureTest` instead of `php artisan make:test --pest Feature/SomeFeatureTest`.
- Run tests: `php artisan test --compact` or filter: `php artisan test --compact --filter=testName`.
- Do NOT delete tests without approval.

</laravel-boost-guidelines>

---

# HK Gold Backoffice — Project Guidelines

Guidelines below are specific to this app (`apps/backoffice-filament`). They extend (not replace) the Laravel Boost rules above.

## Project Context

- **Product:** HK Gold VIP — integrated membership & loyalty platform for luxury gold retail (Mala Emas).
- **This app:** Filament v5 admin backoffice for staff operations.
- **Monorepo:** `hkgold-membership` — shared schema in `packages/database/schema.prisma`, ElysiaJS API backend, Laravel backoffice here.
- **Active branch:** `backoffice` for Filament work.

## Monorepo & Environment

- **Schema source of truth:** Always read `packages/database/schema.prisma` **before** writing migrations, models, or Filament resources. Laravel migrations and Eloquent models must stay in sync with Prisma (`@@map`, column names, types, relations).
- **Prisma → Laravel mapping:** Follow `.cursor/rules/make-resource.mdc` for UUID keys, `HasUuids`, `@@map` table names, soft deletes only when Prisma has `deletedAt`, and realistic Indonesian faker context.
- **Local stack:** Docker provides MySQL (`33068`), Redis (`6381`), MinIO (`9002` API / `9003` console). Object storage uses disk name `r2` — MinIO locally, Cloudflare R2 in production (env-only difference).
- **Dev server:** `php artisan serve --port=8800` from `apps/backoffice-filament/`.
- **R2 wipe on fresh seed:** `migrate:fresh --seed` may wipe the `r2` bucket when `wipe_r2_on_fresh_seed` is enabled (local default).

## Git Workflow

- Do **not** commit or push unless the user explicitly asks.
- Do **not** create documentation files unless requested.
- Keep changes scoped — avoid unrelated refactors in the same task.

## UI Language & Navigation

- All Filament labels use **Bahasa Indonesia**: `navigationLabel`, `modelLabel`, `pluralModelLabel`, section headings, notifications, empty states.
- Follow existing `navigationGroup` conventions:
  - `Manajemen Pengguna` — Member, Staff, Cabang
  - `Master Lokasi` — Provinsi, Kota, Kecamatan, Kelurahan, Kode Pos
  - `CMS` — Konten, Banner Promosi
  - `Katalog Reward` — Kategori Reward, dll.
- Reuse `Heroicon::Outlined*` icons consistent with sibling resources.

## Filament Architecture

Use the **split structure** already in this codebase — do not inline large schemas in the Resource class:

```
app/Filament/
├── Resources/{Name}/
│   ├── {Name}Resource.php      # thin: model, labels, delegates form/table/infolist
│   ├── Schemas/                # {Name}Form.php, {Name}Infolist.php
│   ├── Tables/                 # {Name}sTable.php
│   ├── Support/                # {Name}FormSupport.php — business logic, normalization
│   ├── Pages/                  # List, Create, Edit, View
│   ├── RelationManagers/       # when needed on View/Edit
│   └── Widgets/                # stats/charts on List pages
└── Pages/                      # custom Pages (non-standard CRUD)
    └── Support/
```

- Every new PHP file: `declare(strict_types=1);`
- Resource class stays thin — wire `Form::configure()`, `Table::configure()`, `Infolist::configure()`.
- Put save/mutate logic in **Page** classes (`mutateFormDataBeforeCreate`, `afterCreate`, `handleRecordUpdate`) or **Support** classes, not in schema classes.
- **Page vs Resource:** If unsure whether a feature should be a Filament Resource or custom Page, **ask the user first**. Default assumption: standard entity CRUD → Resource; singleton/settings/repeater-without-entity → Page (e.g. `PromotionBannerPage`).

## Domain Conventions

### Members & Staff

- Member number format: `HK` + letter + 7 digits — use `MemberFormSupport::generateMemberNumber()`.
- Tier/status fields use existing enums (`TierStatus`, etc.).

### Branches (Cabang)

- Branch code: `HK01`, `HK02`, … — use `BranchFormSupport::generateBranchCode()`. Disable `branch_code` on edit.
- `is_online_warehouse` for online warehouse flag.
- `address` (text) for display; `address_id` → normalized `Address` model.

### Points, Redeem, Rewards

- Respect loyalty/point business rules from Prisma models (`PointMutation`, `RedeemInvoice`, `RewardBranchStock`, etc.).
- Use realistic Indonesian retail amounts in factories/seeders (not generic single-digit values).

## Data Normalization

### Phone numbers

- UI prefix: `+62` on `TextInput`.
- Display: `MemberFormSupport::formatPhoneForDisplay()`.
- Persist: `MemberFormSupport::normalizePhone()` → stored as `62xxxxxxxxxx` (no `+`).
- Reuse this for Member, Branch, Staff — do not duplicate phone logic.

### Address (cascading selects)

- Flow: `province_id` → `city_id` → `sub_district_id` → `village_id` → `postal_code_id` → `street`.
- Persist via `MemberFormSupport::syncAddress()`; load via resource-specific `addressState()` helpers.
- Build display string via `buildAddressString()` pattern in `BranchFormSupport`.
- When adding new resources with addresses, **extract shared form fields** into a reusable component/trait rather than copy-pasting — goal is DRY across Member, Branch, and future forms.

### Slugs & codes

- Content slugs: `ContentFormSupport::generateSlug()`.
- Follow existing auto-generation patterns; don't invent new formats without asking.

## Media & File Upload (disk `r2`)

- Always use disk `r2` for CMS/banner media (not `public` for new features).
- **Staging pattern:** FileUpload `directory('temp/')` → on save, move from `temp/` to final folder (`contents/`, `banners/`, etc.) and create/update `Media` record.
- Reference implementations: `ContentFormSupport`, `PromotionBannerSupport`.
- Clean up orphaned `temp/` files and old `Media` records when replacing images.
- On `migrate:fresh --seed`, R2 bucket may be wiped — do not rely on orphaned files persisting locally.

## Testing

- Pest tests are **not required by default** — only write/update tests when the user explicitly requests them.
- When tests are requested, use `php artisan make:test --pest {Name}` and run `php artisan test --compact`.

## Reference Resources

Copy patterns from these when building new features:

| Feature type | Reference |
|---|---|
| Full CRUD + View + Infolist | `Members/MemberResource` |
| Branch + RelationManagers + Stats widget | `Branches/BranchResource` |
| CMS + R2 media + RichEditor | `Contents/ContentResource` |
| Custom Page + repeater | `Pages/PromotionBannerPage` |
| Read-only master data | `Provinces/ProvinceResource` |
| List-only with chart widgets | `Members/` widgets |

