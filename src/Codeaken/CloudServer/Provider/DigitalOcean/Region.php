<?php
namespace Codeaken\CloudServer\Provider\DigitalOcean;

use Codeaken\CloudServer\RegionAbstract;

class Region extends RegionAbstract
{
    public static function create($data)
    {
        return new Region(
            $data['slug'],
            $data['name'],
            static::regionIdToCountry($data['slug']),
            static::regionIdToCity($data['slug']),
            $data['available']
        );
    }

    private static function regionIdToCountry($id)
    {
        switch ($id) {
            case 'nyc1':
            case 'nyc2':
            case 'nyc3':
            case 'sfo1':
                return 'us';

            case 'ams1':
            case 'ams2':
            case 'ams3':
                return 'nl';

            case 'lon1':
                return 'gb';

            case 'sgp1':
                return 'sg';

            case 'fra1':
                return 'de';
        }

        return '??';
    }

    private static function regionIdToCity($id)
    {
        switch ($id) {
            case 'nyc1':
            case 'nyc2':
            case 'nyc3':
                return 'New York';

            case 'sfo1':
                return 'San Francisco';

            case 'ams1':
            case 'ams2':
            case 'ams3':
                return 'Amsterdam';

            case 'lon1':
                return 'London';

            case 'sgp1':
                return 'Singapore';

            case 'fra1':
                return 'Frankfurt';
        }

        return 'Unknown';
    }
}
