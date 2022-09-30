<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Statamic\Facades\Entry;
use Statamic\Facades\Term;

class SearchController extends Controller
{
    public function results(Request $request)
    {
        $currentServices = $request->input('providers.refinementList.services');

        if (! empty($currentServices)) {
            $currentServices = array_map(function ($text) {
            return \Statamic\Support\Str::slug($text);
            }, $currentServices);
        }

        if ($request->input('providers.menu.category')) {
            $currentCategories[] = $request->input('providers.menu.category');
        } else {
            $currentCategories = [];
        }

        $adStack = $this->getAdStack($currentServices, $currentCategories);

        $vars['title'] = 'Search Results';
        $vars['ads'] = $adStack;

        if ($zip = $request->input('providers.zip')) {
            $zipcode = Entry::query()
                ->where('collection', 'zip_codes')
                ->where('code', $zip)
                ->first();

            if ($zipcode) {
                $vars['zipcode'] = $zip;
                $vars['geoloc'] = json_encode(['lng' => (float) $zipcode->get('longitude'), 'lat' => (float) $zipcode->get('latitude')]);
            }
        }

        return (new \Statamic\View\View)
            ->template('search/results')
            ->layout('layout')
            ->with($vars);
    }

    public function getGeoloc(Request $request)
    {
        $zip = $request->input('zip');
        $zipcode = Entry::query()
                ->where('collection', 'zip_codes')
                ->where('code', $zip)
                ->first();

        if ($zipcode) {
            $vars['zipcode'] = $zip;
            $vars['geoloc'] = ['lng' => (float) $zipcode->get('longitude'), 'lat' => (float) $zipcode->get('latitude')];
        }

        return response()->json($vars);
    }

    public function getContent(Request $request)
    {
        $currentServices = $request->input('currentServices');
        $serviceTags = $request->input('serviceTags');

        if (is_array($currentServices) && is_array($serviceTags)) {
            $currentServices = array_merge($currentServices, $currentServices);
        } elseif (is_array($serviceTags)) {
            $currentServices = $serviceTags;
        }

        if (! empty($currentServices)) {
            $currentServices = array_map(function ($text) {
            return \Statamic\Support\Str::slug($text);
            }, $currentServices);
        }

        if ($request->input('currentCategory')) {
            $currentCategories[] = $request->input('currentCategory');
        } else {
            $currentCategories = [];
        }

        $adStack = $this->getAdStack($currentServices, $currentCategories);

        return response()->json($adStack);
    }

    protected function getAdStack($currentServices, $currentCategories)
    {
        $adStack = [];
        $vars = [];

        if (! empty($currentServices)) {
            foreach ($currentServices as $serviceSlug) {
                $taxonomySearch[] = 'services::'.$serviceSlug;
            }

            $serviceTaxonomies = Term::query()->whereIn('slug', $currentServices)->get();

            foreach ($serviceTaxonomies as $item) {
                $currentCategories[] = $item->get('category');
            }
        }

        $ads = \Statamic\Facades\Collection::find('ads')
            ->queryEntries()
            ->where('active', true)
            ->orderBy('order', 'asc')
            ->get();

        foreach ($ads as $ad) {
            if (! empty($currentServices) && ! empty($ad->get('services')) && ! empty(array_intersect($ad->get('services'), $currentServices))) {
                $adStack[$ad->get('placement')]['services'][] = ['link' => $ad->get('link'), 'image' => '/assets/'.$ad->get('image')];
            }

            if (! empty($currentCategories) && ! empty($ad->get('categories')) && ! empty(array_intersect($ad->get('categories'), $currentCategories))) {
                $adStack[$ad->get('placement')]['categories'][] = ['link' => $ad->get('link'), 'image' => '/assets/'.$ad->get('image')];
            }
        }

        foreach ($adStack as $placement => $data) {
            $finalAds = [];

            if (! empty($data['services'])) {
                $finalAds = array_merge($finalAds, $data['services']);
            } elseif (! empty($data['categories'])) {
                $finalAds = array_merge($finalAds, $data['categories']);
            }

            if ($finalAds) {
                $vars['content_'.$placement] = $finalAds;
            }
        }

        return $vars;
    }
}
