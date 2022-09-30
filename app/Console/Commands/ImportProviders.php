<?php

namespace App\Console\Commands;

use Algolia\AlgoliaSearch\PlacesClient;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use League\Csv\Reader;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Taxonomy;
use Statamic\Facades\Term;

class ImportProviders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'providers:import {filename}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import provider data from CSV file';

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
        $filename = $this->argument('filename');
        $path = storage_path('import');

        $collection = Collection::findByHandle('providers');

        echo $path.'/'.$filename."\n\n\n";

        $csv = Reader::createFromPath($path.'/'.$filename)
            ->setHeaderOffset(0);

        foreach ($csv as $record) {
            $data = array_change_key_case($record, CASE_LOWER);
            $data = array_map('trim', $data);

            if (! empty($data['org_name'])) {
                $data['title'] = $data['org_name'];
            } else {
                $data['title'] = $data['first_name'].' '.$data['last_name'];
            }

            $services = explode(',', $data['services']);

            $data['category'] = Str::slug($data['category']);
            $data['services'] = array_map(function ($term) {
            return Str::slug(trim($term));
            }, explode(',', $data['services']));

            foreach ($services as $service) {
                $service = trim($service);
                $slug = Str::slug($service);
                $entry = Term::findBySlug($slug, 'services');

                if (! $entry) {
                    $term = Term::make()
                        ->slug($slug)
                        ->taxonomy(Taxonomy::findByHandle('services'))
                        ->set('title', $service)
                        ->set('active', true)
                        ->set('category', $data['category']);

                    $term->save();
                    echo $service.' '.$slug."\n";

//                    $term = Term::make()->taxonomy("services")->slug($slug)->blueprint("services")->data(['title' => $service, "category" => $data['category'], "slug" => $slug, "active" => "true"])->save();
                }
            }

//            We will do this in a separate command
//            $data['_geoloc'] = $this->fetchGeoloc($data);

            $entry = Entry::make()
                ->slug(Str::slug($data['category'].'-'.uniqid()))
                ->locale('default')
                ->collection($collection)
                ->data($data);

            $entry->save();
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
