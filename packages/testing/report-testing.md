# Testing Report

Generated: `2026-07-21 15:45:02 +0700` → `2026-07-21 15:50:11 +0700`  
Total duration: **5m 9s**  
Overall: **FAIL**

| Suite | Status | Exit | Duration | Summary |
|-------|--------|------|----------|---------|
| backoffice-filament | FAIL | `1` | 5m 3s | (no summary line parsed — see exit code) |
| api-elysia | FAIL | `1` | 6.0s | 125 \| await prisma.$executeRawUnsafe(\`ALTER USER prisma WITH PASSWORD '\${password}'\`) 125 \| await prisma.$executeRawUnsafe(\`ALTER USER prisma WITH PASSWORD '\${password}'\`) 0 pass 21 fail Ran 21 tests across 21 files. [5.15s] |

## backoffice-filament

- Command: `composer test`
- Working directory: `apps/backoffice-filament`
- Status: **FAIL** (`exit 1`, 5m 3s)

### Failures

_No failure lines matched; suite exited non-zero. Re-run locally for full output._

## api-elysia

- Command: `bun run test`
- Working directory: `apps/api-elysia`
- Status: **FAIL** (`exit 1`, 6.0s)

### Failures

_No failure lines matched; suite exited non-zero. Re-run locally for full output._

---

_Run via: `packages/testing/run-tests.sh`_
