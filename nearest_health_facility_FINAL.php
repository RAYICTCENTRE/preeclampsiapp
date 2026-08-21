<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

require_once __DIR__ . '/db_connect.php';

function respond(bool $success, array $data = [], string $message = ''): void
{
    echo json_encode([
        'success' => $success,
        'data' => $data,
        'message' => $message
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$lat = isset($_GET['lat']) ? (float) $_GET['lat'] : null;
$lng = isset($_GET['lng']) ? (float) $_GET['lng'] : null;
$radius = isset($_GET['radius']) ? (float) $_GET['radius'] : 25.0;

if (
    $lat === null || $lng === null ||
    !is_finite($lat) || !is_finite($lng) ||
    $lat < -90 || $lat > 90 ||
    $lng < -180 || $lng > 180
) {
    respond(false, [], 'Invalid GPS coordinates.');
}

if ($radius <= 0 || $radius > 100) {
    $radius = 25;
}

/*
 * Haversine distance in kilometres.
 * Only facilities with valid coordinates are considered.
 */
$sql = "
    SELECT
        id,
        facility_name,
        facility_type,
        facility_level,
        district,
        county,
        sub_county,
        parish,
        village,
        latitude,
        longitude,
        phone,
        (
            6371 * ACOS(
                LEAST(
                    1,
                    GREATEST(
                        -1,
                        COS(RADIANS(?)) *
                        COS(RADIANS(latitude)) *
                        COS(RADIANS(longitude) - RADIANS(?)) +
                        SIN(RADIANS(?)) *
                        SIN(RADIANS(latitude))
                    )
                )
            )
        ) AS distance_km
    FROM health_facilities
    WHERE status = 1
      AND latitude IS NOT NULL
      AND longitude IS NOT NULL
      AND latitude BETWEEN -90 AND 90
      AND longitude BETWEEN -180 AND 180
    HAVING distance_km <= ?
    ORDER BY distance_km ASC
    LIMIT 1
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    respond(false, [], 'Unable to prepare health-facility search.');
}

$stmt->bind_param(
    'ddddd',
    $lat,
    $lng,
    $lat,
    $radius
);

if (!$stmt->execute()) {
    $stmt->close();
    respond(false, [], 'Unable to search the health-facility database.');
}

$result = $stmt->get_result();
$row = $result ? $result->fetch_assoc() : null;
$stmt->close();

if ($row) {

    $row['distance_km'] =
        round((float)$row['distance_km'], 2);

    respond(
        true,
        [
            'source' => 'mothercare_database',
            'facility' => $row
        ],
        'Nearest facility found in MotherCare database.'
    );
}

/*
 * No coordinate-bearing database facility was found.
 * Tell the browser to use its optional external fallback.
 */
respond(
    true,
    [
        'source' => 'external_fallback_required',
        'facility' => null
    ],
    'No coordinate-bearing facility was found in the MotherCare database.'
);
?>
