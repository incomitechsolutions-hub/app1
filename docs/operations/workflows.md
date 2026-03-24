# Workflows

## Purpose
Define the operational workflows for content creation, SEO publishing, lead handling, and future platform expansion.

## 1. Content Workflow
Draft -> Review -> SEO Review -> Published

### Draft
Content is being created or edited.

### Review
Editorial structure and completeness are checked.

### SEO Review
Metadata, canonical logic, internal linking, and page quality are checked.

### Published
Content becomes publicly available and eligible for sitemap output if indexable.

## 2. Course Publishing Workflow
1. Create course.
2. Add structured content.
3. Assign taxonomy.
4. Add FAQs and media.
5. Add or confirm SEO metadata.
6. Preview.
7. Publish.
8. Route becomes available.

## 3. Category / Location / Topic Workflow
1. Create entity.
2. Add unique content.
3. Add metadata.
4. Review quality.
5. Publish.
6. Ensure internal linking visibility.

## 4. Programmatic SEO Workflow
1. Identify valid entity combinations.
2. Decide whether combination deserves an indexable page.
3. Ensure unique content layer exists.
4. Validate canonical logic.
5. Publish only supported combinations.

## 5. Redirect Workflow
1. Detect path change or retired URL.
2. Create redirect.
3. Validate no loops/conflicts.
4. Activate redirect.
5. Ensure route consistency.

## 6. Inquiry Workflow
1. Visitor submits inquiry.
2. Laravel validates request.
3. Inquiry is stored.
4. Notification is sent.
5. Optional CRM sync job is dispatched.
6. Sync result is tracked.

## 7. Internal Linking Workflow
1. Editors and SEO managers maintain entity relationships.
2. Public templates expose related content based on these relationships.
3. Topic clusters reinforce authority through structured links.

## 8. Future Operations Workflow
Later extension:
Lead -> qualification -> proposal -> trainer assignment -> delivery -> feedback

## 9. Category CSV Import (Admin)

### Purpose
Bulk create or update categories from a CSV file (taxonomy module).

### Data written
- **`categories`**: name, slug, description, status (and parent resolution when mapped).
- **`category_translations`**: only when a matching row exists in **`locales`** for the selected import language (e.g. `de`). If you only see rows in `categories`, ensure migrations ran and **`LocaleSeeder`** (or equivalent) created the locale codes you use.

### Required deployment steps
1. Run migrations: `php artisan migrate`
2. Seed locales at least once: `php artisan db:seed --class=LocaleSeeder` (or full `DatabaseSeeder` as per your process)

### Multi-server / load-balanced environments
The import uses two steps:

1. **Preview** stores the uploaded file under **`storage/app/private/imports/categories`** (local disk) and stores preview metadata in the **application cache** (keyed by token).
2. **Execute** reads the same file path and cache entry.

If preview and execute hit **different PHP nodes**, the file may exist only on one server and the import fails or reads zero rows. Mitigations:

- **Shared storage**: mount the same `storage/app/private` (or whole `storage`) on all app servers, **or**
- **Sticky sessions** so preview and execute stay on the same node, **and**
- **Shared cache** (`CACHE_STORE=database`, `redis`, etc.) so the preview token resolves on every node.

Also ensure the **cache** table or Redis is available if using those drivers.

### Operational checks
- After import, use the on-screen **Import-Ergebnis** (totals, errors list).
- For “nothing written”, verify: delimiter/encoding, slug rules (ASCII slug), duplicate strategy (skip vs update), and Laravel log under `storage/logs/laravel.log`.
