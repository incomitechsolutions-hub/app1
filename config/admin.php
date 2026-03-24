<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Admin navigation (sidebar)
    |--------------------------------------------------------------------------
    |
    | "route" must match a named Laravel route. "active" is optional; if omitted,
    | only the exact route name is highlighted. Use patterns like "admin.foo.*"
    | for section-wide highlighting.
    |
    */
    'navigation' => [
        [
            'heading' => 'MENU',
            'items' => [
                [
                    'label' => 'Dashboard',
                    'route' => 'admin.dashboard',
                    'icon' => 'dashboard',
                ],
                [
                    'label' => 'Kontakte',
                    'route' => 'admin.inquiries.index',
                    'active' => 'admin.inquiries.*',
                    'icon' => 'inbox',
                    'children' => [
                        [
                            'label' => 'Anfragen',
                            'route' => 'admin.inquiries.index',
                            'active' => 'admin.inquiries.*',
                        ],
                        [
                            'label' => 'Newsletter',
                            'route' => 'admin.inquiries.index',
                            'active' => 'admin.inquiries.*',
                        ],
                        [
                            'label' => 'SMTP-Einstellungen',
                            'route' => 'admin.seo.index',
                            'active' => 'admin.seo.index',
                        ],
                    ],
                ],
                [
                    'label' => 'Kategorien',
                    'route' => 'admin.taxonomy.categories.index',
                    'active' => 'admin.taxonomy.*',
                    'icon' => 'folder',
                    'children' => [
                        [
                            'label' => 'Hauptkategorien',
                            'route' => 'admin.taxonomy.categories.index',
                            'active' => 'admin.taxonomy.*',
                        ],
                        [
                            'label' => 'Unterkategorien',
                            'route' => 'admin.taxonomy.categories.index',
                            'active' => 'admin.taxonomy.*',
                        ],
                    ],
                ],
                [
                    'label' => 'Kurse',
                    'route' => 'admin.course-catalog.courses.index',
                    'active' => 'admin.course-catalog.courses.*',
                    'icon' => 'document',
                    'children' => [
                        [
                            'label' => 'Alle Kurse',
                            'route' => 'admin.course-catalog.courses.index',
                            'active' => 'admin.course-catalog.courses.*',
                        ],
                        [
                            'label' => 'Einstellungen',
                            'route' => 'admin.seo.index',
                            'active' => 'admin.seo.index',
                        ],
                    ],
                ],
                [
                    'label' => 'Seiten',
                    'route' => 'admin.pages.index',
                    'active' => 'admin.pages.*',
                    'icon' => 'page',
                ],
            ],
        ],
        [
            'heading' => 'SYSTEM',
            'items' => [
                [
                    'label' => 'Einstellungen',
                    'route' => 'admin.identity.users.index',
                    'active' => 'admin.identity.*',
                    'icon' => 'sliders',
                    'children' => [
                        [
                            'label' => 'Benutzer',
                            'route' => 'admin.identity.users.index',
                            'active' => 'admin.identity.*',
                        ],
                        [
                            'label' => 'SEO',
                            'route' => 'admin.seo.index',
                            'active' => 'admin.seo.index',
                        ],
                        [
                            'label' => 'Header',
                            'route' => 'admin.pages.index',
                            'active' => 'admin.pages.*',
                        ],
                        [
                            'label' => 'Footer',
                            'route' => 'admin.pages.index',
                            'active' => 'admin.pages.*',
                        ],
                        [
                            'label' => 'Länder',
                            'route' => 'admin.locations.index',
                            'active' => 'admin.locations.*',
                        ],
                    ],
                ],
                [
                    'label' => 'Extensions',
                    'route' => 'admin.seo.index',
                    'active' => 'admin.seo.*',
                    'icon' => 'puzzle',
                    'children' => [
                        [
                            'label' => 'Manager',
                            'route' => 'admin.media.index',
                            'active' => 'admin.media.*',
                        ],
                    ],
                ],
                [
                    'label' => 'Standorte',
                    'route' => 'admin.locations.index',
                    'active' => 'admin.locations.*',
                    'icon' => 'pin',
                ],
                [
                    'label' => 'FAQs',
                    'route' => 'admin.faqs.index',
                    'active' => 'admin.faqs.*',
                    'icon' => 'question',
                ],
                [
                    'label' => 'Medien',
                    'route' => 'admin.media.index',
                    'active' => 'admin.media.*',
                    'icon' => 'image',
                ],
                [
                    'label' => 'Weiterleitungen',
                    'route' => 'admin.seo.redirects.index',
                    'active' => 'admin.seo.redirects.*',
                    'icon' => 'arrow-path',
                ],
            ],
        ],
    ],
];
