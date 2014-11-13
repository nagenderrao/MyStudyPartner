<?php

function fetch($method, $resource, $search_str = NULL) {
    $params = array('oauth2_access_token' => $_SESSION['access_token'],
        'count' => COUNT_SIZE,
        'format' => 'json',
    );

    $url = 'https://api.linkedin.com' . $resource . '?' . http_build_query($params) . '&' . $search_str;
    //echo $url."<br>";
    //https://api.linkedin.com/v1/people/~:(id,first-name,last-name,headline,picture-url)?oauth2_access_token=AQWTtCw5_kJ_Grgxs4ZFRJfb2IAYAgX9wx3fB-1p_Qqhl75YSq_ZnsW6AjqH1kTFyKu0MmJkb3g2bGfe0tCrFvhIRBkRR-MBxz08dl-fDNtNvsehYawPC4PRDe0qDOwKnVWeWGvkdUMq_54wkIqbPfGt2L2b_fXUqAxwPf6Vramlu-tyX0Q&count=25&format=json&
    // Tell streams to make a (GET, POST, PUT, or DELETE) request
    /* $context = stream_context_create(
      array('http' =>
      array('method' => $method,
      )
      )
      );

      // Hocus Pocus
      $response = file_get_contents($url, false, $context); */

    //echo $url."<br><br><br>";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
    //curl_setopt ($ch,CURLOPT_SSL_VERIFYPEER,false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    if(curl_exec($ch) === false)
		{
		    echo 'Curl error: ' . curl_error($ch);
}
    $response = curl_exec($ch);
    curl_close($ch);

    // Native PHP object, please
    return json_decode($response);
}

function removeEmptyElements($array) {
    foreach ($array as $key => $value) {
        if (empty($value)) {
            unset($array[$key]);
        } else if (is_array($value)) {
            $array[$key] = removeEmptyElements($value);
        }
    }
    return $array;
}

function doImplode($obj = NULL) {
    $address = array();
    $add = '';

    if (is_array($obj)) {
        foreach ($obj as $v) {

            if (is_object($v->address)) {
                $add = $v->address->street1 . ',' . $v->address->city . ',' . $v->address->postalCode;
            }

            if (is_object($v->contactInfo)) {
                $add .= " " . $v->contactInfo->phone1 . ',' . $v->contactInfo->fax;
            }

            try {
                $add = rtrim($add, ',');
                $add = trim($add, ',');
                $add = htmlentities($add, ENT_QUOTES | ENT_IGNORE | ENT_NOQUOTES, "UTF-8");
                $address[] = $add;
            } catch (Exception $e) {
                echo 'Caught exception: ', $e->getMessage(), "\n";
                //print_r($v);
                //exit;
            }
        }
    }

    return $address;
}

function is_multidimention_array($array) {
    foreach ($array as $item) {
        if (is_array($item)) {
            return true;
        }
    }
    return false;
}

function getRows($tbl, $fields = array('*'), $whereParam = array('1' => 1)) {
    $fields_str = implode(",", $fields);
    $where_sql = ' WHERE ';
    foreach ($whereParam as $k => $v) {
        if (!empty($k)) {
            $where_sql .= $k . "='" . $v . "' AND ";
        }
    }
    $where_sql = rtrim($where_sql, " AND ");
    $result = mysql_query("SELECT $fields_str FROM $tbl $where_sql") or die(mysql_error());
    return $result;
}

/*function getRowCount($tbl, $whereParam = array()) {
    $where_sql = ' WHERE ';
    foreach ($whereParam as $k => $v) {
        if (!empty($k)) {
            $where_sql .= $k . "='" . $v . "' AND ";
        }
    }
    $where_sql = rtrim($where_sql, " AND ");

    $result = mysql_query("SELECT * FROM $tbl $where_sql") or die(mysql_error());
    $num_rows = (int) mysql_num_rows($result);
    return $num_rows;
}
  function addData($tbl = NULL, $data = NULL) {
    $keys = array_keys($data);
    $keys_string = implode(",", $keys);
    $values = array_values($data);
    $values_string = "'" . implode("','", $values) . "'";
    $sql = "INSERT INTO " . $tbl . " ({$keys_string}) VALUES ({$values_string})";
    mysql_query($sql) or die(mysql_error());
    return mysql_insert_id();
}

function editData($tbl = NULL, $data = array(), $whereParam = array()) {
    if (!empty($data) && !empty($whereParam)) {
        $set_sql = '';
        foreach ($data as $k => $v) {
            if (!empty($k)) {
                $set_sql .= $k . "='" . $v . "',";
            }
        }
        $set_sql = rtrim($set_sql, ",");

        $where_sql = ' WHERE ';
        foreach ($whereParam as $k => $v) {
            if (!empty($k)) {
                $where_sql .= $k . "='" . $v . "' AND ";
            }
        }
        $where_sql = rtrim($where_sql, " AND ");
        if (!empty($set_sql) && !empty($where_sql)) {
            $sql = "UPDATE " . $tbl . " SET " . $set_sql . $where_sql;
            mysql_query($sql) or die(mysql_error());
        }
    }
}

function getData($tbl = NULL, $fields = NULL, $where = NULL) {
    $where_sql = '';
    if (!empty($where)) {
        $where_sql = ' WHERE ';
        foreach ($where as $k => $v) {
            if (!empty($k)) {
                $where_sql .= $k . "='" . $v . "' AND ";
            }
        }
        $where_sql = rtrim($where_sql, " AND ");
    }

    $result = null;
    if (!empty($tbl)) {
        $sql = "SELECT " . implode(',', $fields) . " FROM " . $tbl . $where_sql;
        $result = mysql_query($sql) or die(mysql_error());
    }

    return $result;
}
  function deleteData($tbl = NULL, $where = NULL) {
    $where_sql = '';
    if (!empty($where)) {
        $where_sql = ' WHERE ';
        foreach ($where as $k => $v) {
            if (!empty($k)) {
                $where_sql .= $k . "='" . $v . "' AND ";
            }
        }
        $where_sql = rtrim($where_sql, " AND ");
    }
    if (!empty($tbl)) {
        $sql = "DELETE FROM " . $tbl . " " . $where_sql;
        mysql_query($sql) or die(mysql_error());
    }
}
 */

function isKeywordIDExist($tbl, $whereParam = array()) {
    $where_sql = ' WHERE ';
    foreach ($whereParam as $k => $v) {
        if (!empty($k)) {
            $where_sql .= $k . "='" . $v . "' AND ";
        }
    }
    $where_sql = rtrim($where_sql, " AND ");

    $sql = "SELECT id FROM " . $tbl . " " . $where_sql;
    $result = mysql_query($sql) or die(mysql_error());
    $row = mysql_fetch_row($result);
    return $row;
}






function addErrorLog($search_data = NULL, $data = NULL) {
    $erro_log_data = array();
    $erro_log_data['error_code'] = $search_data->errorCode;
    $erro_log_data['message'] = $search_data->message;
    $erro_log_data['request_id'] = $search_data->requestId;
    $erro_log_data['status'] = $search_data->status;
    $erro_log_data['timestamp'] = $search_data->timestamp;
    $erro_log_data['keyword_id'] = $data['keyword_id'];
    $erro_log_data['user_auto_id'] = $data['user_auto_id'];
    $erro_log_data['ip_address'] = $_SERVER['REMOTE_ADDR'];
    $error_auto_id = addData('tbl_linkedin_error_log', $erro_log_data);
    $error_log_str = implode(', ', $erro_log_data);
    return $error_log_str . "<br>";
}
?>