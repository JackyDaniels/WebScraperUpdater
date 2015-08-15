<?php

class ScraperDatabaseManager {

    private $_DB_instance;
    private $_DB_mode = "local"; // OPTIONS : "local" | "remote";
    private $_access_mode = "local";
    private $_DB_SERVER;
    private $_DB_USERNAME;
    private $_DB_PASSWORD;
    private $_DATABASE;
    private $_DB_ACCESS = TRUE;
    public $DB_DEBUG = FALSE;
    private $_ScrapedIDsSeen = array();
    private $_SQL_OUT = TRUE;

    // Check database connection locally and remotely
    function __construct() {
        (new ScraperDatabaseUtils())->_initializeDBSettings();
        //--- $this->_checkDBConnection();
    }

    public function updateReferredDomainsToScrape($domain_id, $scraped_id_referrals) {
        $sql = array();
        (new ScraperDatabaseUtils())->_openDBInstance();

        $database_name = $this->_DATABASE;
        (new ScraperDatabaseUtils())->_connectToDatabase($database_name);
        foreach (array_keys($scraped_id_referrals) as $scraped_id) {
            if (!isset($this->_ScrapedIDsSeen[$scraped_id])) {
                $sql[] = '(' . $domain_id . ',"' . $this->_DB_instance->real_escape_string($scraped_id_referrals[$scraped_id]) . '")';
                $simple_sql = 'INSERT INTO `' . $this->_DATABASE . '`.`' . 'amz_products` (referred_page_id, url) VALUES ' . '(' . $domain_id . ',"' . $this->_DB_instance->real_escape_string($scraped_id_referrals[$scraped_id]) . '");';
                if ($this->_SQL_OUT)
                    echo $simple_sql . PHP_EOL;
                $this->_ScrapedIDsSeen[$scraped_id] = 1;
                $this->_DB_instance->query($simple_sql);
            }
        }
        (new ScraperDatabaseUtils())->_closeDBInstance();
    }

    public function getReferredDomainsToScrape($domain_name) {
        $urls_to_scrape = array();
        (new ScraperDatabaseUtils())->_openDBInstance();
        $sql = 'select * from amz_urls;';
        if ($this->DB_DEBUG)
            echo $sql . PHP_EOL;
        $database_name = $this->_DATABASE;
        (new ScraperDatabaseUtils())->_connectToDatabase($database_name);
        $id_result = NULL;
        if ($this->_DB_instance != NULL) {
            $result = $this->_DB_instance->query($sql);

            if ($result != NULL && $result->num_rows > 0) {
                if ($this->DB_DEBUG)
                    print_r($result);
                $id_result = array();
                $url_count = 0;
                while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
                    $urls_to_scrape[$url_count] = $row['url'];
                    $url_count++;
                }
            } else {
                if ($this->DB_DEBUG)
                    echo "0 results" . PHP_EOL;
            }
        }
        (new ScraperDatabaseUtils())->_closeDBInstance();



        $urls_to_scrape = array();
        $url_count = 0;

        $scanned_directory = array_diff(scandir($directory), array('..', '.'));
        foreach ($scanned_directory as $filename) {

            $filename = $directory . '/' . $filename;
            if (file_exists($filename)) {
                $urls_to_scrape[$url_count] = $filename;
                $url_count++;
            } else {
                echo "File " . $filename . " does not exist" . PHP_EOL;
            }
        }



        if ($this->DB_DEBUG)
            print_r($urls_to_scrape);

        return $urls_to_scrape;
    }

    public function updateScrapedItemDetails($objects, $primary_key) {
        $result = NULL;
        (new ScraperDatabaseUtils())->_openDBInstance();

        if ($this->_DB_instance != NULL) {
            $database_name = $this->_DATABASE;
            $table_name = "objects";
            $forced_insert = FALSE;
            $table_attribute_map_list = array();
            $match_attributes_list = array();
            foreach (array_keys($objects) as $obj_attribute) {
                $table_attribute_map_list[$obj_attribute] = '"' . $objects[$obj_attribute] . '"';
            }

            $result = $this->_doGroupingInsertOrUpdate($database_name, $table_name, $primary_key, $table_attribute_map_list, $match_attributes_list, $forced_insert, $result);
        }
        (new ScraperDatabaseUtils())->_closeDBInstance();
        return $result;
    }

    private function _doLoadOrUpdate($database_name, $table_name, $primary_key, $table_attribute_map_list, $match_attributes_list, $forced_insert, $result) {

        // $table_attribute_map_list = $this->_formatDataType($table_attribute_map_list);

        $total_attributes = count($table_attribute_map_list);
        if ($total_attributes > 0) {
            $sql = 'SELECT * from ' . $table_name . ' as T';
            $attribute_count = 1;
            $and_tag = "";
            foreach ($match_attributes_list as $c_attribute_crit) {
                $c_attribute = "";
                if (isset($c_attribute_crit['attribute'])) {
                    $c_attribute = $c_attribute_crit['attribute'];
                }
                $attr_match_type = "exact";
                if (isset($c_attribute_crit['match_type'])) {
                    $attr_match_type = $c_attribute_crit['match_type'];
                }

                if (isset($table_attribute_map_list[$c_attribute])) {
                    // echo "C_attrib : " . $c_attribute . PHP_EOL;

                    if ($attribute_count == 1) {
                        $sql .= " where ";
                    }

                    if ($attribute_count != 1) {
                        $and_tag = " and ";
                    }

                    $exists_in_db = FALSE;
                    if (isset($c_attribute_crit['exists_in_db'])) {
                        $exists_in_db = $c_attribute_crit['exists_in_db'];
                    }

                    $sql .= $and_tag;
                    if ($attr_match_type === "exact") {
                        if ($exists_in_db) {
                            $sql .= ' ( T.' . $c_attribute . ' != NULL and ';
                            $sql .= ' T.' . $c_attribute . ' = ' . $table_attribute_map_list[$c_attribute];
                            $sql .= ' ) ';
                        } else {
                            $sql .= ' T.' . $c_attribute . ' = ' . $table_attribute_map_list[$c_attribute];
                        }
                    } else {
                        $lb_offset_set = FALSE;
                        if (isset($c_attribute_crit['lb_offset'])) {
                            $lb_offset = $c_attribute_crit['lb_offset'];
                            if ($exists_in_db) {
                                $sql .= ' ( T.' . $c_attribute . ' != NULL and ';
                                $sql .= ' ( ( T.' . $c_attribute . ' - ' . $lb_offset . ' ) <= ' . $table_attribute_map_list[$c_attribute] . ' )';
                                $sql .= ' )';
                            } else {
                                $sql .= ' ( ( T.' . $c_attribute . ' - ' . $lb_offset . ' ) <= ' . $table_attribute_map_list[$c_attribute] . ' )';
                            }
                            $lb_offset_set = TRUE;
                        }

                        if (isset($c_attribute_crit['ub_offset'])) {
                            if ($lb_offset_set) {
                                $sql .= ' and ';
                            }

                            $ub_offset = $c_attribute_crit['ub_offset'];
                            // range
                            if ($exists_in_db) {
                                $sql .= ' ( T.' . $c_attribute . ' != NULL and ';
                                $sql .= '( ( T.' . $c_attribute . ' + ' . $ub_offset . ' ) >= ' . $table_attribute_map_list[$c_attribute] . ' )';
                                $sql .= ' )';
                            } else {
                                $sql .= '( ( T.' . $c_attribute . ' + ' . $ub_offset . ' ) >= ' . $table_attribute_map_list[$c_attribute] . ' )';
                            }
                        }
                    }
                }

                $attribute_count ++;
            }


            $sql .= ';';
            if ($this->DB_DEBUG)
                echo 'Check SQL :' . $sql . PHP_EOL;

            (new ScraperDatabaseUtils())->_connectToDatabase($database_name);
            $attribute_list = array();
            $attribute = $primary_key;
            $attribute_list[$attribute] = $attribute;
            $attribute_list['title'] = 'title';
            $id_result_table = (new ScraperDatabaseLoader())->_selectRecords($sql, $attribute_list);

            if ($this->DB_DEBUG)
                print_r($id_result_table);

            if ($id_result_table != NULL && count($id_result_table) > 0) {
                if ($result == NULL) {
                    $result = array();
                }
                if ($this->DB_DEBUG)
                    echo 'There already exists ' . count($id_result_table) . ' record rows in ' . $table_name . ' table. Not creating a new record. ' . PHP_EOL;

                $UPDATE_REQUIRED = FALSE;

                // check here for which fields have changed.
                $num_matched_rows = 0;
                foreach ($id_result_table as $id_row) {
                    $id = $id_row[$attribute];

                    if ($UPDATE_REQUIRED) {

                        $update_field_list = (new ScraperDatabaseUpdater())->_compareFieldsToUpdate($table_name, $primary_key, $id, $table_attribute_map_list);
                        if ($update_field_list) {
                            echo $table_name . " - Update Required because some fields changed" . PHP_EOL;
                            $sql = 'UPDATE `' . $this->_DATABASE . "`.`" . $table_name . '`';
                            $attribute_count = 0;
                            $i = 0;
                            foreach (array_keys($table_attribute_map_list) as $i_attribute) {
                                if ($attribute_count == 0) {
                                    $sql .= ' SET ' . $i_attribute . ' = ' . $table_attribute_map_list[$i_attribute];
                                } else {
                                    $sql .= ' , ' . $i_attribute . ' = ' . $table_attribute_map_list[$i_attribute];
                                }

                                $attribute_count ++;
                            }
                            $sql .= ' where ' . $primary_key . ' = ' . $id . ';';

                            if ($this->DB_DEBUG)
                                echo 'Update SQL :' . $sql . PHP_EOL;
                            $table_operation = 'update';

                            // If anything was set, then update.
                            if ($attribute_count > 0) {
                                (new ScraperDatabaseUpdater())->_updateRecord($sql);
                                $table_operation = 'update';
                                $op_key = $table_operation;
                                if (!isset($result[$table_name][$op_key])) {
                                    $result[$table_name][$op_key] = 1;
                                } else {
                                    $result[$table_name][$op_key] += 1;
                                }
                            }
                        } else {
                            echo $table_name . " - Update NOT Required because none of the fields changed" . PHP_EOL;
                        }
                    }

                    $result[$primary_key] = $id;
                    if ($num_matched_rows == 0) {
                        break;
                    }
                    $num_matched_rows ++;
                }
            } else { {

                    if ($this->DB_DEBUG)
                        echo 'No records in ' . $table_name . PHP_EOL;

                    $sql = 'INSERT into `' . $this->_DATABASE . "`.`" . $table_name . '`( ';
                    $attribute_count = 1;
                    foreach (array_keys($table_attribute_map_list) as $i_attribute) {
                        if ($attribute_count == 1) {
                            $sql .= $i_attribute;
                        } else {
                            $sql .= ',' . $i_attribute;
                        }

                        $attribute_count ++;
                    }
                    $sql .= ')';

                    $sql .= 'values(';
                    $attribute_count = 1;
                    foreach ($table_attribute_map_list as $i_attribute) {
                        if ($attribute_count == 1) {
                            $sql .= $i_attribute;
                        } else {
                            $sql .= ',' . $i_attribute;
                        }

                        $attribute_count ++;
                    }
                    $sql .= ');';

                    if ($this->DB_DEBUG)
                        echo 'Insert SQL :' . $sql . PHP_EOL;

                    $ins_result = (new ScraperDatabaseLoader())->_insertRecord($sql);
                    if ($this->DB_DEBUG)
                        print_r($ins_result);
                    if ($ins_result != NULL && isset($ins_result['insert_id'])) {
                        $table_operation = 'insert';
                        $op_key = $table_operation; // $table_name . '_' . $table_operation;
                        if (!isset($result[$table_name][$op_key])) {
                            $result[$table_name][$op_key] = 1;
                        } else {
                            $result[$table_name][$op_key] += 1;
                        }

                        $result[$primary_key] = $ins_result['insert_id'];
                    }
                }
            }
        }

        return $result;
    }

}
