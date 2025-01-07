<?php
class JsonComparator {
    private $data1;
    private $data2;
    
    public function __construct($data1, $data2) {
        $this->data1 = $data1;
        $this->data2 = $data2;
    }
    
    /**
     * Find common fields between two JSON structures
     */
    public function findCommonFields() {
        $fields1 = $this->extractFields($this->data1);
        $fields2 = $this->extractFields($this->data2);
        
        return array_intersect($fields1, $fields2);
    }
    
    /**
     * Compare structures and suggest improvements
     */
    public function suggestImprovements() {
        $suggestions = [];
        
        // Check for duplicate records
        if ($this->hasDuplicates($this->data1)) {
            $suggestions[] = [
                'type' => 'duplicates',
                'message' => 'Found duplicate records in first dataset',
                'dataset' => 1
            ];
        }
        
        if ($this->hasDuplicates($this->data2)) {
            $suggestions[] = [
                'type' => 'duplicates',
                'message' => 'Found duplicate records in second dataset',
                'dataset' => 2
            ];
        }
        
        // Check for inconsistent key formats
        $keyFormat1 = $this->detectKeyFormat($this->data1);
        $keyFormat2 = $this->detectKeyFormat($this->data2);
        
        if ($keyFormat1 !== $keyFormat2) {
            $suggestions[] = [
                'type' => 'format',
                'message' => 'Inconsistent key formats detected between datasets',
                'formats' => ['dataset1' => $keyFormat1, 'dataset2' => $keyFormat2]
            ];
        }
        
        return $suggestions;
    }
    
    private function extractFields($data, $prefix = '') {
        $fields = [];
        
        if (!is_array($data)) {
            return $fields;
        }
        
        foreach ($data as $key => $value) {
            $currentField = $prefix ? "$prefix.$key" : $key;
            $fields[] = $currentField;
            
            if (is_array($value)) {
                $fields = array_merge($fields, $this->extractFields($value, $currentField));
            }
        }
        
        return $fields;
    }
    
    private function hasDuplicates($data) {
        if (!is_array($data)) {
            return false;
        }
        
        $seen = [];
        foreach ($data as $item) {
            $hash = md5(json_encode($item));
            if (isset($seen[$hash])) {
                return true;
            }
            $seen[$hash] = true;
        }
        
        return false;
    }
    
    private function detectKeyFormat($data) {
        if (!is_array($data)) {
            return null;
        }
        
        $camelCount = 0;
        $snakeCount = 0;
        
        foreach ($data as $key => $value) {
            if (strpos($key, '_') !== false) {
                $snakeCount++;
            } elseif (preg_match('/[A-Z]/', $key)) {
                $camelCount++;
            }
            
            if (is_array($value)) {
                $subFormat = $this->detectKeyFormat($value);
                if ($subFormat === 'camel') $camelCount++;
                if ($subFormat === 'snake') $snakeCount++;
            }
        }
        
        if ($camelCount > $snakeCount) return 'camel';
        if ($snakeCount > $camelCount) return 'snake';
        return 'mixed';
    }
}