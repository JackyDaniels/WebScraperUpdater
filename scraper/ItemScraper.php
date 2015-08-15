<?php


require_once 'HTMLDOMParser.php';

class ItemScraper {

    public function extractNumericNameValueFields($item) {
        
        
        $item = "Price";
        if (isset($this->_scraped_items_map[$item])) {
            $str = $this->_scraped_items_map[$item];
            preg_match_all('/\d+/', $str, $matches);
            if (isset($matches[0]) && isset($matches[0][0])) {
                $item_value = strval($matches[0][0]);
                $this->_scraped_items_map[$item] = $item_value;
            }
        }

    }

    public function extractTitle() {
        $item = "Title";
        $this->storeAmazonField($item);
    }
    
    
    public function extractUrl() {
        $item = "DetailPageURL";
        $this->storeAmazonField($item);
    }
    
    
    public function extractImage() {
        $item = "MediumImage->URL";
        $this->storeAmazonField($item);
    }

    public function extractASIN() {
        $item = "ASIN";
        $this->storeAmazonField($item);
        
    }
    
    
    public function extractPrice() {
        $item = "OfferSummary->LowestNewPrice->Amount";
        $this->storeAmazonField($item);
    }

    public function extractNewPrice() {
        $item = "OfferSummary->LowestNewPrice->Amount";
        $this->storeAmazonField($item);
    }
    
    public function extractUsedPrice() {
        $item = "OfferSummary->LowestUsedPrice->Amount";
        $this->storeAmazonField($item);
    }
    
    
    public function extractCode() {
        $item = "OfferSummary->LowestNewPrice->CurrencyCode";
        $this->storeAmazonField($item);
    }

    
    public function extractQuantity() {
        $item = "OfferSummary->TotalNew";
        $this->storeAmazonField($item);
    }

    
    
    
    public function storeAmazonField($item) {
        $attribute_name = "attribute";
        $this->_scraped_items_map[$item] = $item;
        $value = $this->extractAttributeFields("attribute", $item, "span");
        $this->_storeSemanticMap("scraped_items_map",$attribute_name , $item);
        
        
    }

    public function extractAttributeFields($attribute_name, $attribute_value, $html_el_type) {
        $ext_pattern_match = $html_el_type . "[" . $attribute_name . "=\"" . $attribute_value . "\"";
        foreach ($this->_html_content->find($ext_pattern_match) as $d) {
            $value = trim($d->plaintext);
        }

        return $value;
    }
    
    
    public function extractAddress() {
        $attribute_name = "Address";
        $this->_scraped_items_map[$item] = $item;
        $address = $this->extractAttributeFields("attribute", $item, "div");
        $ad_delim = "<br>";
        $address_map = $this->_parseAddress($address, $ad_delim);
    }

    
    
    }