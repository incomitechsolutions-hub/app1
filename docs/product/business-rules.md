# Business Rules

## Purpose
This document defines the mandatory business rules of the platform.

These rules must be enforced in Laravel application logic.

## Core Platform Rules
1. Public pages must be derived from structured entities.
2. Published content must resolve to a valid route.
3. Draft content must not be publicly visible.
4. SEO metadata must exist directly or through fallback logic before publication.
5. Redirect and canonical logic must remain consistent.
6. Admin workflows must be permission-aware.

## Publishing Rules
- Only publishable entities may appear publicly.
- Publication requires required fields and slug validation.
- Slug conflicts must block publication.
- Publicly visible relations must be valid.
- Publish actions should be auditable.

## URL Rules
- Slugs must be stable and human-readable.
- Slugs must be unique within entity scope.
- Path changes must trigger redirect review.
- Canonical must point to the preferred public URL.
- Duplicate variants must be avoided or explicitly canonicalized.

## SEO Rules
- Every indexable page must support meta title and meta description.
- Noindex pages must be excluded from sitemap output.
- Structured data must reflect actual page content.
- Programmatic pages require sufficient unique value.
- Internal linking must be entity-driven, not manually hardcoded everywhere.

## Content Quality Rules
- Course pages require meaningful structured content.
- Topic pages require unique explanatory content.
- FAQs must belong to a relevant entity.
- AI-generated content requires editorial review before publication.

## Taxonomy Rules
- Courses must belong to at least one category.
- Category hierarchies must not be circular.
- Tags support discovery but do not replace core taxonomy.
- Audience and difficulty are structured values.

## Lead Rules
- Every inquiry stores source URL and timestamp.
- Consent must be stored where required.
- Inquiry storage must succeed even if downstream integrations fail.

## Permission Rules
- Editors can create and edit draft content.
- SEO Managers can manage SEO fields and redirects.
- Administrators can manage users, roles, settings, and integrations.
- Publication rights should be restricted.

## Data Integrity Rules
- Migrations are mandatory for schema changes.
- Foreign keys must be enforced where relevant.
- Hard deletes of business-critical entities should be avoided.
- Public routing changes must be traceable.
