<?php
require('../Support/utils.php');
startSession();
if (!isset($_SESSION['authenticated'])) {
    exit ;   // fail silently
}
set_time_limit(0);
require('../Includes/connection.inc.php');
$conn = dbConnect('write');

function curl_request($sURL, $sQueryString = null) {
    $cURL = curl_init();
    $sRequest = $sURL . '?' . $sQueryString;
    curl_setopt($cURL, CURLOPT_URL, $sRequest);
    curl_setopt($cURL, CURLOPT_RETURNTRANSFER, TRUE);
    $cResponse = trim(curl_exec($cURL));
    curl_close($cURL);
    
    return $cResponse;
}

function get_geocoded_address($sAddress) {
    $sAddress = str_replace(" ", "+", urlencode($sAddress));
    $sQuery = "address=" . $sAddress . "&sensor=false";
    return json_decode(curl_request('http://maps.googleapis.com/maps/api/geocode/json', $sQuery));
}

?>
<!DOCTYPE html>
<head>
    <title>Normalize Addresses</title>
</head>
<body>
<?php

$sql = "SELECT address_id, str, street_number, street, zip, unit FROM address ORDER BY address_id limit 0, 200";

($result = $conn->query($sql)) || dbErrorExit('query() failed '. $conn->error);

$recordCount = 0;
$geocoderFail = 0;
$problemAddress = 0;
while ($row = $result->fetch_assoc()) {
    $recordCount++;
    echo "<pre>\n";
    $sAddress = $row['str'];
    $streetNumber = $row['street_number'];
    $street = $row['street'];
    $unit = $row['unit'];
    $addressId = $row['address_id'];
    $oResponse = get_geocoded_address($sAddress);
    if ($oResponse->status !== 'OK') {
        $geocoderFail++;
        echo "Geocoder failed: $oResponse->status ($addressId)\n";
        echo "</pre>\n";
        sleep(2);
        continue;
    }
    $result = $oResponse->results[0];
    if ($result->types[0] !== 'street_address') {
        $problemAddress++;
        echo "Address ($addressId) $streetNumber $street $unit has invalid type $result->types[0]\n";        
    } elseif ($result->geometry->location_type !== 'ROOFTOP') {
        $problemAddress++;
        echo "Address ($addressId) $streetNumber $street $unit has problematic location type $result->geometry->location_type\n";        
    }
    
    echo "</pre>\n";
    ob_flush();
    flush();
    sleep(2);
}

echo "\n\n" . $recordCount . " records processed.\n";
echo "The geocoder failed to process $geocoderFail records.\n";
echo "$problemAddress problem addresses were found.\n";
?>
</body>
</html>