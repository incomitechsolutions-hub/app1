# Domain modules (modular monolith)

Each bounded context lives under `app/Domain/{Name}` and ships its own:

- `{Name}ServiceProvider.php` — registers `routes/public.php` and `routes/admin.php`
- `Models/`, `Services/`, `Policies/`
- `Http/Controllers/Admin|Public/`, `Http/Requests/Admin/`

Shared infrastructure:

- `Shared/Providers/DomainModuleServiceProvider` — base provider that loads route files when present
- `Shared/Contracts/`, `Shared/Models/Concerns/` — cross-module contracts and model traits

## Map (docs/architecture/data-model.md)

| Module | Responsibility |
|--------|------------------|
| `CourseCatalog` | courses, course_modules, learning_objectives, prerequisites |
| `Taxonomy` | categories, tags, audiences, difficulty_levels + pivots |
| `PageManagement` | pages, content_blocks (editorial, topic, etc.) |
| `Faq` | polymorphic faqs |
| `Seo` | seo_meta, redirects, alternate_locales; sitemap route |
| `Locations` | locations, location_translations, course_locations |
| `Media` | media_assets |
| `Inquiries` | inquiries (`POST /anfrage`) |
| `Identity` | users, roles (admin; login scaffolding added later) |

Public URL prefixes are stubbed with `501` responses until controllers and services exist. Replace closures with thin controllers that delegate to module `Services/`.
