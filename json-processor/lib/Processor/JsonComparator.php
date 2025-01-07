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
        
        // Check for structure differences
        $structureDiff = $this->compareStructures();
        if (!empty($structureDiff['data1_only']) || !empty($structureDiff['data2_only'])) {
            $suggestions[] = [
                'type' => 'structure',
                'message' => 'Different data structures detected',
                'details' => [
                    'missing_in_data2' => array_values($structureDiff['data1_only']),
                    'missing_in_data1' => array_values($structureDiff['data2_only'])
                ]
            ];
        }
        
        // Check for duplicate values in common fields
        $commonFields = $structureDiff['common_fields'];
        foreach ($commonFields as $field) {
            $values1 = $this->extractValues($this->data1, $field);
            $values2 = $this->extractValues($this->data2, $field);
            
            if ($this->hasDuplicateValues($values1)) {
                $suggestions[] = [
                    'type' => 'duplicates',
                    'message' => "Duplicate values found for field '$field' in dataset 1",
                    'dataset' => 1,
                    'field' => $field
                ];
            }
            
            if ($this->hasDuplicateValues($values2)) {
                $suggestions[] = [
                    'type' => 'duplicates',
                    'message' => "Duplicate values found for field '$field' in dataset 2",
                    'dataset' => 2,
                    'field' => $field
                ];
            }
        }
        
        // Check key format consistency
        $format1 = $this->detectKeyFormat($this->data1);
        $format2 = $this->detectKeyFormat($this->data2);
        
        if ($format1 !== $format2 || $format1 === 'mixed' || $format2 === 'mixed') {
            $suggestions[] = [
                'type' => 'format',
                'message' => 'Inconsistent key formats detected',
                'formats' => [
                    'dataset1' => $format1,
                    'dataset2' => $format2
                ]
            ];
        }
        
        // Check for null or empty values
        $nullValues = $this->findNullValues($this->data1, $this->data2);
        if (!empty($nullValues)) {
            $suggestions[] = [
                'type' => 'null_values',
                'message' => 'Null or empty values found in important fields',
                'fields' => $nullValues
            ];
        }
        
        return $suggestions;
    }
    
    private function extractValues($data, $field) {
        if (!is_array($data)) {
            return [];
        }
        
        $values = [];
        if (isset($data[$field])) {
            $values[] = $data[$field];
        }
        
        foreach ($data as $value) {
            if (is_array($value)) {
                $values = array_merge($values, $this->extractValues($value, $field));
            }
        }
        
        return $values;
    }
    
    private function hasDuplicateValues($values) {
        return count($values) !== count(array_unique($values));
    }
    
    private function findNullValues($data1, $data2) {
        $nullFields = [];
        $importantFields = ['id', 'title', 'category_id', 'price'];
        
        foreach ($importantFields as $field) {
            if ($this->hasNullOrEmpty($data1, $field)) {
                $nullFields[] = ['field' => $field, 'dataset' => 1];
            }
            if ($this->hasNullOrEmpty($data2, $field)) {
                $nullFields[] = ['field' => $field, 'dataset' => 2];
            }
        }
        
        return $nullFields;
    }
    
    private function hasNullOrEmpty($data, $field) {
        if (!is_array($data)) {
            return false;
        }
        
        if (isset($data[$field])) {
            return $data[$field] === null || $data[$field] === '';
        }
        
        foreach ($data as $value) {
            if (is_array($value) && $this->hasNullOrEmpty($value, $field)) {
                return true;
            }
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
        $fields1 = $this->extractFields($this->data1);
        $fields2 = $this->extractFields($this->data2);
        
        return [
            'common_fields' => array_intersect($fields1, $fields2),
            'data1_only' => array_diff($fields1, $fields2),
            'data2_only' => array_diff($fields2, $fields1)
        ];
    }
} 