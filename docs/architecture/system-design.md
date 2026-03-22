# System Design

## Purpose
Describe the overall architecture, rendering strategy, module boundaries, and deployment model.

## Architecture Overview
The platform follows a Laravel monolith architecture:

- Application Framework: Laravel
- Database: MySQL / MariaDB
- Rendering: Blade templates
- UI Styling: Tailwind CSS
- File Storage: local disk or S3-compatible storage
- Admin Backend: custom-built inside Laravel

## High-Level Components

### Public Frontend
Responsibilities:
- route resolution
- page rendering
- SEO metadata rendering
- schema markup output
- inquiry forms
- internal linking presentation

### Admin Backend
Responsibilities:
- content CRUD
- workflow actions
- SEO management
- redirect management
- media management
- role-based access

### Application Layer
Responsibilities:
- business logic
- content assembly
- publishing validation
- preview support
- internal linking logic
- notifications and integration orchestration

### Database Layer
Responsibilities:
- relational storage
- SEO metadata
- public page relationships
- redirects
- taxonomy
- inquiries
- future operational entities

## Rendering Strategy
Use server-side rendering with Blade templates.

Recommended pattern:
- Laravel routes -> controllers -> services/view models -> Blade
- cache where needed
- clear separation between public and admin interfaces

## Public Page Types
- course
- category
- location
- course-location
- topic
- editorial page

## Routing Model
Examples:
- `/kurse/{slug}`
- `/kategorie/{slug}`
- `/standorte/{slug}`
- `/kurse/{courseSlug}/{locationSlug}`
- `/thema/{slug}`

## Content Rendering Flow
1. User requests URL.
2. Laravel route resolves controller.
3. Service loads entity and related content.
4. SEO metadata and schema are prepared.
5. Blade renders page.

## Programmatic SEO Flow
1. Structured entities exist in DB.
2. Valid combinations are resolved into routes.
3. Canonical logic determines preferred URL.
4. Internal linking reinforces topical clusters.

## Caching Strategy
- route/model caching where useful
- application cache for expensive lookups
- database indexing
- page fragment caching selectively

## Security Architecture
- Laravel auth
- role-based authorization
- CSRF protection
- validation on all form submissions
- environment-based secret management

## Deployment Model
Recommended:
- Linux server
- Apache or Nginx
- PHP-FPM
- MySQL
- Composer-based deployment
- queue worker later if needed

## Monitoring
Track:
- application errors
- failed publishes
- failed notifications
- failed inquiry submissions
- performance and crawl issues

## Evolution Path
Phase 1:
- SEO content platform

Phase 2:
- lead platform

Phase 3:
- training operations platform
