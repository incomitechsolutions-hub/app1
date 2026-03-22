# AGENTS.md

## Purpose
This repository defines the implementation blueprint for a scalable, SEO-optimized training platform.

The platform combines:
- structured course catalog management
- editorial content management
- SEO landing page generation
- programmatic SEO
- lead generation
- future support for training operations

## Technology Baseline
The implementation must use:
- PHP 8.2+
- Laravel
- MySQL / MariaDB
- Blade for server-side rendering
- Tailwind CSS for UI
- Laravel migrations, seeders, validation, queues, mail, auth
- Git for source control

The implementation must NOT assume:
- Directus
- Next.js
- PostgreSQL
- Bootstrap
- a third-party CMS

## Product Approach
We are building our own CMS inside the Laravel application.

This means:
- our own admin backend
- our own content models
- our own SEO management
- our own publishing workflow
- our own page rendering logic
- our own internal linking logic

We are NOT building a general-purpose CMS.
We are building a domain-specific content and operations system for the training business.

## Source of Truth
Always consult these files before implementing:
1. `docs/product/prd.md`
2. `docs/product/business-rules.md`
3. `docs/product/user-flows.md`
4. `docs/architecture/system-design.md`
5. `docs/architecture/data-model.md`
6. `docs/architecture/api-design.md`
7. `docs/architecture/integrations.md`
8. `docs/operations/workflows.md`

## Mandatory Engineering Principles
- SEO-first architecture
- modular monolith
- structured entities over generic free-text chaos
- explicit relationships over hidden assumptions
- Blade-rendered public frontend
- Tailwind-based admin UI
- server-side validation is mandatory
- no business logic in controllers
- no direct database manipulation outside migrations
- no hidden schema changes without updating docs

## Product Scope
### Phase 1
- course catalog
- categories
- locations
- topic pages
- editorial pages
- SEO metadata
- redirects
- content blocks
- FAQ management
- internal linking
- media management

### Phase 2
- inquiry forms
- lead capture
- CRM integration
- notifications
- simple sales workflow support

### Phase 3
- training operations
- trainers
- schedules
- bookings
- participants
- certificates
- feedback

## Public URL Strategy
The system must support:
- `/kurse/{course-slug}`
- `/kategorie/{category-slug}`
- `/standorte/{location-slug}`
- `/kurse/{course-slug}/{location-slug}`
- `/thema/{topic-slug}`

URLs must be stable, human-readable, and derived from structured entities.

## UI Rules
- Tailwind CSS is the standard UI layer
- no Bootstrap
- admin UI must be workflow-oriented, fast, and clear
- public frontend must prioritize crawlability, speed, and clean HTML structure

## AI Usage Rules
AI may help with:
- code generation
- migration creation
- CRUD scaffolding
- content drafting
- SEO metadata drafting
- internal linking suggestions
- implementation planning

AI must not:
- publish content automatically
- invent unsupported business rules
- bypass validation or permissions
- create hidden schema changes
- modify production data directly

## Implementation Protocol
When implementing a feature:
1. identify affected entities
2. identify affected routes
3. identify SEO implications
4. design DB changes
5. design controller/service/view changes
6. add validation and permissions
7. update docs if structure changes
8. ensure workflow consistency

## Definition of Done
A feature is done only if:
- data model is respected
- migration exists if schema changed
- validation exists
- permissions exist
- routes and templates are consistent
- SEO impact is handled
- implementation is testable
