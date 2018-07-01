<?php
require('../Support/utils.php');
beginServiceFunction();

if (isset($_GET['address'])) {
    require('../Includes/connection.inc.php');
    $conn = dbConnect('read');

    $address = json_decode($_GET['address']);
    $sql = "
SELECT GROUP_CONCAT(phone) as phones, GROUP_CONCAT(phone_id) AS phone_ids, GROUP_CONCAT(phone_type_id) AS phone_type_ids, 
     address.str, street_number, street, city, county, state_code, zip, unit, location.location_id as location_id, loc_name, loc_type_desc, 
     address.address_id as address_id, lastname, firstname, customer.comment as customer_comment, address.comment as address_comment, 
     customer.customer_id as customer_id
FROM phone_address 
INNER JOIN phone using (phone_id)
INNER JOIN address using (address_id)
INNER JOIN location USING (location_id)
INNER JOIN location_type USING (loc_type_id)
LEFT OUTER JOIN customer_phone USING (phone_id)
LEFT OUTER JOIN customer USING (customer_id)
WHERE address_id IN (
     SELECT address_id FROM address
     INNER JOIN phone_address USING (address_id)
     WHERE street = '$address->street' AND street_number = '$address->streetNumber'
)
GROUP BY address_id
ORDER BY unit";
    ($result = $conn->query($sql)) || dbErrorExit('query() failed '. $conn->error);

    $rows = array();
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    echo json_encode($rows);
}