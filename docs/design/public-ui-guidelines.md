\# Public UI Guidelines



\## Purpose

Define the structure and behavior of the public-facing pages based on the current reference implementation.



\## Global Page Structure

Default page composition:

1\. Header

2\. Optional hero

3\. Main content sections

4\. CTA section

5\. Footer



\## Header Rules

The header is:

\- sticky

\- on top

\- white in light mode

\- dark gray in dark mode

\- shadowed

\- full-width

\- responsive



Use:

\- logo left

\- desktop navigation center/right

\- theme toggle

\- mobile menu button



Header classes baseline:

\- `bg-white dark:bg-gray-800 shadow-md sticky top-0 z-50`



\### Desktop Navigation

Use top-level navigation items with:

\- plain links for simple entries

\- mega menu dropdowns for taxonomy-heavy entries



Navigation link style:

\- `text-sm font-semibold text-gray-900 dark:text-white hover:text-primary-600 transition-colors`



\### Header Behavior

\- desktop dropdowns open on hover

\- mobile menu opens as off-canvas / side panel

\- navigation should be taxonomy-driven where possible

\- "Alle Kategorien" and "Kontakt" are standard utility links



\## Mega Menu Pattern

Use mega menus for main taxonomy groups.



\### Structure

\- trigger button with label + chevron

\- large dropdown panel

\- white/dark surface

\- 2-column grid for children

\- footer row with "Alle ... ansehen" link



\### Visuals

\- `absolute left-0 top-full mt-1 w-screen max-w-4xl`

\- `rounded-2xl bg-white dark:bg-gray-800 shadow-xl`

\- inner grid: `p-6 grid grid-cols-2 gap-6`



\## Mobile Navigation Pattern

The mobile menu must:

\- open from the right

\- use backdrop overlay

\- support nested accordions

\- preserve the same information hierarchy as desktop



\### Structure

\- fixed inset backdrop

\- fixed side panel

\- close button

\- accordion groups for large taxonomies

\- nested accordions for deeper nodes



\### Interaction

\- Alpine.js for `open`, `subopen`, `mobileMenuOpen`

\- `x-cloak` must be used to avoid flash

\- use transitions for menu open/close



\## Theme Toggle

The public layout includes a dark/light toggle.



\### Rules

\- toggle state stored in `localStorage`

\- class toggled on `document.documentElement`

\- icon changes between light and dark modes



\## Hero Section Pattern

Used on homepage and major landing pages.



\### Structure

\- strong background, often primary gradient

\- centered headline

\- supporting description

\- 1-2 CTA buttons



\### Baseline classes

\- `bg-gradient-to-r from-primary-700 to-primary-900 text-white py-20`

\- heading: `text-4xl md:text-5xl font-bold`

\- lead: `text-xl mb-8 opacity-90`



\## Category Section Pattern

Used to show top-level taxonomy groups.



\### Card Structure

Each category card contains:

\- icon/image block

\- title

\- short description

\- metadata row with child counts and training counts



\## Featured Trainings Section

Used for highlighted offers or curated courses.



\### Card Content

\- title

\- teaser text

\- duration

\- price or commercial info

\- CTA button



\## Stats Section

Used for social proof and platform scale.

\- strong primary background

\- centered large numbers

\- short labels



\## CTA Section

Used before footer.

\- centered headline

\- supporting text

\- clear CTA button



\## Footer Rules

The footer is not minimal.

It is a structured trust and navigation block.



Must include:

\- brand/logo area

\- short company statement

\- social links

\- CTA button

\- multiple navigation groups

\- legal links

\- contact details



Footer classes baseline:

\- `bg-primary-900 text-white py-12`



\### Desktop Footer

Show multi-column link groups.



\### Mobile Footer

Use accordion pattern per group.



\### Footer Navigation Groups

Typical groups:

\- Lösungen

\- Unternehmen

\- Rechtliches



\## Section Ordering Guidance

Homepage reference pattern:

1\. Hero

2\. Category grid

3\. Featured trainings

4\. Stats

5\. CTA

6\. Footer

