<?php

namespace App\Listeners;

use Illuminate\Support\Facades\App;
use Statamic\Facades\Term;

class AddCategoryTerm
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
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        if (App::runningInConsole()) {
            //Do not run on bulk imports
            return;
        }

        $entry = $event->entry;
        if ($entry->collectionHandle() != 'service_categories') {
            return;
        }

        $slug = $entry->slug();
        $title = $entry->get('title');

        $term = Term::findBySlug($slug, 'service_categories');

        if (! $term) {
            $term = Term::make()
                ->taxonomy('service_categories')
                ->slug($slug)
                ->data(['title' => $title])
                ->save();
        }
    }
}
