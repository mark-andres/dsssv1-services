<?php

$connect = NULL;

function addAddress($conn, $parms) {
    global $connect;        
    $connect = $conn;
    $addressId = 0;
    $count = setVarsFromArray($parms, array('str' => &$str, 'street' => &$street, 'streetNumber' => &$streetNumber, 'unit' => &$unit,
        'locationId' => &$locationId, 'location' => &$location, 'locationTypeId' => &$locationTypeId, 'county' => &$county,
        'city' => &$city, 'state' => &$state, 'zip' => &$zip, 'comment' => &$comment));
    if ($count) {
        $sqlFormat = 'CALL addAddress(%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)';
        $sql = sprintf($sqlFormat,
            dbStr($str), dbStr($streetNumber), dbStr($street), dbStr($city), dbStr($county), dbStr($state), dbStr($zip), dbStr($unit), 
            dbInt($locationId), dbStr($location), dbInt($locationTypeId), dbStr($comment)
        );
        $conn->multi_query($sql) || dbErrorExit('multi_query() failed ' . $conn->error);
        
        do {
            if ($result = $conn->store_result()) {
                while ($row = $result->fetch_assoc()) {
                    if (isset($row['address_id']))
                        $addressId = $row['address_id'];
                    if (isset($row['location_id'])) {
                        $locationId = $row['location_id'];
                    }
                } 
                $result->free();
            }
        } while ($conn->more_results() && $conn->next_result());
        
        return array('result' => 1, 'addressId' => $addressId, 'locationId' => $locationId);                        
    } else {
        return array('result' => 0, 'error' => 'No variables POSTed');        
    }
}

function updateAddress($conn, $parms) {
    $count = setVarsFromArray($parms, array('addressId' => &$addressId, 'comment' => &$comment, 'locationId' => &$locationId, 'locationTypeId' => &$locationTypeId,
        'location' => &$location));
    if ($count) {
        if ($locationId === null) {
            $locationId = updateLocation($conn, $locationId, $locationTypeId, $location);
        }
        $stmt = $conn->stmt_init();
        
        $sql = 'UPDATE address SET comment = ?, location_id = ? WHERE address_id = ?';
        
        $stmt->prepare($sql) || dbErrorExit('prepare() failed ' . $stmt->error);
        
        $stmt->bind_param('sii', $comment, $locationId, $addressId) || dbErrorExit('bind_param() failed ' . $stmt->error);
        $stmt->execute() || dbErrorExit('execute() failed ' . $stmt->error);
        
        $affectedRows = $stmt->affected_rows;
        
        $stmt->close();
        return $affectedRows;
    } else {
        return 0;
    }
}

function updateLocation($conn, $locationId, $locationTypeId, $location) {
    $lookupSQL = "SELECT location_id FROM location WHERE loc_name = '$location'";
    ($result = $conn->query($lookupSQL)) || dbErrorExit('query() failed '. $conn->error);
    $row = $result->fetch_assoc();
    if ($row) {
        return $row['location_id'];
    }

    // insert the location if it doesn't exist
    $stmt = $conn->stmt_init();
    $sql = 'INSERT INTO location(loc_name, loc_type_id) VALUES (?, ?)';
    $stmt->prepare($sql) || dbErrorExit('prepare() failed in updateLocation() ' . $stmt->error);
    $stmt->bind_param('si', $location, $locationTypeId) || dbErrorExit('bind_param() failed ' . $stmt->error);
    $stmt->execute() || dbErrorExit('execute() failed ' . $stmt->error);
    return $conn->insert_id;
}

function addPhone($conn, $parms) {
    global $connect;        
    $connect = $conn;
    $count = setVarsFromArray($parms, 
        array('phone' => &$phone, 'phoneTypeId' => &$phoneTypeId, 'addressId' => &$addressId, 'customerId' => &$customerId)
    );
    if ($count) {
        if (!$phoneTypeId) {
            $phoneTypeId = array(NULL, NULL, NULL);
        }
        $phoneIdArray = array();
        
        for ($i = 0; $i < count($phone); $i++) {

            if ($phoneTypeId[$i] == "null")
                $phoneTypeId[$i] = NULL;
            
            $sqlFormat = 'CALL addPhone(%s, %s, %s, %s)';
            $sql = sprintf($sqlFormat,
                dbStr($phone[$i]), dbInt($phoneTypeId[$i]), dbInt($addressId), dbInt($customerId)
            );
            $conn->multi_query($sql) || dbErrorExit('multi_query() failed (' . "$i) (" . $sql . ') ' . $conn->error);
            
            do {
                if ($result = $conn->store_result()) {
                    while ($row = $result->fetch_assoc()) {
                        if (isset($row['phone_id'])) {
                            $phoneIdArray[$i] = $row['phone_id'];
                        }
                    } 
                    $result->free();
                }
            } while ($conn->more_results() && $conn->next_result());
        }       
        
        return array('result' => 1, 'phoneId' => $phoneIdArray, 'phone' => $phone); 
    } else {
        return array('result' => 0, 'error' => 'No variables POSTed');
    } 
}

function addCustomer($conn, $parms) {
    global $connect;        
    $connect = $conn;
    $customerId = 0;
    $count = setVarsFromArray($parms, 
        array('addressId' => &$addressId, 'lastname' => &$lastname, 'firstname' => &$firstname, 'customerComment' => &$comment)
    );
    if ($count) {        
        $sqlFormat = 'CALL addCustomer(NULL, %s, %s, %s, %s)';
        $sql = sprintf($sqlFormat,
            dbInt($addressId), dbStr($lastname), dbStr($firstname), dbStr($comment)        
        );
        $conn->multi_query($sql) || dbErrorExit('multi_query() failed (' . $sql . ') ' . $conn->error);
        
        do {
            if ($result = $conn->store_result()) {
                while ($row = $result->fetch_assoc()) {
                    if (isset($row['customer_id'])) {
                        $customerId = $row['customer_id'];
                    }
                } 
                $result->free();
            }
        } while ($conn->more_results() && $conn->next_result());
              
        return array('result' => 1, 'customerId' => $customerId); 
    } else {
        return array('result' => 0, 'error' => 'No variables POSTed');        
    }    
}

function updateCustomer($conn, $parms) {
    $count = setVarsFromArray($parms, array(
        'customerId' => &$customerId, 'lastname' => &$lastname, 'firstname' => &$firstname, 'customerComment' => &$customerComment
    ));
    if ($count) {
        $stmt = $conn->stmt_init();
        
        $sql = 'UPDATE customer SET lastname = ?, firstname = ?, comment = ? WHERE customer_id = ?';
        
        $stmt->prepare($sql) || dbErrorExit('prepare() failed ' . $stmt->error);
        
        $stmt->bind_param('sssi', $lastname, $firstname, $customerComment, $customerId) || dbErrorExit('bind_param() failed ' . $stmt->error);
        $stmt->execute() || dbErrorExit('execute() failed ' . $stmt->error);
        
        $affectedRows = $stmt->affected_rows;
        
        $stmt->close();
        return $affectedRows;
    } else {
        return 0;
    }    
}

function assocCustomerAddress($conn, $customerId, $addressId) {
    $stmt = $conn->stmt_init();
    
    $sql = 'INSERT INTO customer_address (`customer_id`,`address_id`) VALUES (?, ?)';
    
    $stmt->prepare($sql) || dbErrorExit('prepare() failed ' . $stmt->error);
    
    $stmt->bind_param('ii', $customerId, $addressId) || dbErrorExit('bind_param() failed ' . $stmt->error);
    $stmt->execute() || dbErrorExit('execute() failed ' . $stmt->error);
    
    $affectedRows = $stmt->affected_rows;
    
    $stmt->close();
    return $affectedRows;  
}

function addOrder($conn, $parms) {
    global $connect;        
    $connect = $conn;
    $orderId = $created = 0;
    $count = setVarsFromArray($parms, 
        array('phoneId' => &$phoneId, 'addressId' => &$addressId, 'customerId' => &$customerId, 'storeId' => &$storeId, 
        'distance' => &$distance, 'amount' => &$amount, 'orderComment' => &$orderComment, 'estimate' => &$estimate, 'created' => &$created)
    );
    if ($count) {
        $sqlFormat = 'CALL addOrder(%s, %s, %s, %s, %s, %s, %s, %s, %s)';
        $sql = sprintf($sqlFormat,
            dbInt($phoneId[0]), dbInt($addressId), dbInt($customerId), dbInt($storeId), dbFloat($distance), 
            dbFloat($amount), dbStr($orderComment), dbInt($estimate), dbStr($created)
        );
        $conn->multi_query($sql) || dbErrorExit('multi_query() failed ' . $conn->error);
        
        do {
            if ($result = $conn->store_result()) {
                while ($row = $result->fetch_assoc()) {
                    if (isset($row['order_id'])) {
                        $orderId = $row['order_id'];
                    }
                    if (isset($row['created'])) {
                        $created = $row['created'];
                    }
                } 
                $result->free();
            }
        } while ($conn->more_results() && $conn->next_result());

        return array('result' => 1, 'orderId' => $orderId, 'created' => $created); 
    } else {
        return array('result' => 0, 'error' => 'No variables POSTed');        
    }    
}

function updateOrder($conn, $orderId, $amount, $requestedBy, $comment, $customerId, $created) {
    $stmt = $conn->stmt_init();
    
    $sql = 'UPDATE `order` SET amount = ?, requested_at = ?, comment = ?, customer_id = ?, created = ?  WHERE order_id = ?';

    $stmt->prepare($sql) || dbErrorExit('prepare() failed ' . $stmt->error);
    
    $stmt->bind_param('dssisi', $amount, $requestedBy, $comment, $customerId, $created, $orderId) || dbErrorExit('bind_param() failed ' . $stmt->error);
    $stmt->execute() || dbErrorExit('execute() failed ' . $stmt->error);
    
    $affectedRows = $stmt->affected_rows;
    
    $stmt->close();
    return $affectedRows;  
}

function assocOrderPhone($conn, $orderId, $phoneId) {
    $stmt = $conn->stmt_init();
    
    $sql = 'INSERT INTO order_phone (`order_id`,`phone_id`) VALUES (?, ?)';
    
    $stmt->prepare($sql) || dbErrorExit('prepare() failed ' . $stmt->error);
    
    $stmt->bind_param('ii', $orderId, $phoneId) || dbErrorExit('bind_param() failed ' . $stmt->error);
    $stmt->execute() || dbErrorExit('execute() failed ' . $stmt->error);
    
    $affectedRows = $stmt->affected_rows;
    
    $stmt->close();
    return $affectedRows;  
}

function saveStore($conn, $address) {
    global $connect;        
    $connect = $conn;
    $addressId = 0;
    $sqlFormat = 'CALL addAddress(%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)';
    $sql = sprintf($sqlFormat,
        dbStr($address->str), dbStr($address->streetNumber), dbStr($address->street), dbStr($address->city), dbStr($address->county), 
        dbStr($address->state), dbStr($address->zip), dbStr(''), 
        dbInt(NULL), dbStr(NULL), dbInt(NULL), dbStr(NULL)
    );
    $conn->multi_query($sql) || dbErrorExit('multi_query() failed ' . $conn->error);
    
    do {
        if ($result = $conn->store_result()) {
            while ($row = $result->fetch_assoc()) {
                if (isset($row['address_id']))
                    $addressId = $row['address_id'];
            } 
            $result->free();
        }
    } while ($conn->more_results() && $conn->next_result());
    
    $phoneId = 0;
    $sqlFormat = 'CALL addPhone(%s, %s, %s, %s)';
    $sql = sprintf($sqlFormat,
        dbStr($address->phones[0]->phone), dbInt($address->phones[0]->phoneTypeId), dbInt($addressId), dbInt(NULL)
    );
    $conn->multi_query($sql) || dbErrorExit('multi_query() failed (' . "$i) (" . $sql . ') ' . $conn->error);
    
    do {
        if ($result = $conn->store_result()) {
            while ($row = $result->fetch_assoc()) {
                if (isset($row['phone_id'])) {
                    $phoneId = $row['phone_id'];
                }
            } 
            $result->free();
        }
    } while ($conn->more_results() && $conn->next_result());
    
    $sql = "INSERT INTO store (`address_id`, `phone_id`) VALUES ($addressId, $phoneId)";
    $conn->query($sql) || dbErrorExit('Insert into store failed ' . $conn->error);
    
    return array('result' => 1, 'store_id' => $conn->insert_id);                        
}

function dbInt($val) {
    if ($val === NULL) 
        return 'NULL';
    return $val;
}

function dbFloat($val) {
    if ($val === NULL) 
        return 'NULL';
    return $val;    
}

function dbStr($str) {
    global $connect;
    
    $escStr = NULL;
    if ($str === NULL)
        return 'NULL';
    $escStr = $connect->real_escape_string($str);
    return "'$escStr'";    // put ticks around it if it's not NULL
}
