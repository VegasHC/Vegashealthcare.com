<?php

use Statamic\Facades\Markdown;
use Statamic\Facades\Term;

return [

    /*
    |--------------------------------------------------------------------------
    | Default search index
    |--------------------------------------------------------------------------
    |
    | This option controls the search index that gets queried when performing
    | search functions without explicitly selecting another index.
    |
    */

    'default' => env('STATAMIC_DEFAULT_SEARCH_INDEX', 'default'),

    /*
    |--------------------------------------------------------------------------
    | Search Indexes
    |--------------------------------------------------------------------------
    |
    | Here you can define all of the available search indexes.
    |
    */

    'indexes' => [
        'default' => [
            'driver' => 'local',
            'searchables' => 'all',
            'fields' => ['title'],
        ],
        'providers' => [
            'driver' => 'algolia',
            'searchables' => 'collection:providers',
            'fields' => [
                '_geoloc', 'address', 'category', 'city', 'description', 'email', 'fax', 'first_name', 'gallery', 'id',
                'image', 'insurance_accepted', 'last_name', 'license_type', 'location', 'middle_name', 'org_name',
                'phone', 'promotion_level', 'service_category', 'services', 'sponsored', 'state', 'suffix_name', 'title',
                'test', 'video', 'video2', 'video3', 'video4', 'website', 'zip',
            ],
            'transformers' => [
                'services' => function ($services) {
                'description' => fn ($description) => Markdown::parse((string) $description),
                'insurance_accepted' => fn ($text) => Markdown::parse((string) $text),
                    if (empty($services)) {
                        return [];
                    }

                    $newServices = [];
                    foreach ($services as $key) {
                        $service = Term::findBySlug($key, 'services');
                        if ($service) {
                            $newServices[] = $service->get('title');
                        } else {
                            var_dump($key);
                        }
                    }

                    if (empty($newServices)) {
                        $newServices = $services;
                    }

                    return ['services' => $newServices];
                },
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Driver Defaults
    |--------------------------------------------------------------------------
    |
    | Here you can specify default configuration to be applied to all indexes
    | that use the corresponding driver. For instance, if you have two
    | indexes that use the "local" driver, both of them can have the
    | same base configuration. You may override for each index.
    |
    */

    'drivers' => [
        'local' => [
            'path' => storage_path('statamic/search'),
        ],
        'algolia' => [
            'credentials' => [
                'id' => env('ALGOLIA_APP_ID', ''),
                'secret' => env('ALGOLIA_SECRET', ''),
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Search Defaults
    |--------------------------------------------------------------------------
    |
    | Here you can specify default configuration to be applied to all indexes
    | regardless of the driver. You can override these per driver or per index.
    |
    */

    'defaults' => [
        'fields' => ['title'],
    ],
];
