# API Design

## Purpose
Define the Laravel application interfaces between public routes, admin routes, form handling, and optional future APIs.

## Principles
- web-first architecture
- server-rendered frontend
- admin routes for CMS actions
- optional JSON endpoints where needed
- validation via Laravel Form Requests or equivalent
- authorization via policies/gates/middleware

## Public Web Routes
Examples:
- `GET /kurse/{slug}`
- `GET /kategorie/{slug}`
- `GET /standorte/{slug}`
- `GET /kurse/{courseSlug}/{locationSlug}`
- `GET /thema/{slug}`

## Inquiry Routes

### POST `/anfrage`
Purpose:
capture public inquiry submissions.

Fields:
- first_name
- last_name
- email
- company (optional)
- phone (optional)
- message
- consent_privacy
- consent_marketing (optional)
- source_url
- source_entity_type (optional)
- source_entity_id (optional)

Behavior:
- validate input
- store inquiry
- trigger notification
- optionally dispatch CRM sync job later
- redirect or return success response

## Admin Routes
Examples:
- `/admin/courses`
- `/admin/categories`
- `/admin/pages`
- `/admin/locations`
- `/admin/faqs`
- `/admin/seo`
- `/admin/redirects`
- `/admin/media`
- `/admin/inquiries`
- `/admin/users`

## Admin Behavior
- protected by auth middleware
- role-based authorization
- validation on create/update
- publish/unpublish actions are explicit

## Optional JSON Endpoints
Use only where needed for:
- slug availability checks
- async media handling
- preview support
- admin dynamic widgets

## Response Strategy
- web routes return Blade views
- JSON endpoints return consistent JSON structures
- validation errors use Laravel standard validation flow

## Security
- CSRF on forms
- auth middleware for admin
- authorization policies for actions
- rate limiting on public forms where appropriate

## Route Design Rule
Do not build the platform as an API-first SPA unless there is a strong later need.
The current default is Laravel-rendered web application.
