# Workflow Test Matrix

## Publishing
- cannot publish without required fields
- cannot publish with duplicate slug
- noindex pages excluded from sitemap
- preview does not expose draft publicly

## Redirects
- reject redirect loops
- reject duplicate source path
- path changes trigger redirect review

## Inquiries
- invalid input rejected
- consent enforced
- inquiry stored even if integrations fail

## Rendering
- course route resolves by slug
- category route resolves by slug
- topic route renders metadata and schema
