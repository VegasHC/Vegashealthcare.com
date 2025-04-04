<?php

use Statamic\Facades\Markdown;
use Statamic\Facades\Term;
use Statamic\Support\Arr;

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
                'phone', 'priority', 'promotion_level', 'service_category', 'services', 'sponsored', 'state', 'suffix_name',
                'title', 'test', 'video', 'video2', 'video3', 'video4', 'website', 'zip',
            ],
            'transformers' => [
                'description' => fn ($description) => Markdown::parse((string) $description),
                '_geoloc' => fn ($geoloc) => $geoloc ?: ['lat' => null, 'lon' => null],
                'insurance_accepted' => fn ($text) => Markdown::parse((string) $text),
                'promotion_level' => function ($level, $searchable) {
                    $sponsoredPriority = boolval(Arr::get($searchable, 'sponsored')) ? 5 : 0;

                    return [
                        'priority' => intval($level) + $sponsoredPriority,
                        'promotion_level' => $level,
                    ];
                },
                'services' => function (?array $services = []) {
                    if (empty($services)) {
                        return [];
                    }

                    $titles = collect($services)
                        ->map(fn (string $service) => Term::find('services::'.$service)->title)
                        ->all();

                    return ['services' => $titles];

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
