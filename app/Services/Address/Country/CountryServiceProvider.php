<?php

namespace App\Services\Address\Country;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;

class CountryServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        // Add country validation rule
        Validator::extend('country', function ($attribute, $value) {
            return array_key_exists(mb_strtolower($value), countries());
        }, 'Country MUST be valid!');
    }
}
