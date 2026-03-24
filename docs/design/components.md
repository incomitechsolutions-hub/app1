\# Components



\## Purpose

Define reusable public UI components based on the reference implementation.



\## 1. Header

Reusable partial with:

\- logo

\- nav links

\- dropdowns

\- theme toggle

\- mobile menu toggle



\## 2. Mega Menu

Reusable taxonomy dropdown pattern:

\- trigger

\- panel

\- grid of links

\- footer CTA row



\## 3. Mobile Accordion Menu

Reusable mobile navigation component:

\- backdrop

\- side panel

\- accordion groups

\- nested items



\## 4. Hero Block

Reusable hero section with:

\- headline

\- lead text

\- primary CTA

\- secondary CTA



\## 5. Category Card

Fields:

\- image or icon

\- title

\- description

\- metadata

\- link target



Classes:

\- white/dark surface

\- rounded-xl

\- shadow-md

\- hover:shadow-lg



\## 6. Training Card

Fields:

\- title

\- teaser

\- duration

\- price

\- CTA link



\## 7. Stats Block

Reusable numbers + labels grid on strong colored background.



\## 8. CTA Block

Centered call-to-action block with one primary action.



\## 9. Footer Group

Reusable footer navigation section with:

\- heading

\- desktop list

\- mobile accordion



\## 10. Theme Toggle

Reusable button with:

\- dark icon

\- light icon

\- localStorage state

\- html class toggle



\## 11. Social Link Icon Button

Rounded icon button with:

\- hover background transition

\- accessible title/label



\## 12. Legal / Contact Mini Link Row

Used in lower footer strip for:

\- phone

\- email

\- legal micro-links



\## Design Rules

All UI implementation must follow the design documentation in `docs/design/\*`.



Do not invent new visual patterns if an existing pattern is documented.



Public UI must use:

\- the documented color system

\- the documented header and footer pattern

\- the documented mega menu and mobile navigation pattern

\- the documented section spacing and card styles

\- the documented dark mode behavior



Admin UI must use:

\- Blade

\- Tailwind

\- documented spacing, forms, tables, buttons, badges, and layout patterns



\## UI Rules

\- Tailwind CSS is the standard UI layer

\- no Bootstrap

\- Vite build is required in production

\- public frontend must prioritize crawlability, speed, and clean HTML structure

\- public layouts must include reusable header and footer partials

\- navigation should be data-driven from taxonomy/category structures where possible

