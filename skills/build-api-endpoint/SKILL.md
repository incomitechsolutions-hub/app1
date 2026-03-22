# Skill: Build API Endpoint

## Purpose
Create a Laravel endpoint consistent with architecture, contracts, and validation rules.

## Steps
1. Read `api-design.md`, `data-model.md`, and related docs.
2. Define route, controller action, request, and response.
3. Validate payload with Form Request or equivalent.
4. Apply authorization.
5. Keep controller thin.
6. Put business logic in service/action classes where appropriate.
7. Return view or JSON consistently.
8. Document changed files.

## Rules
- no business logic in controllers
- no missing validation
- no overexposed internal fields
