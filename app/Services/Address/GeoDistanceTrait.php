<?php

namespace App\Services\Address;


use App\Services\Address\Exceptions\GeoDistance\InvalidMeasurementException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

trait GeoDistanceTrait
{

    protected $latColumn = 'latitude';

    protected $lngColumn = 'longitude';

    protected $distance = 10;

    private static $MEASUREMENTS = [
        'miles' => 3959,
        'm' => 3959,
        'kilometers' => 6371,
        'km' => 6371,
        'meters' => 6371000,
        'feet' => 20902231,
        'nautical_miles' => 3440.06479,
    ];

    public function getLatColumn()
    {
        return "{$this->getTable()}.{$this->latColumn}";
    }

    public function getLngColumn()
    {
        return "{$this->getTable()}.{$this->lngColumn}";
    }

    public function lat($lat = null)
    {
        if ($lat)
        {
            $this->lat = $lat;
            return $this;
        }

        return $this->lat;
    }

    public function lng($lng = null)
    {
        if ($lng)
        {
            $this->lng = $lng;
            return $this;
        }

        return $this->lng;
    }

    /**
     * @param string
     *
     * Grabs the earths mean radius in a specific measurment based on the key provided, throws an exception
     * if no mean readius measurement is found
     *
     * @return float
     **@throws InvalidMeasurementException
     */

    public function resolveEarthMeanRadius($measurement = null)
    {
        $measurement = ($measurement === null) ? key(static::$MEASUREMENTS) : strtolower($measurement);

        if (array_key_exists($measurement, static::$MEASUREMENTS))
        {
            return static::$MEASUREMENTS[$measurement];
        }

        throw new InvalidMeasurementException('Invalid measurement');
    }

    /**
     * @param Query
     * @param integer
     * @param mixed
     * @param mixed
     *
     * @return Query
     *
     * Implements a distance radius search using Haversine formula.
     * Returns a query scope.
     * credit - https://developers.google.com/maps/articles/phpsqlsearch_v3
     **@throws InvalidMeasurementException
     * @todo Use pdo paramater bindings, instead of direct variables in query
     */

    public function scopeWithin($q, $distance, $measurement = null, $lat = null, $lng = null)
    {
        $pdo = DB::connection()->getPdo();

        $latColumn = $this->getLatColumn();
        $lngColumn = $this->getLngColumn();

        $lat = ($lat === null) ? $this->lat() : $lat;
        $lng = ($lng === null) ? $this->lng() : $lng;

        $meanRadius = $this->resolveEarthMeanRadius($measurement);
        $distance = (float)($distance);

        // first-cut bounding box (in degrees)
        $maxLat = (float)($lat) + rad2deg($distance / $meanRadius);
        $minLat = (float)($lat) - rad2deg($distance / $meanRadius);
        // compensate for degrees longitude getting smaller with increasing latitude
        $maxLng = (float)($lng) + rad2deg($distance / $meanRadius / cos(deg2rad((float)($lat))));
        $minLng = (float)($lng) - rad2deg($distance / $meanRadius / cos(deg2rad((float)($lat))));

        $lat = $pdo->quote((float)($lat));
        $lng = $pdo->quote((float)($lng));
        $meanRadius = $pdo->quote((float)($meanRadius));

        // Paramater bindings havent been used as it would need to be within a DB::select which would run straight away and return its result, which we dont want as it will break the query builder.
        // This method should work okay as our values have been cooerced into correct types and quoted with pdo.
        $haversineSql = "( $meanRadius * acos( cos( radians($lat) ) * cos( radians( $latColumn ) ) * cos( radians( $lngColumn ) - radians($lng) ) + sin( radians($lat) ) * sin( radians( $latColumn ) ) ) )";
        return $q->select('*', DB::raw("$haversineSql AS distance"))
            ->from(DB::raw(
                "(
                    Select *
                    From {$this->getTable()}
                    Where $latColumn Between $minLat And $maxLat
                    And $lngColumn Between $minLng And $maxLng
                ) As {$this->getTable()}"
            ))
            ->where(DB::raw($haversineSql), '<=', $distance)
            ->orderby('distance', 'ASC');
    }

    public function scopeOutside(Builder $q, $distance, $measurement = null, $lat = null, $lng = null)
    {
        $pdo = DB::connection()->getPdo();

        $latColumn = $this->getLatColumn();
        $lngColumn = $this->getLngColumn();

        $lat = ($lat === null) ? $this->lat() : $lat;
        $lng = ($lng === null) ? $this->lng() : $lng;

        $meanRadius = $this->resolveEarthMeanRadius($measurement);
        $distance = (float)($distance);

        $lat = $pdo->quote((float)$lat);
        $lng = $pdo->quote((float)$lng);
        $meanRadius = $pdo->quote((float)$meanRadius);
        $haversineSql = "( $meanRadius * acos( cos( radians($lat) ) * cos( radians( $latColumn ) ) * cos( radians( $lngColumn ) - radians($lng) ) + sin( radians($lat) ) * sin( radians( $latColumn ) ) ) )";
        return $q->select('*', DB::raw("$haversineSql AS distance"))
            ->where(DB::raw($haversineSql), '>=', $distance)
            ->orderby('distance', 'ASC');
    }

}
