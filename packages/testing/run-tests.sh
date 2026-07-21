#!/usr/bin/env bash
# Run backoffice-filament then api-elysia tests; write report-testing.md.
# Continues on failure; exits non-zero if any suite failed.
set -u

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
REPORT="$ROOT/packages/testing/report-testing.md"
LOG_DIR="$(mktemp -d)"
trap 'rm -rf "$LOG_DIR"' EXIT

STARTED_AT="$(date '+%Y-%m-%d %H:%M:%S %z')"
OVERALL_EXIT=0

BACKOFFICE_EXIT=0
BACKOFFICE_MS=0
API_EXIT=0
API_MS=0

fmt_duration() {
  local ms="$1"
  if (( ms < 1000 )); then
    printf '%dms' "$ms"
  elif (( ms < 60000 )); then
    printf '%d.%ds' $((ms / 1000)) $(((ms % 1000) / 100))
  else
    printf '%dm %ds' $((ms / 60000)) $(((ms % 60000) / 1000))
  fi
}

status_label() {
  if [[ "$1" -eq 0 ]]; then echo PASS; else echo FAIL; fi
}

extract_failures() {
  local log="$1"
  {
    grep -E 'FAILED\s+' "$log" || true
    grep -E '^\s*(⨯|×|\(fail\))' "$log" || true
  } | sed 's/^[[:space:]]*//' | awk '!seen[$0]++' | head -n 40
}

extract_summary() {
  local log="$1"
  local line
  line="$(
    grep -E 'Tests:|Assertions:|pass|fail|Ran [0-9]+ tests' "$log" \
      | tail -n 5 \
      | tr '\n' ' ' \
      | sed 's/[[:space:]]\+/ /g; s/^[[:space:]]*//; s/[[:space:]]*$//'
  )"
  if [[ -z "$line" ]]; then
    echo "(no summary line parsed — see exit code)"
  else
    echo "$line"
  fi
}

md_escape_cell() {
  sed 's/|/\\|/g' <<<"$1"
}

run_suite() {
  local key="$1" label="$2" workdir="$3" cmd="$4"
  local log="$LOG_DIR/${key}.log"
  local start_s end_s exit_code duration_ms

  echo ""
  echo "==> [$label] $cmd"
  echo "    cwd: $workdir"
  start_s="$(date +%s)"

  (
    cd "$workdir" || exit 127
    eval "$cmd"
  ) >"$log" 2>&1
  exit_code=$?

  # Stream log to terminal after capture (keeps exit code clean)
  cat "$log"

  end_s="$(date +%s)"
  duration_ms=$(( (end_s - start_s) * 1000 ))

  case "$key" in
    backoffice)
      BACKOFFICE_EXIT="$exit_code"
      BACKOFFICE_MS="$duration_ms"
      ;;
    api)
      API_EXIT="$exit_code"
      API_MS="$duration_ms"
      ;;
  esac

  if [[ $exit_code -ne 0 ]]; then
    OVERALL_EXIT=1
    echo "    ✗ $label failed (exit $exit_code, $(fmt_duration "$duration_ms"))"
  else
    echo "    ✓ $label passed ($(fmt_duration "$duration_ms"))"
  fi
}

run_suite backoffice "backoffice-filament" \
  "$ROOT/apps/backoffice-filament" \
  'composer test'

run_suite api "api-elysia" \
  "$ROOT/apps/api-elysia" \
  'bun run test'

FINISHED_AT="$(date '+%Y-%m-%d %H:%M:%S %z')"
TOTAL_MS=$(( BACKOFFICE_MS + API_MS ))

BACKOFFICE_SUMMARY="$(extract_summary "$LOG_DIR/backoffice.log")"
API_SUMMARY="$(extract_summary "$LOG_DIR/api.log")"
BACKOFFICE_FAILURES="$(extract_failures "$LOG_DIR/backoffice.log")"
API_FAILURES="$(extract_failures "$LOG_DIR/api.log")"

write_failures_section() {
  local exit_code="$1" failures="$2"
  if [[ -n "$failures" ]]; then
    printf '%s\n' "$failures" | sed 's/^/- /'
  elif [[ "$exit_code" -eq 0 ]]; then
    echo "_None._"
  else
    echo "_No failure lines matched; suite exited non-zero. Re-run locally for full output._"
  fi
}

{
  cat <<EOF
# Testing Report

Generated: \`$STARTED_AT\` → \`$FINISHED_AT\`  
Total duration: **$(fmt_duration "$TOTAL_MS")**  
Overall: **$(status_label "$OVERALL_EXIT")**

| Suite | Status | Exit | Duration | Summary |
|-------|--------|------|----------|---------|
| backoffice-filament | $(status_label "$BACKOFFICE_EXIT") | \`$BACKOFFICE_EXIT\` | $(fmt_duration "$BACKOFFICE_MS") | $(md_escape_cell "$BACKOFFICE_SUMMARY") |
| api-elysia | $(status_label "$API_EXIT") | \`$API_EXIT\` | $(fmt_duration "$API_MS") | $(md_escape_cell "$API_SUMMARY") |

## backoffice-filament

- Command: \`composer test\`
- Working directory: \`apps/backoffice-filament\`
- Status: **$(status_label "$BACKOFFICE_EXIT")** (\`exit $BACKOFFICE_EXIT\`, $(fmt_duration "$BACKOFFICE_MS"))

### Failures

EOF
  write_failures_section "$BACKOFFICE_EXIT" "$BACKOFFICE_FAILURES"

  cat <<EOF

## api-elysia

- Command: \`bun run test\`
- Working directory: \`apps/api-elysia\`
- Status: **$(status_label "$API_EXIT")** (\`exit $API_EXIT\`, $(fmt_duration "$API_MS"))

### Failures

EOF
  write_failures_section "$API_EXIT" "$API_FAILURES"

  cat <<EOF

---

_Run via: \`packages/testing/run-tests.sh\`_
EOF
} > "$REPORT"

echo ""
echo "Report written: $REPORT"
echo "Overall: $(status_label "$OVERALL_EXIT")"
exit "$OVERALL_EXIT"
