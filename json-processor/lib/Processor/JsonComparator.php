<?php
namespace App\Processor;

class JsonComparator extends BaseProcessor {
    private $data1;
    private $data2;
    
    public function __construct($data1, $data2) {
        $this->data1 = $data1;
        $this->data2 = $data2;
    }
    
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
        
        // Check key format consistency
        $format1 = $this->detectKeyFormat($this->data1);
        $format2 = $this->detectKeyFormat($this->data2);
        
        if ($format1 === 'mixed' || $format2 === 'mixed' || $format1 !== $format2) {
            $suggestions[] = [
                'type' => 'format',
                'message' => 'Inconsistent key formats detected',
                'formats' => [
                    'dataset1' => $format1,
                    'dataset2' => $format2
                ]
            ];
        }
        
        return $suggestions;
    }
    
    private function hasDuplicates($data) {
        if (!is_array($data)) {
            return false;
        }
        
        $seen = [];
        foreach ($data as $item) {
            if (!is_array($item)) {
                continue;
            }
            
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
    
    public function findCommonFields() {
        $fields1 = $this->extractFields($this->data1);
        $fields2 = $this->extractFields($this->data2);
        
        return array_intersect($fields1, $fields2);
    }
    
    private function extractFields($data, $prefix = '') {
        $fields = [];
        
        foreach ($data as $key => $value) {
            $fullKey = $prefix ? "$prefix.$key" : $key;
            $fields[] = $fullKey;
            
            if (is_array($value)) {
                $fields = array_merge($fields, $this->extractFields($value, $fullKey));
            }
        }
        
        return $fields;
    }
    
    public function compareStructures() {
        return [
            'common_fields' => $this->findCommonFields(),
            'data1_only' => array_diff($this->extractFields($this->data1), $this->extractFields($this->data2)),
            'data2_only' => array_diff($this->extractFields($this->data2), $this->extractFields($this->data1))
        ];
    }
} 