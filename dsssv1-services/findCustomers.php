<?php
require('../Support/utils.php');
startSession();
if (!isset($_SESSION['authenticated']) || !isset($_GET['lastname'])) {
    exit ;   // fail silently
}

require('../Includes/connection.inc.php');
$conn = dbConnect('read');

$lastname = $conn->real_escape_string($_GET['lastname']);

$sql = "SELECT address_id, address.comment as address_comment, city, customer.comment as customer_comment, county, customer_id, 
        firstname, lastname, loc_name, loc_type_id, location_id, state_code, str, street, street_number, unit, zip 
        FROM customer
        JOIN customer_address USING (customer_id)
        JOIN address USING (address_id)
        JOIN location USING (location_id)
        WHERE lastname LIKE '{$lastname}%'
        ORDER BY lastname;";

($result = $conn->query($sql)) || dbErrorExit('query() failed '. $conn->error);

$response = new stdClass;
$response->rows = array();

while ($row = $result->fetch_assoc()) {
    $response->rows[] = $row;
}

$response->result = 1;
echo json_encode($response);
