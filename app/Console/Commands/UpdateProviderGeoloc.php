<?php

namespace App\Console\Commands;

use Algolia\AlgoliaSearch\PlacesClient;
use Illuminate\Console\Command;
use Statamic\Facades\Entry;

class UpdateProviderGeoloc extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'providers:updategeoloc';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update all provider geolocation coordinates';

    protected $api;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->api = PlacesClient::create(env('PLACES_APP_ID', false), env('PLACES_API_KEY', false));
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $providers = Entry::query()
            ->where('collection', 'providers')
            ->get();

        foreach ($providers as $provider) {
            if (! empty($provider->has('_geoloc'))) {
                continue;
            }

            echo $provider->title."\n";

            $geoloc = $this->fetchGeoloc([
                'address' => $provider->address,
                'city' => $provider->city,
                'state' => $provider->state,
                'zip' => $provider->zip,
            ]);

            if (! empty($geoloc)) {
                var_dump($geoloc);
                $provider->_geoloc = $geoloc;
                $provider->save();
            }
        }

        return 0;
    }

    protected function fetchGeoloc($data)
    {
        $result = $this->api->search(implode(', ', [$data['address'], $data['city'], $data['state'], $data['zip']]), ['type' => 'address', 'countries' => ['us']]);
//        $result = $places->search("9499 W Charleston Blvd, Las Vegas, NV, 89117", ["type" => "address", "countries" => ["us"]]);

        if (! empty($result['hits'])) {
            return $result['hits'][0]['_geoloc'];
        }

        $zipcode = Entry::query()
            ->where('collection', 'zip_codes')
            ->where('code', $data['zip'])
            ->first();

        if ($zipcode) {
            return ['lng' => (float) $zipcode->get('longitude'), 'lat' => (float) $zipcode->get('latitude')];
        }

        return [];
    }
}
