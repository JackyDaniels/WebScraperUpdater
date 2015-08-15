<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ScraperDatabaseUtils {

    public function _initializeDBSettings() {
            $this->_DB_SERVER = "<YOUR DATABASE SERVER>";
            $this->_DB_USERNAME = "<YOUR DATABASE USERNAME>";
            $this->_DB_PASSWORD = "<YOUR DATABASE PASSWORD>";
            $this->_DATABASE = "<YOUR DATABASE>";
    }

    public function _checkDBConnection() {
        $this->_openDBInstance();
        $this->_closeDBInstance();
    }

    public function _openDBInstance() {
        if ($this->_DB_instance == NULL) {
            // Create connection
            $this->_DB_instance = new mysqli(
                    $this->_DB_SERVER, $this->_DB_USERNAME, $this->_DB_PASSWORD);
        }
        if ($this->_DB_instance != NULL) {
            if ($this->DB_DEBUG)
                echo "Database connection object created " . PHP_EOL;
            // Check connection
            if ($this->_DB_instance->connect_error) {
                $this->_DB_ACCESS = FALSE;
                die("Connection failed: " . $this->_DB_instance->connect_error);
            } else {
                if ($this->DB_DEBUG)
                    echo "Connected successfully" . PHP_EOL;
                $this->_DB_instance->query("SET NAMES 'utf8'");
                $this->_DB_instance->set_charset('utf8');

                if (!$this->_DB_instance->set_charset('utf8')) {
                    printf("Error loading character set utf8: %s\n", $this->_DB_instance->error);
                } else {
                    printf("Current character set: %s\n", $this->_DB_instance->character_set_name());
                }
            }
        } else {
            if ($this->DB_DEBUG)
                echo "Unable to created Database connection object " . PHP_EOL;
        }
    }

    public function _closeDBInstance() {
        if ($this->_DB_instance != NULL) {
            if (is_resource($this->_DB_instance)) {
                $this->_DB_instance->close();
            }
            if ($this->DB_DEBUG)
                echo "Closing database connection" . PHP_EOL;
        } else {
            if ($this->DB_DEBUG)
                echo "No database connection to close" . PHP_EOL;
        }
    }

    public function _connectToDatabase($database_name) {
        $db_selected = FALSE;
        if ($this->_DB_instance != NULL) {
            $db_selected = $this->_DB_instance->select_db($database_name);
            if (!$db_selected) {
                die('Can\'t use ' . $database_name . ' : ' . mysql_error());
            } else {
                if ($this->DB_DEBUG)
                    echo "Connected to database : " . $database_name . PHP_EOL;
            }
        }
        return $db_selected;
    }

}
