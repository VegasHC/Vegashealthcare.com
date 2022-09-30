<?php

namespace App\Listeners;

use Algolia\AlgoliaSearch\PlacesClient;
use Illuminate\Support\Facades\App;
use Statamic\Events\EntrySaving;
use Statamic\Facades\Entry;

class AddProviderGeoloc
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  EntrySaving  $event
     * @return void
     */
    public function handle(EntrySaving $event)
    {
        if (App::runningInConsole()) {
            //Do not run on bulk imports
            return;
        }

        $entry = $event->entry;

        if ($entry->collectionHandle() != 'providers') {
            return;
        }

        if (empty($entry->get('_geoloc')) && ! empty($entry->get('zip'))) {
            $api = PlacesClient::create(env('PLACES_APP_ID', false), env('PLACES_API_KEY', false));

            $result = $api->search(implode(', ', [$entry->get('address'), $entry->get('city'), $entry->get('state'), $entry->get('zip')]), ['type' => 'address', 'countries' => ['us']]);
//        $result = $places->search("9499 W Charleston Blvd, Las Vegas, NV, 89117", ["type" => "address", "countries" => ["us"]]);

            if (! empty($result['hits'])) {
                $entry->set('_geoloc', $result['hits'][0]['_geoloc']);

                return;
            }

            $zipcode = Entry::query()
                ->where('collection', 'zip_codes')
                ->where('code', $entry->get('zip'))
                ->first();

            if ($zipcode) {
                $entry->set('_geoloc', ['lng' => (float) $zipcode->get('longitude'), 'lat' => (float) $zipcode->get('latitude')]);
            }
        }
    }
}
