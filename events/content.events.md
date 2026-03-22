# Content and Platform Events

## Purpose
Define domain events that may be used by Laravel listeners, jobs, and integrations.

## Suggested Events
- course.created
- course.updated
- course.published
- course.unpublished
- category.published
- location.published
- topic.published
- redirect.created
- redirect.updated
- inquiry.created
- inquiry.synced
- inquiry.sync_failed

## Consumers
- notification emails
- CRM sync jobs
- audit logging
- cache clearing
