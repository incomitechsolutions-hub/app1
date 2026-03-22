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
            'heading' => 'Übersicht',
            'items' => [
                [
                    'label' => 'Dashboard',
                    'route' => 'admin.dashboard',
                ],
            ],
        ],
        [
            'heading' => 'Inhalt',
            'items' => [
                [
                    'label' => 'Kurse',
                    'route' => 'admin.course-catalog.courses.index',
                    'active' => 'admin.course-catalog.courses.*',
                ],
                [
                    'label' => 'Kategorien',
                    'route' => 'admin.taxonomy.categories.index',
                    'active' => 'admin.taxonomy.*',
                ],
                [
                    'label' => 'Standorte',
                    'route' => 'admin.locations.index',
                    'active' => 'admin.locations.*',
                ],
                [
                    'label' => 'Seiten',
                    'route' => 'admin.pages.index',
                    'active' => 'admin.pages.*',
                ],
                [
                    'label' => 'FAQs',
                    'route' => 'admin.faqs.index',
                    'active' => 'admin.faqs.*',
                ],
                [
                    'label' => 'Medien',
                    'route' => 'admin.media.index',
                    'active' => 'admin.media.*',
                ],
            ],
        ],
        [
            'heading' => 'SEO',
            'items' => [
                [
                    'label' => 'SEO',
                    'route' => 'admin.seo.index',
                    'active' => 'admin.seo.index',
                ],
                [
                    'label' => 'Weiterleitungen',
                    'route' => 'admin.seo.redirects.index',
                    'active' => 'admin.seo.redirects.*',
                ],
            ],
        ],
        [
            'heading' => 'Leads',
            'items' => [
                [
                    'label' => 'Anfragen',
                    'route' => 'admin.inquiries.index',
                    'active' => 'admin.inquiries.*',
                ],
            ],
        ],
        [
            'heading' => 'System',
            'items' => [
                [
                    'label' => 'Benutzer',
                    'route' => 'admin.identity.users.index',
                    'active' => 'admin.identity.*',
                ],
            ],
        ],
    ],
];
