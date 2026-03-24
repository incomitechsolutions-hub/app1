<?php

return [
    'brand' => [
        'name' => 'Course ITS',
        'tagline' => 'Wir entwickeln Weiterbildung, die Unternehmen und Teams messbar voranbringt.',
        'cta' => [
            'label' => 'Beratung anfragen',
            'href' => '/kontakt',
        ],
    ],
    'simple_links' => [
        [
            'label' => 'Alle Kategorien',
            'href' => '/kategorie',
        ],
        [
            'label' => 'Kontakt',
            'href' => '/kontakt',
        ],
    ],
    'mega_menus' => [
        [
            'label' => 'Kurse',
            'sections' => [
                [
                    'title' => 'IT-Trainings',
                    'links' => [
                        ['label' => 'Microsoft', 'href' => '/thema/microsoft', 'description' => 'Zertifizierungen und Praxisworkshops'],
                        ['label' => 'Cloud & DevOps', 'href' => '/thema/cloud-devops', 'description' => 'Azure, AWS und Automatisierung'],
                        ['label' => 'Cyber Security', 'href' => '/thema/cyber-security', 'description' => 'Schutz von Infrastruktur und Daten'],
                    ],
                ],
                [
                    'title' => 'Business & Management',
                    'links' => [
                        ['label' => 'Projektmanagement', 'href' => '/thema/projektmanagement', 'description' => 'Agile und klassische Methoden'],
                        ['label' => 'Leadership', 'href' => '/thema/leadership', 'description' => 'Führung, Kommunikation, Strategie'],
                        ['label' => 'Compliance', 'href' => '/thema/compliance', 'description' => 'Regulatorik sicher umsetzen'],
                    ],
                ],
            ],
            'view_all' => [
                'label' => 'Alle Kurse ansehen',
                'href' => '/kurse',
            ],
        ],
        [
            'label' => 'Standorte',
            'sections' => [
                [
                    'title' => 'Präsenz',
                    'links' => [
                        ['label' => 'Berlin', 'href' => '/standorte/berlin', 'description' => 'Trainingszentrum Berlin'],
                        ['label' => 'Hamburg', 'href' => '/standorte/hamburg', 'description' => 'Trainingszentrum Hamburg'],
                        ['label' => 'München', 'href' => '/standorte/muenchen', 'description' => 'Trainingszentrum München'],
                    ],
                ],
                [
                    'title' => 'Formate',
                    'links' => [
                        ['label' => 'Live Online', 'href' => '/standorte/live-online', 'description' => 'Virtuelle Trainer-Session'],
                        ['label' => 'Inhouse', 'href' => '/standorte/inhouse', 'description' => 'Bei Ihnen vor Ort'],
                        ['label' => 'Hybrid', 'href' => '/standorte/hybrid', 'description' => 'Präsenz plus Online'],
                    ],
                ],
            ],
            'view_all' => [
                'label' => 'Alle Standorte ansehen',
                'href' => '/standorte',
            ],
        ],
    ],
    'footer_groups' => [
        [
            'title' => 'Lösungen',
            'links' => [
                ['label' => 'Kurskatalog', 'href' => '/kurse'],
                ['label' => 'Kategorien', 'href' => '/kategorie'],
                ['label' => 'Themenwelten', 'href' => '/thema'],
                ['label' => 'Inhouse-Trainings', 'href' => '/standorte/inhouse'],
            ],
        ],
        [
            'title' => 'Unternehmen',
            'links' => [
                ['label' => 'Über uns', 'href' => '/ueber-uns'],
                ['label' => 'Karriere', 'href' => '/karriere'],
                ['label' => 'Kontakt', 'href' => '/kontakt'],
            ],
        ],
        [
            'title' => 'Rechtliches',
            'links' => [
                ['label' => 'Impressum', 'href' => '/impressum'],
                ['label' => 'Datenschutz', 'href' => '/datenschutz'],
                ['label' => 'AGB', 'href' => '/agb'],
            ],
        ],
    ],
    'social_links' => [
        ['label' => 'LinkedIn', 'href' => 'https://www.linkedin.com'],
        ['label' => 'YouTube', 'href' => 'https://www.youtube.com'],
        ['label' => 'Xing', 'href' => 'https://www.xing.com'],
    ],
    'contact' => [
        'phone' => '+49 40 1234567',
        'phone_href' => 'tel:+49401234567',
        'email' => 'info@course-its.example',
        'email_href' => 'mailto:info@course-its.example',
    ],
    'legal_links' => [
        ['label' => 'Impressum', 'href' => '/impressum'],
        ['label' => 'Datenschutz', 'href' => '/datenschutz'],
        ['label' => 'Cookies', 'href' => '/cookies'],
    ],
];
