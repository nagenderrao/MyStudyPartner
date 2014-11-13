<?php
class BatchPersistence extends BatchRoot
{
    var $m_connectionHandle;
    var $m_url_val;
    var $m_row_count;
    var $m_col_count;
    var $m_data_rows;

    function BatchPersistence()
    {
        $this->BatchRoot();
    }


    function BatchPersistenceSetup()
    {
        $this->Setup();
        $this->InitDatabase();
    }

    function InitDatabase()
    {
        //global $DB_SERVER;
        $serverName = "75.103.66.7";
//        $serverName = "localhost";
//        $userName = "root";
//        $userPassword = "";

        $userName = "spotcrow_admin";
        $userPassword = "spotcrow_adminP1!";
        $databaseName = "spotcrow_db";
        $this->m_connectionHandle = mysql_connect($serverName, $userName, $userPassword);
        mysql_select_db($databaseName);

        $this->InfoMessages(__METHOD__, "DB Connection " . $this->m_connectionHandle);
    }


    function ExecuteQuery($m_sql, $mode = 0)
    {
        $value_pairs = array();
        $m_result = mysql_query($m_sql, $this->m_connectionHandle);

        $this->m_data_rows = array();
        $this->m_row_count = 0;
        $this->m_col_count = 0;
        if ($m_result) {
            if ($mode) //select
            {
                $this->m_row_count = mysql_num_rows($m_result);
                $this->m_col_count = mysql_num_fields($m_result);
                while ($rowArray = mysql_fetch_row($m_result)) {
                    $rows_data = "";
                    for ($i = 0; $i < $this->m_col_count; $i++) {
                        $fld_name = mysql_field_name($m_result, $i);
                        $values_pairs[$fld_name] = $rowArray[$i];
                        //$this->DisplayLog($fld_name."= ".$rowArray[$i]."<BR>");
                    }
                    $this->m_data_rows[] = $values_pairs;
                }
            } else {
                return mysql_insert_id($this->m_connectionHandle);
            }
            mysql_freeresult($m_result);
        }
    	


    }

    function SelectQuery($m_sql)
    {
        $this->ExecuteQuery($m_sql, 1);
    }

    function CloseDatabase()
    {
        if ($this->m_connectionHandle)
            mysql_close($this->m_connectionHandle);
    }

    function CleanUp()
    {
        $this->CloseDatabase();
        $this->InfoMessages(__METHOD__, "Memory Final " . memory_get_usage());
    }

    function GenerateSingleRow($sqlQuery)
    {
        $this->SelectQuery($sqlQuery);
        if ($this->m_row_count)
            return $this->m_data_rows[0];
    }

    function GenerateSingleValue($sqlQuery, $fldName)
    {
        $rowData = $this->GenerateSingleRow($sqlQuery);
        if ($rowData){
            return $rowData[$fldName];
        }else{
        	return "0";
        }
    }

    function GenerateSingleIntValue($sqlQuery, $fldName)

    {
        $val = $this->GenerateSingleValue($sqlQuery, $fldName);
        return intval($val);
    }

    function GetDisplayValue($table_name, $condition_col, $id_val, $fldName)
    {
        $id_val = intval($id_val);
        $sqlQuery = "Select * from $table_name where $condition_col = $id_val";
        return $this->GenerateSingleValue($sqlQuery, $fldName);
    }

    function GetRowValues($table_name, $condition_col, $id_val)
    {
        $id_val = intval($id_val);
        $sqlQuery = "Select * from $table_name where $condition_col = $id_val";
        return $this->GenerateSingleRow($sqlQuery);
    }

    function GenerateIdList($sqlQuery, $desc, $id, $none = 0)
    {
        $this->SelectQuery($sqlQuery);
        $combo_list = array();

        if ($none)
            $combo_list[] = array("NONE", "");

        for ($i = 0; $i < $this->m_row_count; $i++)
            $combo_list[] = array($this->m_data_rows[$i][$desc], $this->m_data_rows[$i][$id]);

        return $combo_list;
    }

    function AddUpdateDelete($table_name, $flds, $id_col, $delete = 0)
    {
        global $DISPLAY_FIELD, $CHECK_FIELD, $TEXT_AREA, $HTML_TEXT_AREA, $ASSOCIATE_ITEMS;
        global $FIELD_LABEL, $FIELD_TYPE, $FIELD_LENGTH, $FIELD_DATA, $FIELD_DDATA,$DD_VAL_WID_MUL;

        $id_val = $this->GetIntValue("$id_col");

        if ($delete) {
            $sqlQuery = "DELETE FROM $table_name WHERE $id_col = $id_val ";
//            $this->DisplayLog($sqlQuery);
            $this->ExecuteQuery($sqlQuery);
            return $id_val;
        }

        $ins_sqlQuery = "INSERT INTO $table_name  (";
        $values = " VALUES ( ";

        $update_sqlQuery = "Update $table_name SET $id_col = $id_val ";

        foreach ($flds as $field_name => $field_array) {
            if (!($field_array[$FIELD_TYPE] == $DISPLAY_FIELD || $field_array[$FIELD_TYPE] == $ASSOCIATE_ITEMS)) {
                $ins_sqlQuery = $ins_sqlQuery . $field_name . ",";
                $field_val = $this->DecodeUrlStr($field_name);

                if ($field_array[$FIELD_TYPE] == $CHECK_FIELD) //Checkbox assign zero
                $field_val = ($field_val) ? $field_val : "0";

                if ($field_array[$FIELD_TYPE] == $TEXT_AREA || $field_array[$FIELD_TYPE] == $HTML_TEXT_AREA) //TEXTAREA
                $field_val = str_replace("html:textarea", "textarea", $field_val);

                $values = $values . "\"" . $field_val . "\",";
                $update_sqlQuery = $update_sqlQuery . " , $field_name = \"" . $field_val . "\"";
            }
        }

        if ($id_val) {
            $sqlQuery = $update_sqlQuery . " WHERE $id_col = $id_val ";
            $this->ExecuteQuery($sqlQuery);
        } else {
            $sqlQuery = trim($ins_sqlQuery, ',') . ") " . trim($values, ',') . " )";
            $id_val = $this->ExecuteQuery($sqlQuery);
        }

//        $this->DisplayLog($sqlQuery);
        foreach ($flds as $field_name => $field_array) {
            $run_query = 0;
            if ($field_array[$FIELD_TYPE] == $ASSOCIATE_ITEMS || $field_array[$FIELD_TYPE] == $DD_VAL_WID_MUL) {
                $SRC_FLDS = @$field_array[$FIELD_DDATA]["SRC"];
                $TGT_FLDS = @$field_array[$FIELD_DDATA]["TARGET"];

                $where_query = $SRC_FLDS["WHERE"] ? " where " . $SRC_FLDS["WHERE"] : "";
                $sub_query = "select " . $SRC_FLDS["COL_ID"] . " from " . $SRC_FLDS["TABLE"] . " $where_query ";
                $sqlQuery = "delete from " . $TGT_FLDS["TABLE"] . " where " . $TGT_FLDS["REF_ID"] . "=$id_val and " . $TGT_FLDS["COL_ID"] . " in ($sub_query) ";
                $this->ExecuteQuery($sqlQuery);
//                $this->DisplayLog($sqlQuery);


                $val_array = @$_POST[$field_name];

                if (is_array($val_array)) {
                    foreach ($val_array as $item_id) {
                        $sqlQuery = "INSERT INTO " . $TGT_FLDS["TABLE"] . " (" . $TGT_FLDS["COL_ID"] . ", " . $TGT_FLDS["REF_ID"] . " ) VALUES ( $item_id,$id_val) ";
                        $this->ExecuteQuery($sqlQuery);
//                        $this->DisplayLog($sqlQuery);
                    }
                }
            }
        }
//                        $this->DisplayLog($sqlQuery);

        return $id_val;
    }

    function GetLastInsertId()
    {
        return mysql_insert_id($this->m_connectionHandle);
    }

}


?>