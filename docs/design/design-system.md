\# Design System



\## Purpose

Define the public-facing visual system for the Laravel + Blade + Tailwind implementation.



This design system is based on the current Sbase / Course ITS reference implementation and must be treated as the baseline visual language for the platform.



\## Core Visual Direction

The design should feel:

\- professional

\- modern

\- trustworthy

\- readable

\- conversion-oriented

\- suitable for B2B training and education



It should not feel:

\- overly playful

\- startup-gimmicky

\- overly minimal to the point of looking empty

\- enterprise-grey and lifeless



\## Technology Rules

\- Tailwind CSS is the styling standard

\- Vite is used for asset bundling in Laravel

\- Alpine.js is used for lightweight interactions

\- no Bootstrap

\- no jQuery

\- avoid large JS frameworks for simple navigation behavior



\## Color System



\### Primary Brand Color

Base:

\- `primary`: `#0086E6`



Scale:

\- `primary-50`: `#f2f9fe`

\- `primary-100`: `#e6f3fd`

\- `primary-200`: `#b3dbf8`

\- `primary-300`: `#80c3f3`

\- `primary-400`: `#4daaee`

\- `primary-500`: `#0086e6`

\- `primary-600`: `#0072c4`

\- `primary-700`: `#005ea1`

\- `primary-800`: `#004373`

\- `primary-900`: `#002845`



\### Secondary Brand Color

Base:

\- `secondary`: `#9BD000`



Scale:

\- `secondary-50`: `#fafdf2`

\- `secondary-100`: `#f5fae6`

\- `secondary-200`: `#e1f1b3`

\- `secondary-300`: `#cde880`

\- `secondary-400`: `#b9de4d`

\- `secondary-500`: `#9bd000`

\- `secondary-600`: `#84b100`

\- `secondary-700`: `#6d9200`

\- `secondary-800`: `#4e6800`

\- `secondary-900`: `#2f3e00`



\### Neutral Palette

Use Tailwind slate / gray style neutrals:

\- backgrounds: `bg-gray-50`, `bg-white`, `bg-gray-100`

\- dark mode surfaces: `bg-gray-800`, `bg-gray-900`

\- text: `text-gray-900`, `text-gray-600`, `text-gray-500`

\- borders: `border-gray-200`, `border-gray-700`



\## Typography

\- clean sans-serif

\- strong hierarchy

\- high readability

\- no decorative fonts



\### Public Headings

\- H1: `text-4xl md:text-5xl font-bold`

\- H2: `text-3xl font-bold`

\- H3: `text-xl font-bold`

\- Card title: `text-lg font-bold`



\### Body Text

\- lead text: `text-xl`

\- default text: `text-base`

\- small muted text: `text-sm text-gray-500`



\## Layout Rules

\### Container Width

Default public container:

\- `max-w-7xl mx-auto px-4 sm:px-6 lg:px-8`



\### Section Spacing

\- hero: `py-20`

\- standard sections: `py-16`

\- footer: `py-12`



\### Grid Usage

\- category cards: `grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8`

\- stats: `grid grid-cols-2 md:grid-cols-4 gap-8`

\- footer columns: `grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2 lg:gap-8`



\## Surfaces and Cards

\### Card Pattern

For category and training cards use:

\- `bg-white dark:bg-gray-800`

\- `rounded-xl`

\- `shadow-md`

\- hover to `shadow-lg`

\- structured padding `p-6`



\## Buttons

\### Primary Button

\- `bg-primary-600 text-white`

\- hover `bg-primary-700`

\- `px-6 py-3`

\- `rounded-lg`

\- `font-semibold`



\### Secondary Inverted Hero Button

\- `bg-white text-primary-600`

\- hover `bg-gray-100`



\### Outline Button

\- `border-2 border-white`

\- `text-white`

\- hover switches to white background and primary text



\## Radius and Shadow

\- cards: `rounded-xl`

\- buttons: `rounded-lg`

\- menus / mega menus: `rounded-2xl`

\- use `shadow-md`, `shadow-lg`, `shadow-xl` selectively



\## Dark Mode

Dark mode is supported.



\### Rules

\- use `dark:` variants consistently

\- theme toggle must persist in `localStorage`

\- do not create separate templates for dark mode

\- use class-based dark mode



\## Accessibility

\- navigation must have visible hover/focus states

\- all interactive icons need accessible labels

\- dropdowns and toggles need semantic buttons

\- sufficient contrast must be preserved in light and dark mode



\## SEO / HTML Quality Rules

\- semantic header, nav, main, section, footer structure

\- no JS-dependent rendering for core content

\- headings must remain crawlable

\- category and course cards should contain plain anchor links

