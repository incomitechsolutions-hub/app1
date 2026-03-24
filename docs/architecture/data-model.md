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
- slug
- short_description
- long_description
- duration_hours
- language_code
- status
- primary_category_id
- difficulty_level_id
- hero_media_asset_id
- created_at
- updated_at
- published_at

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
