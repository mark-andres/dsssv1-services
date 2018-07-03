<?php
require('../Support/utils.php');
beginServiceFunction();

require('../Includes/connection.inc.php');
$conn = dbConnect('read');

$storeId = $_SESSION['store_id'];
$condition = "store_id = $storeId";
if (isset($_GET['fromDate']) && isset($_GET['toDate'])) {
    $fromDate = urldecode($_GET['fromDate']);
    $toDate = urldecode($_GET['toDate']);
    $condition .= " AND order.created BETWEEN '$fromDate' AND '$toDate'";
} elseif (isset($_GET['lastUpdate'])) {
    $lastUpdate = urldecode($_GET['lastUpdate']);
    $condition .= " AND order.updated >= '$lastUpdate'";
} else {
    $condition .= ' AND DATE(order.created) = DATE(NOW())';
}
if (isset($_GET['routeId'])) {
    $condition .= ' AND route_id = ' . $_GET['routeId'] . ' ';
}

$sql = "SELECT 
GROUP_CONCAT(phone_id) AS phone_ids, GROUP_CONCAT(phone) AS phones, GROUP_CONCAT(phone_type_id) AS phone_type_ids, 
store_id, order_id, customer_id, address_id, distance, amount, order.created as created, order.updated as updated, order.comment as order_comment, delivered, 
requested_at, order_status_id, str, street_number, street, city, county, state_code, zip, unit, location_id, address.comment as address_comment, 
lastname, firstname, customer.comment as customer_comment, loc_name, loc_type_id, route_id, route_step, staff_id, route.created as route_created,
map_service_route
FROM `order`
JOIN order_phone USING (order_id)
JOIN phone USING (phone_id)
JOIN address USING (address_id)
JOIN location USING (location_id)
LEFT JOIN customer USING (customer_id)
LEFT JOIN route USING (route_id)
WHERE $condition
GROUP BY order_id
ORDER BY order.created DESC";

($result = $conn->query($sql)) || dbErrorExit('query() failed '. $conn->error);

$rows = array();
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}

echo json_encode($rows);



