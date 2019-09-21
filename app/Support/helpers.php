<?php

use Illuminate\Support\Facades\Request;
use App\Services\Address\Country\Country;
use App\Services\Address\Country\CountryLoader;

if (!function_exists('country'))
{
    /**
     * Get the country by it's ISO 3166-1 alpha-2.
     *
     * @param string $code
     * @param bool $hydrate
     *
     * @return Country|mixed
     * @throws Exception
     */
    function country($code, $hydrate = true)
    {
        return CountryLoader::country($code, $hydrate);
    }
}

if (!function_exists('countries'))
{
    /**
     * Get all countries short-listed.
     *
     * @param bool $longlist
     * @param bool $hydrate
     *
     * @return array
     */
    function countries($longlist = false, $hydrate = false)
    {
        return CountryLoader::countries($longlist, $hydrate);
    }
}

if (! function_exists('active')) {
    /**
     * Determine if one of the given route names is active.
     *
     * @param  mixed  $routes
     * @return string
     */
    function active($routes)
    {
        return Request::routeIs($routes) ? ' active' : '';
    }
}

if (! function_exists('flash')) {
    /**
     * Create a flash message.
     *
     * @param  string|null  $message
     * @return \App\Support\Flash\Notifier
     */
    function flash($message = null)
    {
        $notifier = app('flash');

        if (is_null($message)) {
            return $notifier;
        }

        return $notifier->info($message);
    }
}

if (! function_exists('svg')) {
    /**
     * Render an SVG file.
     *
     * @param  string  $name
     * @param  string  $class
     * @param  string  $title
     * @return string
     */
    function svg($name, $class, $title = '')
    {
        $output = file_get_contents(resource_path("svg/{$name}.svg"));

        if ($class) {
            $output = str_replace('<svg', "<svg class=\"{$class}\"", $output);
        }

        if ($title) {
            $output = str_replace('</svg>', "<title>{$title}</title></svg>", $output);
        }

        return $output;
    }
}
