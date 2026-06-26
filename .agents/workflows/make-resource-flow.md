---
description: Prisma Schema to Laravel Ecosystem
---

# AGENT WORKFLOW: Prisma Schema to Laravel Ecosystem

When the user inputs a Prisma schema snippet, you MUST execute the task strictly following this sequential workflow. Do not skip any steps.

## STEP 1: Contextual & Domain Analysis

- Read the provided Prisma schema.
- Identify the core entity (e.g., Is this an accounting ledger, a stock movement, or a user profile?).
- Determine how this entity interacts with the rest of the "posinvenfin" Mini-ERP system.
- _Internal Check:_ Ask yourself, "What kind of real-world data belongs in this table?" Hold this context for Step 4.

## STEP 2: Schema Translation Mapping

- Map the Prisma data types to Laravel 13 Blueprint types.
- Identify primary keys (ULID), unique constraints, and nullable fields.
- Map out all one-to-many and many-to-many relationships.

## STEP 3: Planning & Artisan Command Execution

- Determine the correct model name (Singular, PascalCase).
- Provide the exact terminal command required to generate the foundational files.
- Example format to output: `> php artisan make:model {ModelName} -m -f -s`
- This ensures exactly 4 isolated files are planned: Migration, Model, Factory, and Seeder.

## STEP 4: Execution & Generation

- Generate the final customized code for all 4 files based on the `make-resource.md` provided to you.
- **Crucial:** Inject your domain understanding from Step 1 into the Factory and Seeder files to ensure the mock data is highly relevant to an ERP system.
- Print the complete code blocks. Put the exact file path as a comment at the very top of each code block (e.g., `// app/Models/CategoryAsset.php`).

## STEP 5: Self-Review

- Verify that strict typing `declare(strict_types=1);` is present.
- Verify that ULID is used correctly across Migrations and Models.
- Ensure no extra files (like resources or controllers) were generated.
- Output the code cleanly without unnecessary conversational filler. Let the code speak for itself.
