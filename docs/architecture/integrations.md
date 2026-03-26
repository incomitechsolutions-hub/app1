# Integrations

## Purpose
Define external systems the Laravel platform may connect to.

## Email
Used for:
- inquiry notifications
- autoresponders
- system alerts

Options:
- SMTP
- SendGrid
- AWS SES

Laravel mail should be used as the abstraction layer.

## CRM
Phase 2.

Candidate systems:
- HubSpot
- Salesforce

Use cases:
- sync inquiries
- attach source page and attribution
- support commercial follow-up

Recommended approach:
- queue-driven sync jobs
- retry-safe logic
- persistent sync status

## Analytics / Marketing
Potential:
- Google Analytics
- Google Tag Manager
- campaign attribution parameters

Requirements:
- capture source data
- support consent-aware tracking

## File Storage
Options:
- local disk
- S3-compatible storage

Use cases:
- media assets
- downloadable resources
- future document workflows

## Search
Optional later:
- Meilisearch
- Algolia

Use cases:
- site search
- filtered discovery

## AI
Used for:
- content drafting
- metadata drafting
- FAQ suggestions
- internal linking suggestions
- development support

Rules:
- no autonomous publication
- human review required
- prompts/versioning live in repository

## SEO — Keyword suggestions (admin, KI-Kursgenerator)
Used for:
- optional keyword research before the OpenAI course draft is generated

Implementation notes:
- Google Suggest (`suggestqueries.google.com`, `client=firefox`) via Laravel HTTP client with a short timeout; failures yield an empty suggestion list and scoring fallbacks apply
- entry point: `App\Domain\Seo\Services\GoogleSuggestService` and `KeywordResearchOrchestrator`; admin route `POST /admin/courses/ai-generation/keyword-research` (session auth, JSON)

## Queues / Jobs
Later recommended for:
- CRM sync
- notifications
- sitemap regeneration
- media processing
