<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ScraperUtils {

    private function _shouldParseURL($url) {
        // Enforce any constraints or filter-out URLs

        return true;
    }

    private function _parseAddress($address, $ad_delim) {

        $num_brs = 0;
        $ADD_DEBUG = FALSE;
        $address = str_replace("&nbsp;", " ", $address);
        $address = trim($address);

        if ($ad_delim === "<br>") {
            if (!preg_match("/<br>/", $address)) {
                $address = str_replace("-", "<br>", $address);
            }
            $matches = preg_split("/<br>/", $address);
            if (count($matches) > $num_brs) {
                $num_brs = count($matches);
            }
        }
        if ($ad_delim === "<br/>") {
            if (!preg_match("/<br\/>/", $address)) {
                $address = str_replace("-", "<br/>", $address);
            }
            $matches = preg_split("/<br\/>/", $address);
            if (count($matches) > $num_brs) {
                $num_brs = count($matches);
            }
        }

        foreach ($matches as $key => $val) {
            $matches[$key] = trim($matches[$key]);
        }


        $address_map = array();

        if ($matches != null && count($matches) > 0) {
            foreach ($matches as $key => $val) {
                $regex = "/\d\d\d\d\ [a-zA-Z]+/";
                if (preg_match($regex, $val)) { // "June 24"
                    $matches_3 = preg_split("/,/", $val);
                    if ($matches_3 != null && count($matches_3) > 0) {
                        if (isset($matches_3[0])) {
                            $matches_1 = preg_split("/\s+/", $matches_3[0]);
                            if ($matches_1 != null && count($matches_1) > 1) {

                                $city = "";
                                for ($k = 1; $k < count($matches_1); $k++) {
                                    $city .= $matches_1[$k] . " ";
                                }
                                $city = trim($city);
                                if (!isset($address_map['city'])) {
                                    $address_map['city'] = trim($city);
                                }
                                if ($ADD_DEBUG)
                                    echo "\tCity : " . $city . PHP_EOL;
                            }
                        }

                        if (isset($matches_3[1])) {
                            $district = trim($matches_3[1]);
                        }
                    }
                    if ($key - 1 >= 0) {
                        if ($ADD_DEBUG)
                            echo "\tStreet : " . $matches[$key - 1];
                        $address_map['street'] = $matches[$key - 1];
                        if ($ADD_DEBUG)
                            echo "\t ( EXISTS )" . PHP_EOL;
                    } else {
                        if ($ADD_DEBUG)
                            echo "\tStreet : NOT EXISTS" . PHP_EOL;
                    }

                    if ($key < count($matches) - 1) {
                        if ($ADD_DEBUG)
                            echo "\t ( EXISTS )" . PHP_EOL;
                    } else {
                        if (isset($address_map['zip'])) {
                            if ($ADD_DEBUG)
                                echo "\t" . $address_map['zip'] . " ( EXISTS ) " . PHP_EOL;
                        } else {
                            if ($ADD_DEBUG)
                                echo "\Zip : NOT EXISTS" . PHP_EOL;
                        }
                    }
                }
                $regex = "/\d+.\s+[a-zA-Z]+/";
                if (preg_match($regex, $val)) {
                    $matches_2 = preg_split("/,/", $val);

                    if ($matches_2 != null && count($matches_2) > 1) {
                        if (isset($matches_2[1])) {
                            
                        }
                        if (isset($matches_2[2])) {
                            $zip = trim($matches_2[2]);
                            if ($ADD_DEBUG)
                                echo "\Zip : " . $zip . PHP_EOL;
                            if (!isset($address_map['zip'])) {
                                $address_map['zip'] = $zip;
                            }
                        }
                    }
                }
            }
        }

        return $address_map;
    }

}
