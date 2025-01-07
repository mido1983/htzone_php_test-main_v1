<?php
class JsonCleaner {
    private $data;
    
    public function __construct($data) {
        $this->data = $data;
    }
    
    /**
     * Remove duplicate records based on specified keys
     */
    public function removeDuplicates($keys = ['id']) {
        if (!is_array($this->data)) {
            return $this->data;
        }
        
        $unique = [];
        $seen = [];
        
        foreach ($this->data as $item) {
            $hash = $this->generateHash($item, $keys);
            if (!isset($seen[$hash])) {
                $seen[$hash] = true;
                $unique[] = $item;
            }
        }
        
        return $unique;
    }
    
    /**
     * Format keys to consistent style (camelCase, snake_case, etc.)
     */
    public function formatKeys($format = 'snake') {
        if (!is_array($this->data)) {
            return $this->data;
        }
        
        return $this->recursiveKeyFormat($this->data, $format);
    }
    
    /**
     * Remove empty values from array
     */
    public function removeEmpty() {
        if (!is_array($this->data)) {
            return $this->data;
        }
        
        return array_filter($this->data, function($value) {
            if (is_array($value)) {
                return !empty($this->removeEmpty($value));
            }
            return $value !== null && $value !== '';
        });
    }
    
    private function generateHash($item, $keys) {
        $values = [];
        foreach ($keys as $key) {
            $values[] = isset($item[$key]) ? $item[$key] : '';
        }
        return md5(json_encode($values));
    }
    
    private function recursiveKeyFormat($array, $format) {
        $result = [];
        foreach ($array as $key => $value) {
            $newKey = $this->formatKey($key, $format);
            $result[$newKey] = is_array($value) ? 
                              $this->recursiveKeyFormat($value, $format) : 
                              $value;
        }
        return $result;
    }
    
    private function formatKey($key, $format) {
        switch ($format) {
            case 'camel':
                return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $key))));
            case 'snake':
                return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $key));
            default:
                return $key;
        }
    }
}