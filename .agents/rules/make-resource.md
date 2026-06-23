---
trigger: manual
---

# SYSTEM RULES: Laravel 13 Code Generation

Project Context: "posinvenfin" (Mini-ERP System)

You are constrained to these strict architectural and translation rules whenever generating Laravel code from a Prisma schema.

## 1. Artisan First Approach

- ALWAYS prioritize the use of Artisan commands to scaffold files.
- You must output the exact, unified Artisan command (e.g., `php artisan make:model ModelName -m -f -s`) at the beginning of your response to scaffold the Model, Migration, Factory, and Seeder simultaneously.

## 2. General Constraints

- Always include `declare(strict_types=1);` at the top of every PHP file.
- Use strict typing for all method parameters and return types.
- Follow modern Laravel 13 syntax and conventions.

## 3. Database & Migrations

- **Primary Keys:** Translate Prisma `String @id @default(ulid())` to Laravel ULID: `$table->ulid('id')->primary();`.
- **Naming:** Read `@@map("table_name")` for the exact table name.
- **Foreign Keys:** Must use constrained foreign ULIDs (e.g., `$table->foreignUlid('client_id')->nullable()->constrained('clients')->nullOnDelete();`). Apply `cascadeOnDelete()` or `restrictOnDelete()` logically based on ERP constraints.
- **Timestamps:** Always include `$table->timestamps();`. Add `$table->softDeletes();` only if requested.

## 4. Eloquent Models

- **Traits:** Always use `Illuminate\Database\Eloquent\Concerns\HasUlids` for ULID primary keys.
- **Properties:** Set `protected $table = 'table_name';`. Only mass-assignable fields go into `$fillable`.
- **Casts:** Use Laravel 11+ method syntax: `protected function casts(): array { ... }`.
- **Relationships:** Define all `belongsTo`, `hasMany`, `hasOne` explicitly with strict return types (e.g., `public function client(): BelongsTo`).

## 5. Context-Aware Factories & Seeders

- **Business Logic Context:** You MUST analyze the table name and its role in an ERP (e.g., Inventory, Finance, Point of Sale).
- **Faker Generation:** Do not use generic fakers blindly.
  - If the table is `vendors`, fake company names, not human names.
  - If the table is `transactions`, fake realistic monetary amounts (e.g., `fake()->numberBetween(10000, 5000000)`), not small integers.
  - If generating serial numbers, use `fake()->bothify('SN-####-????')`.
- **Seeders:** Ensure seeders call the factory and create at least 10 realistic records.
