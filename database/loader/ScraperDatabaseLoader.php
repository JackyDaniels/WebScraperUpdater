<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ScraperDatabaseLoader {

    function __construct() {
        (new ScraperDatabaseUtils())->_initializeDBSettings();
    }

    public function _selectRecords($sql, $attribute_list) {
        $id_result_table = NULL;
        if ($this->_DB_instance != NULL) {
            $result = $this->_DB_instance->query($sql);
            if ($result != NULL && $result->num_rows > 0) {
                $id_result_table = array();

                $id_result_row = array();
                while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
                    foreach ($attribute_list as $attribute) {
                        $attribute_val = $row[$attribute];
                        $id_result_row[$attribute] = $attribute_val;
                    }
                    $id_result_table[] = $id_result_row;
                }
            } else {
                if ($this->DB_DEBUG)
                    echo "0 results" . PHP_EOL;
            }
        }
        return $id_result_table;
    }

    public function _insertRecord($sql) {
        if ($this->_SQL_OUT)
            echo $sql . PHP_EOL;
        $result = NULL;
        if ($this->_DB_instance != NULL) {
            $result = $this->_DB_instance->query($sql);

            if ($result === TRUE) {
                $result = array();
                if ($this->DB_DEBUG)
                    echo "New record created successfully" . PHP_EOL;
                $result['insert_id'] = $this->_DB_instance->insert_id;
            } else {
                if ($this->DB_DEBUG)
                    echo PHP_EOL . "Error: " . $sql . "<br>" . $this->_DB_instance->error;
            }
        }
        return $result;
    }

}
