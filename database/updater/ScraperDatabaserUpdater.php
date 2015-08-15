<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ScraperDatabaseUpdater {

    function __construct() {
        (new ScraperDatabaseUtils())->_initializeDBSettings();
    }

    public function _updateRecord($sql) {
        if ($this->_SQL_OUT)
            echo $sql . PHP_EOL;
        $result = NULL;
        if ($this->_DB_instance != NULL) {
            $result = $this->_DB_instance->query($sql);

            if ($result === TRUE) {
                if ($this->DB_DEBUG)
                    echo "Record updated successfully" . PHP_EOL;
            } else {
                if ($this->DB_DEBUG)
                    echo "Error updating record : " . $sql . "<br>" . $this->_DB_instance->error;
            }
        }
        return $result;
    }

    public function _compareNewerFieldsToUpdate($table_name, $primary_key, $id, $match_attribute_map_list) {
        $update_after_comparing = FALSE;
        $MAP_DEBUG = FALSE;



        if ($MAP_DEBUG) {
            echo "Printing the attribute-map list" . PHP_EOL;
            foreach ($match_attribute_map_list as $m_key => $m_val) {
                echo $m_key . " => " . $m_val . PHP_EOL;
            }
        }

        $total_attributes = count($match_attribute_map_list);
        $sql = 'SELECT * from ' . $table_name . ' as T where T.' . $primary_key . ' = ' . $id; 
        $sql .= ';';
        if ($this->DB_DEBUG)
            echo 'Update Compare SQL :' . $sql . PHP_EOL;

        $id_result_table = NULL;
        if ($this->_DB_instance != NULL) {
            $result = $this->_DB_instance->query($sql);
            if ($result != NULL && $result->num_rows > 0) {
                while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
                    foreach ($row as $attr_name => $attr_val) {

                        if ($attr_name !== $primary_key && !$this->_isDateTypeField($attr_name)) {
                            $match_val = null;
                            if (isset($match_attribute_map_list[$attr_name])) {
                                $match_val = $match_attribute_map_list[$attr_name];
                            } else {
                                $match_val = null;
                            }
                            if ($attr_val != null) {
                                $attr_val = "\"" . $attr_val . "\"";
                            }

                            if ($attr_val !== $match_val) {
                                if ($MAP_DEBUG) {
                                    echo "[ DIFFERENT ]";
                                }
                                $update_after_comparing = TRUE;
                            } else {
                                if ($MAP_DEBUG) {
                                    echo "[    SAME   ]";
                                }
                                // $update_after_comparing = TRUE;
                            }
                            if ($MAP_DEBUG) {
                                echo "[" . $attr_name . "] => (" . $attr_val . ")   /  (" . $match_val . ")" . PHP_EOL;
                            }
                        }
                    }
                }
            } else {
                $update_after_comparing = FALSE;
            }
        }

        return $update_after_comparing;
    }

}
