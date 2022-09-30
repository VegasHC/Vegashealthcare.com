<?php

namespace App\Listeners;

use Statamic\Events\EntrySaving;

class AddProviderTitle
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
        return;
        $entry = $event->entry;

        if ($entry->collectionHandle() != 'providers') {
            return;
        }

        if (! empty($entry->get('org_name'))) {
            $entry->set('title', $entry->get('org_name'));
        } else {
            $entry->set('title', $entry->get('first_name').' '.$entry->get('last_name'));
        }
    }
}
