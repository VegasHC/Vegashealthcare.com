<?php

namespace App\Listeners;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use Statamic\Events\EntrySaving;

class AddProviderGeoloc
{
    public function handle(EntrySaving $event): void
    {
        if (App::runningInConsole()) {
            // Do not run on bulk imports
            return;
        }

        $provider = $event->entry;

        if ($provider->collectionHandle() != 'providers') {
            return;
        }

        if (empty($provider->get('_geoloc')) && ! empty($provider->get('zip'))) {
            $query = http_build_query([
                'text' => "$provider->address, $provider->city, $provider->state, $provider->zip, USA",
                'apiKey' => config('services.geoapify.key'),
            ]);

            $properties = Http::get("https://api.geoapify.com/v1/geocode/search?$query")
                ->json('features.0.properties');

            $provider->set('_geoloc', [
                'lat' => $properties['lat'],
                'lng' => $properties['lon'],
            ])->save();
        }
    }
}
