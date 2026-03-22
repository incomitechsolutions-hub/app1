# User Flows

## Purpose
Define the primary flows for admin users and public users.

## Flow 1: Create Course
### Actor
Editor

### Steps
1. Open admin course form.
2. Enter title, slug, summary, status, and core fields.
3. Assign categories, audience, difficulty, tags.
4. Add modules, objectives, prerequisites, FAQs, and media.
5. Add SEO metadata or confirm fallback behavior.
6. Save as draft.
7. Preview route.
8. Publish when validation passes.

## Flow 2: Create Category Page
### Actor
Editor / SEO Manager

### Steps
1. Create category.
2. Define hierarchy and content.
3. Add SEO metadata.
4. Publish.
5. Category page becomes available.

## Flow 3: Create Location Page
### Actor
SEO Manager

### Steps
1. Create location.
2. Add location content and metadata.
3. Publish.
4. Location page becomes publicly available.

## Flow 4: Create Topic Page
### Actor
SEO Manager / Editor

### Steps
1. Create topic page.
2. Add long-form content and FAQs.
3. Link related courses and categories.
4. Publish.

## Flow 5: Manage Redirects
### Actor
SEO Manager / Admin

### Steps
1. Create redirect source and target.
2. Select redirect type.
3. Validate for conflicts and loops.
4. Activate redirect.

## Flow 6: Visitor Browses Public Page
### Actor
Visitor

### Steps
1. User lands on route.
2. Laravel resolves entity by slug.
3. Controller/service prepares page data.
4. Blade template renders content, metadata, schema, FAQs, and internal links.
5. User navigates further or submits inquiry.

## Flow 7: Submit Inquiry
### Actor
Visitor

### Steps
1. User fills inquiry form.
2. Laravel validates form.
3. Inquiry is stored.
4. Notification and optional CRM sync are triggered.
5. Confirmation is shown.

## Flow 8: Future Operations Extension
### Actor
Admin / Operations

### Steps
1. Enable trainer and schedule modules later.
2. Link trainers and sessions to courses.
3. Extend inquiry flow into delivery workflows.
