# Skill: Create Migration

## Purpose
Create safe Laravel migrations for schema changes.

## Steps
1. Confirm schema change in `data-model.md`.
2. Assess backward compatibility.
3. Create migration with proper up/down logic.
4. Add constraints, indexes, and foreign keys.
5. consider backfill or nullable transition strategy.
6. Document impact.

## Rules
- no manual schema changes
- no destructive changes without plan
- align with documented model
