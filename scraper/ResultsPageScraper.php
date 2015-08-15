<?php

require_once 'PHPDOMParser.php';

class ResultsPageScraper {

    private $_website_url;
    private $_name = "AmazonProductResultsPage";
    private $_website_filename;
    private $_html_content = NULL;
    private $_referral_links = NULL;
    private $_access_mode = "local"; // "remote";
    private $domain_name = 'www.amazon.com';
    public $SC_DEBUG = FALSE;
    private $_referral_key;
    private $_extracted_db_map = array(); // has a reference to the collected list of items in each page and is used for inserting or updating the database periodically

    function __construct() {
        
    }

    function setWebsiteFileName($website_filename) {
        $this->_website_filename = $website_filename;
    }

    function setSearchPageUrl($website_url) {
        $this->_website_url = $website_url;
    }

    function getName() {
        return $this->_name;
    }

    function _loadHTMLContent() {
        try {
            if ($this->_html_content == NULL && $this->_website_filename != NULL) {
                $this->_html_content = file_get_html($this->_website_filename);
                if ($this->SC_DEBUG)
                    echo "Search Results Page Processing - File : " . $this->_website_filename . PHP_EOL;
            }
            if ($this->_html_content == NULL && $this->_website_url != NULL) {
                $this->_html_content = file_get_contents($this->_website_url); // can be replaced with file_get_html()
                if ($this->SC_DEBUG)
                    echo "Search Results Page Processing - Url : " . $this->_website_url . PHP_EOL;
            }
            $this->_html_content = iconv("ISO-8859-1", "UTF-8", $this->_html_content);
            $this->_html_content = str_get_html($this->_html_content);
        } catch (Exception $e) {
            if ($this->SC_DEBUG)
                echo 'Caught exception: ', $e->getMessage(), "\n";
        }
    }

    function getAllLinkReferrals() {
        $this->_loadHTMLContent();
        if ($this->_html_content != NULL) {
            $links = array();
            foreach ($this->_html_content->find('a') as $a) {
                $url = trim($a->href);
                if (!isset($links[$url])) {
                    $links[$url] = $url;
                }
            }
            if ($this->SC_DEBUG)
                print_r($links);
        }
        return $links;
    }

    function getScrapeReferrals() {
        $this->_loadHTMLContent();
        if ($this->_html_content != NULL) {
            $this->_referral_links = array();
            foreach ($this->_html_content->find('a[az_url="url"]') as $a) {
                $url = trim($a->href);

                
                if ($this->_access_mode !== "local") {
                    if (substr($url, 0, strlen($domain_name)) !== $domain_name) {
                        $url = $domain_name . $url;
                    }
                }
                if ($this->_shouldParseURL($url)) {
                    list($uri, $url_params) = preg_split("/\?/", $url);
                    parse_str($url_params, $output);
                    if ( isset($output['id'])) {
                        $id = $output['id'];
                        if (!isset($this->_referral_links[$id])) {
                            $this->_referral_links[$id] = $url;
                        }
                    }
                }
            }
            if ($this->SC_DEBUG)
                print_r($this->_referral_links);
        }
        return $this->_referral_links;
    }

    function getAllImageReferrals() {
        $this->_loadHTMLContent();
        $images = array();
        if ($this->_html_content != NULL) {
            foreach ($this->_html_content->find('img') as $img) {
                $url = trim($img->src);
                if (!isset($images[$url])) {
                    $images[$url] = $url;
                }
            }
            if ($this->SC_DEBUG)
                print_r(array_keys($images));
        }
        return array_keys($images);
    }


}

