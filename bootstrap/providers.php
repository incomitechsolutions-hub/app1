<?php

return [
    App\Providers\AppServiceProvider::class,

    App\Domain\CourseCatalog\CourseCatalogServiceProvider::class,
    App\Domain\Taxonomy\TaxonomyServiceProvider::class,
    App\Domain\PageManagement\PageManagementServiceProvider::class,
    App\Domain\Faq\FaqServiceProvider::class,
    App\Domain\Seo\SeoServiceProvider::class,
    App\Domain\Locations\LocationsServiceProvider::class,
    App\Domain\Media\MediaServiceProvider::class,
    App\Domain\Inquiries\InquiriesServiceProvider::class,
    App\Domain\Identity\IdentityServiceProvider::class,
    App\Domain\Localization\LocalizationServiceProvider::class,
    App\Domain\Ai\AiServiceProvider::class,
];
