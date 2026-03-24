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
                    'module' => 'inquiries',
                    'children' => [
                        [
                            'label' => 'Anfragen',
                            'route' => 'admin.inquiries.index',
                            'active' => 'admin.inquiries.*',
                            'module' => 'inquiries',
                        ],
                        [
                            'label' => 'Newsletter',
                            'route' => 'admin.inquiries.index',
                            'active' => 'admin.inquiries.*',
                            'module' => 'inquiries',
                        ],
                        [
                            'label' => 'SMTP-Einstellungen',
                            'route' => 'admin.seo.index',
                            'active' => 'admin.seo.index',
                            'module' => 'seo',
                        ],
                    ],
                ],
                [
                    'label' => 'Kategorien',
                    'route' => 'admin.taxonomy.categories.index',
                    'active' => 'admin.taxonomy.*',
                    'icon' => 'folder',
                    'module' => 'taxonomy',
                    'children' => [
                        [
                            'label' => 'Hauptkategorien',
                            'route' => 'admin.taxonomy.categories.main',
                            'active' => 'admin.taxonomy.categories.main',
                            'module' => 'taxonomy',
                        ],
                        [
                            'label' => 'Unterkategorien',
                            'route' => 'admin.taxonomy.categories.sub',
                            'active' => 'admin.taxonomy.categories.sub',
                            'module' => 'taxonomy',
                        ],
                    ],
                ],
                [
                    'label' => 'Kursverwaltung',
                    'route' => 'admin.course-catalog.courses.index',
                    'active' => 'admin.course-catalog.courses.*',
                    'icon' => 'document',
                    'module' => 'course_catalog',
                    'children' => [
                        [
                            'label' => 'Ubersicht',
                            'route' => 'admin.course-catalog.courses.index',
                            'active' => 'admin.course-catalog.courses.*',
                            'module' => 'course_catalog',
                        ],
                        [
                            'label' => 'Einstellungen',
                            'route' => 'admin.seo.index',
                            'active' => 'admin.seo.index',
                            'module' => 'seo',
                        ],
                    ],
                ],
                [
                    'label' => 'Seiten',
                    'route' => 'admin.pages.index',
                    'active' => 'admin.pages.*',
                    'icon' => 'page',
                    'module' => 'pages',
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
                    'module' => 'identity',
                    'children' => [
                        [
                            'label' => 'Benutzer',
                            'route' => 'admin.identity.users.index',
                            'active' => 'admin.identity.*',
                            'module' => 'identity',
                        ],
                        [
                            'label' => 'SEO',
                            'route' => 'admin.seo.index',
                            'active' => 'admin.seo.index',
                            'module' => 'seo',
                        ],
                        [
                            'label' => 'Header',
                            'route' => 'admin.pages.index',
                            'active' => 'admin.pages.*',
                            'module' => 'pages',
                        ],
                        [
                            'label' => 'Footer',
                            'route' => 'admin.pages.index',
                            'active' => 'admin.pages.*',
                            'module' => 'pages',
                        ],
                        [
                            'label' => 'Länder',
                            'route' => 'admin.locations.index',
                            'active' => 'admin.locations.*',
                            'module' => 'locations',
                        ],
                    ],
                ],
                [
                    'label' => 'Module',
                    'route' => 'admin.modules.index',
                    'active' => 'admin.modules.*',
                    'icon' => 'puzzle',
                ],
                [
                    'label' => 'Standorte',
                    'route' => 'admin.locations.index',
                    'active' => 'admin.locations.*',
                    'icon' => 'pin',
                    'module' => 'locations',
                ],
                [
                    'label' => 'FAQs',
                    'route' => 'admin.faqs.index',
                    'active' => 'admin.faqs.*',
                    'icon' => 'question',
                    'module' => 'faqs',
                ],
                [
                    'label' => 'Medien',
                    'route' => 'admin.media.index',
                    'active' => 'admin.media.*',
                    'icon' => 'image',
                    'module' => 'media',
                ],
                [
                    'label' => 'Weiterleitungen',
                    'route' => 'admin.seo.redirects.index',
                    'active' => 'admin.seo.redirects.*',
                    'icon' => 'arrow-path',
                    'module' => 'seo',
                ],
            ],
        ],
    ],
];
