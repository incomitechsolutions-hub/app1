# Data Model

## Purpose
Define the relational data model for the Laravel-based SEO training platform.

## Modeling Principles
- normalized schema
- explicit relationships
- SEO-first structure
- migration-driven schema evolution
- future operational extensibility

## Core Entity Groups
1. Core Content
2. Taxonomy
3. Localization (locales, markets, per-entity translations)
4. Page Management
5. SEO Layer
6. Locations
7. Media
8. Leads / Inquiries
9. Future Operations

## Core Content

### courses
Fields:
- id
- title
- subtitle (nullable)
- slug
- external_course_code (nullable, unique business reference e.g. `KURS0001`)
- short_description
- long_description
- target_audience_text (nullable longtext)
- prerequisites_text (nullable longtext)
- duration_hours
- duration_days (nullable unsigned smallint)
- language_code
- currency_code (char 3, default EUR)
- status
- primary_category_id
- difficulty_level_id
- hero_media_asset_id
- price (decimal, nullable, net amount in `currency_code`)
- delivery_format (nullable enum: `online` | `presence` | `hybrid`)
- delivery_mode (nullable enum: `live_online` | `self_study` | `blended_learning`)
- lessons_count (nullable unsigned int)
- min_participants (nullable unsigned int)
- instructor_name (nullable)
- certificate_label (nullable)
- author_name (nullable)
- content_version (nullable string)
- is_featured (boolean, default false)
- booking_url (nullable URL)
- offer_url (nullable URL)
- ai_prompt_source (nullable longtext)
- internal_notes (nullable longtext)
- average_rating (decimal 3,2, default 0)
- ratings_count (unsigned int, default 0)
- media_icon_enabled, media_header_enabled, media_video_enabled, media_gallery_enabled (booleans, default false)
- created_at
- updated_at
- published_at
- deleted_at (soft deletes)

### course_translations
Per-locale titles and slugs for courses (aligned with default locale `de` on `courses` via sync).

Fields:
- id
- course_id
- locale_id
- title
- slug
- short_description
- long_description
- created_at
- updated_at

Unique: `(course_id, locale_id)`, `(locale_id, slug)` where applicable.

### course_modules
- id
- course_id
- title
- description
- duration_hours
- sort_order
- created_at
- updated_at

### learning_objectives
- id
- course_id
- objective_text
- sort_order
- created_at
- updated_at

### prerequisites
- id
- course_id
- prerequisite_text
- sort_order
- created_at
- updated_at

### course_catalog_global_settings
Singleton row (`id = 1`) for default course creation and commercial defaults in admin.

- id
- default_currency (ISO 4217)
- default_delivery_format (enum string)
- default_language_code
- default_min_participants
- tax_rate_percent
- early_bird_enabled, early_bird_days_before, early_bird_discount_percent
- group_discount_enabled, group_discount_layout (`layout_1` | `layout_2`)
- created_at, updated_at

### course_group_discount_tiers
Tiered group discounts linked to `course_catalog_global_settings`.

- id
- course_catalog_global_setting_id (FK)
- sort_order
- min_participants
- discount_percent
- created_at, updated_at

### course_coupons
Minimal coupon codes for future checkout (admin-managed).

- id
- code (unique)
- discount_percent
- is_active
- notes (nullable)
- created_at, updated_at

### ai_prompts
Reusable AI prompt templates (admin Prompt-Bibliothek), grouped by `use_case` (e.g. `course_creation`, `general`).

- id
- title
- slug (unique)
- use_case (string enum)
- body (long text)
- placeholder_definitions (nullable JSON) — optional structured metadata for placeholders (e.g. `name`, `label`, `required`); placeholders may also be inferred from `{{name}}` in `body`
- description (nullable)
- sort_order
- is_active
- created_at, updated_at

### ai_course_generation_sessions
Server-side workflow for AI-assisted course outline generation (no `courses` row until finalize).

- id
- user_id (FK → `users`)
- ai_prompt_id (nullable FK → `ai_prompts`)
- status — `draft` | `in_review` | `ready_to_finalize` | `completed` | `cancelled` | `expired`
- template_snapshot (nullable JSON) — copy of the selected prompt at session creation
- placeholder_input (JSON) — filled `{{placeholder}}` values
- brief (text) — Kursidee / Anforderungen
- interpolated_body (nullable longtext) — template body after placeholder substitution
- compiled_prompt (longtext) — assembled user message for the LLM
- full_prompt_audit (nullable longtext) — optional JSON/meta of what was sent (without API key)
- draft_payload (nullable JSON) — canonical course-shaped draft (aligned with StoreCourseRequest)
- confirmed_steps (nullable JSON) — optional per-tab confirmation flags
- last_regenerated_section (nullable string)
- resulting_course_id (nullable FK → `courses`) — set when finalized
- last_error (nullable text)
- expires_at (nullable)
- created_at, updated_at

### ai_course_generation_events
Audit log for AI generation workflow steps.

- id
- ai_course_generation_session_id (FK → `ai_course_generation_sessions`, cascade delete)
- user_id (nullable FK → `users`)
- type — e.g. `session_created`, `prompt_compiled`, `ai_request_started`, `ai_request_succeeded`, `ai_request_failed`, `draft_updated_manual`, `section_regenerated`, `step_confirmed`, `finalize_attempted`, `course_persisted`, `session_cancelled`
- meta (nullable JSON) — model, duration_ms, error, draft_payload_hash, section, etc.
- created_at, updated_at

## Taxonomy

### categories
- id
- name
- slug
- description
- parent_id
- sort_order (manual sibling order within the same parent; lower values first)
- status
- icon_media_asset_id (nullable FK → `media_assets`, optional category icon)
- header_media_asset_id (nullable FK → `media_assets`, optional header/hero image)
- created_at
- updated_at

### category_taxonomy_settings
Singleton-style settings row for taxonomy defaults (typically `id = 1`).

Fields:
- id
- default_new_category_status (`draft` | `published` | `archived`) — preselect in admin “Neue Kategorie”
- created_at
- updated_at

### category_translations
Per-locale names and slugs for categories (aligned with default locale `de` on `categories` via sync).

Fields:
- id
- category_id
- locale_id
- name
- slug
- description
- created_at
- updated_at

Unique: `(category_id, locale_id)`, `(locale_id, slug)` where applicable.

### tags
- id
- name
- slug
- created_at
- updated_at

### audiences
- id
- name
- slug
- description
- created_at
- updated_at

### difficulty_levels
- id
- code
- label
- sort_order
- created_at
- updated_at

### course_categories
- course_id
- category_id

### course_tags
- course_id
- tag_id

### course_audiences
- course_id
- audience_id

## Localization

### locales
Content languages (BCP 47 style codes, e.g. `de`, `en`).

Fields:
- id
- code
- name
- is_active
- sort_order
- created_at
- updated_at

### markets
Country / domain configuration (VAT, default locale, optional flag media).

Fields:
- id
- label
- country_code (nullable ISO 3166-1 alpha-2 where applicable)
- display_code
- domain
- vat_rate (decimal)
- is_active
- default_locale_id (FK → locales)
- flag_media_asset_id (nullable FK → media_assets)
- sort_order
- created_at
- updated_at

## Page Management

### pages
Generic editorial / landing page entity.

Fields:
- id
- title
- slug
- page_type
- entity_type
- entity_id
- status
- created_at
- updated_at
- published_at

### content_blocks
- id
- page_id
- block_type
- content_json
- sort_order
- created_at
- updated_at

### faqs
- id
- owner_type
- owner_id
- question
- answer
- sort_order
- is_schema_enabled
- created_at
- updated_at

## SEO Layer

### seo_meta
Polymorphic SEO metadata per content owner (`owner_type` / `owner_id`).

- id
- owner_type
- owner_id
- seo_title
- meta_description
- focus_keyword (nullable)
- tags_csv (nullable, comma-separated free tags for SEO)
- preview_image_url (nullable)
- landing_page_url (nullable)
- canonical_url
- robots_index
- robots_follow
- og_title
- og_description
- og_image_media_asset_id
- schema_json
- created_at
- updated_at

### redirects
- id
- source_path
- target_path
- redirect_type
- is_active
- created_at
- updated_at

### alternate_locales
- id
- owner_type
- owner_id
- locale_code
- target_url
- created_at
- updated_at

## Locations

### locations
- id
- city
- country
- slug
- latitude
- longitude
- intro_text
- status
- created_at
- updated_at

### location_translations
- id
- location_id
- locale_code
- title
- body_content
- created_at
- updated_at

### course_locations
- id
- course_id
- location_id
- unique_intro
- unique_body
- status
- created_at
- updated_at

## Media

### media_assets
- id
- disk
- file_name
- file_path
- mime_type
- alt_text
- created_at
- updated_at

## Leads / Inquiries

### inquiries
- id
- first_name
- last_name
- email
- company
- phone
- message
- consent_privacy
- consent_marketing
- source_url
- source_entity_type
- source_entity_id
- utm_json
- sync_status
- created_at
- updated_at

## Users / Admin

### users
- id
- name
- email
- password
- status
- created_at
- updated_at

### roles
- id
- name
- slug
- created_at
- updated_at

### role_user
- user_id
- role_id

## Key Constraints
- unique slugs for relevant entities
- unique redirects.source_path
- no circular category hierarchy
- foreign keys where relevant
- indexes on slugs, owner refs, and foreign keys
