<?php

namespace Eduardoks98\Geolocation\Services;

/**
 * Distance Calculation Service
 *
 * Calculate distances between coordinates using various formulas.
 */
class DistanceService
{
    protected string $unit;
    protected float $earthRadiusKm;
    protected float $earthRadiusMi;

    public function __construct()
    {
        $config = config('geolocation.distance');

        $this->unit = $config['unit'] ?? 'km';
        $this->earthRadiusKm = $config['earth_radius_km'] ?? 6371;
        $this->earthRadiusMi = $config['earth_radius_mi'] ?? 3959;
    }

    /**
     * Calculate distance between two points using Haversine formula.
     *
     * @param float $lat1 Latitude of point 1
     * @param float $lng1 Longitude of point 1
     * @param float $lat2 Latitude of point 2
     * @param float $lng2 Longitude of point 2
     * @param string|null $unit Unit (km, mi, m)
     * @return float Distance in specified unit
     */
    public function calculate(
        float $lat1,
        float $lng1,
        float $lat2,
        float $lng2,
        ?string $unit = null
    ): float {
        $unit = $unit ?? $this->unit;

        // Convert to radians
        $lat1Rad = deg2rad($lat1);
        $lat2Rad = deg2rad($lat2);
        $deltaLat = deg2rad($lat2 - $lat1);
        $deltaLng = deg2rad($lng2 - $lng1);

        // Haversine formula
        $a = sin($deltaLat / 2) ** 2 +
            cos($lat1Rad) * cos($lat2Rad) * sin($deltaLng / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        // Get Earth radius based on unit
        $radius = $this->getEarthRadius($unit);

        $distance = $radius * $c;

        // Convert to meters if needed
        if ($unit === 'm') {
            $distance *= 1000;
        }

        return round($distance, 2);
    }

    /**
     * Calculate distance between two coordinates (array format).
     *
     * @param array $point1 [lat, lng]
     * @param array $point2 [lat, lng]
     * @param string|null $unit
     * @return float
     */
    public function between(array $point1, array $point2, ?string $unit = null): float
    {
        return $this->calculate(
            $point1['lat'] ?? $point1[0],
            $point1['lng'] ?? $point1[1],
            $point2['lat'] ?? $point2[0],
            $point2['lng'] ?? $point2[1],
            $unit
        );
    }

    /**
     * Calculate distances from one point to multiple points.
     *
     * @param array $origin [lat, lng]
     * @param array $destinations Array of [lat, lng] points
     * @param string|null $unit
     * @return array Distances for each destination
     */
    public function fromPointToMany(array $origin, array $destinations, ?string $unit = null): array
    {
        $distances = [];

        foreach ($destinations as $index => $destination) {
            $distances[$index] = $this->between($origin, $destination, $unit);
        }

        return $distances;
    }

    /**
     * Find the closest point from a list.
     *
     * @param array $origin [lat, lng]
     * @param array $points Array of [lat, lng] points
     * @param string|null $unit
     * @return array|null Closest point with distance
     */
    public function findClosest(array $origin, array $points, ?string $unit = null): ?array
    {
        if (empty($points)) {
            return null;
        }

        $distances = $this->fromPointToMany($origin, $points, $unit);
        $minIndex = array_keys($distances, min($distances))[0];

        return [
            'point' => $points[$minIndex],
            'index' => $minIndex,
            'distance' => $distances[$minIndex],
            'unit' => $unit ?? $this->unit,
        ];
    }

    /**
     * Find points within a radius.
     *
     * @param array $center [lat, lng]
     * @param array $points Array of points
     * @param float $radius Radius in specified unit
     * @param string|null $unit
     * @return array Points within radius with distances
     */
    public function findWithinRadius(
        array $center,
        array $points,
        float $radius,
        ?string $unit = null
    ): array {
        $results = [];

        foreach ($points as $index => $point) {
            $distance = $this->between($center, $point, $unit);

            if ($distance <= $radius) {
                $results[] = [
                    'point' => $point,
                    'index' => $index,
                    'distance' => $distance,
                ];
            }
        }

        // Sort by distance
        usort($results, fn($a, $b) => $a['distance'] <=> $b['distance']);

        return $results;
    }

    /**
     * Sort points by distance from origin.
     *
     * @param array $origin [lat, lng]
     * @param array $points Array of points
     * @param string|null $unit
     * @param string $order 'asc' or 'desc'
     * @return array Sorted points with distances
     */
    public function sortByDistance(
        array $origin,
        array $points,
        ?string $unit = null,
        string $order = 'asc'
    ): array {
        $results = [];

        foreach ($points as $index => $point) {
            $results[] = [
                'point' => $point,
                'index' => $index,
                'distance' => $this->between($origin, $point, $unit),
            ];
        }

        usort($results, function ($a, $b) use ($order) {
            return $order === 'asc'
                ? $a['distance'] <=> $b['distance']
                : $b['distance'] <=> $a['distance'];
        });

        return $results;
    }

    /**
     * Calculate the center point of multiple coordinates.
     *
     * @param array $points Array of [lat, lng] points
     * @return array Center point [lat, lng]
     */
    public function calculateCenter(array $points): array
    {
        if (empty($points)) {
            return ['lat' => 0, 'lng' => 0];
        }

        $x = 0;
        $y = 0;
        $z = 0;

        foreach ($points as $point) {
            $lat = $point['lat'] ?? $point[0];
            $lng = $point['lng'] ?? $point[1];

            $latRad = deg2rad($lat);
            $lngRad = deg2rad($lng);

            $x += cos($latRad) * cos($lngRad);
            $y += cos($latRad) * sin($lngRad);
            $z += sin($latRad);
        }

        $count = count($points);
        $x /= $count;
        $y /= $count;
        $z /= $count;

        $centralLng = atan2($y, $x);
        $centralLat = atan2($z, sqrt($x ** 2 + $y ** 2));

        return [
            'lat' => round(rad2deg($centralLat), 6),
            'lng' => round(rad2deg($centralLng), 6),
        ];
    }

    /**
     * Calculate the bounding box for a center point and radius.
     *
     * @param float $lat Center latitude
     * @param float $lng Center longitude
     * @param float $radius Radius in km
     * @return array Bounding box [min_lat, min_lng, max_lat, max_lng]
     */
    public function getBoundingBox(float $lat, float $lng, float $radius): array
    {
        // Approximate degrees per km
        $latDegPerKm = 1 / 110.574;
        $lngDegPerKm = 1 / (111.320 * cos(deg2rad($lat)));

        $latDelta = $radius * $latDegPerKm;
        $lngDelta = $radius * $lngDegPerKm;

        return [
            'min_lat' => round($lat - $latDelta, 6),
            'min_lng' => round($lng - $lngDelta, 6),
            'max_lat' => round($lat + $latDelta, 6),
            'max_lng' => round($lng + $lngDelta, 6),
        ];
    }

    /**
     * Check if a point is within a bounding box.
     *
     * @param array $point [lat, lng]
     * @param array $boundingBox [min_lat, min_lng, max_lat, max_lng]
     * @return bool
     */
    public function isWithinBoundingBox(array $point, array $boundingBox): bool
    {
        $lat = $point['lat'] ?? $point[0];
        $lng = $point['lng'] ?? $point[1];

        return $lat >= $boundingBox['min_lat'] &&
            $lat <= $boundingBox['max_lat'] &&
            $lng >= $boundingBox['min_lng'] &&
            $lng <= $boundingBox['max_lng'];
    }

    /**
     * Convert distance between units.
     *
     * @param float $distance
     * @param string $from Source unit
     * @param string $to Target unit
     * @return float
     */
    public function convertUnit(float $distance, string $from, string $to): float
    {
        // Convert to kilometers first
        $km = match ($from) {
            'mi' => $distance * 1.60934,
            'm' => $distance / 1000,
            default => $distance,
        };

        // Convert from kilometers to target unit
        return match ($to) {
            'mi' => round($km / 1.60934, 2),
            'm' => round($km * 1000, 2),
            default => round($km, 2),
        };
    }

    /**
     * Format distance with unit.
     *
     * @param float $distance
     * @param string|null $unit
     * @return string
     */
    public function format(float $distance, ?string $unit = null): string
    {
        $unit = $unit ?? $this->unit;

        return match ($unit) {
            'mi' => number_format($distance, 2) . ' mi',
            'm' => number_format($distance, 0) . ' m',
            default => number_format($distance, 2) . ' km',
        };
    }

    /**
     * Get Earth radius based on unit.
     */
    protected function getEarthRadius(string $unit): float
    {
        return match ($unit) {
            'mi' => $this->earthRadiusMi,
            default => $this->earthRadiusKm,
        };
    }
}
