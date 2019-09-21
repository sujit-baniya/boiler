<?php


namespace App\Services\Media\Facades;

use App\Services\Media\ConversionRegistry;
use Illuminate\Support\Facades\Facade;
class Conversion extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return ConversionRegistry::class;
    }
}

