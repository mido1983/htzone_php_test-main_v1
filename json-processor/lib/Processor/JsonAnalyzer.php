<?php
namespace App\Processor;

class JsonAnalyzer extends BaseProcessor {
    private $data;
    
    public function __construct($data) {
        $this->data = $data;
    }
    
    public function analyze() {
        $analysis = [
            'structure' => $this->analyzeStructure(),
            'statistics' => $this->calculateStatistics(),
            'issues' => $this->findPotentialIssues()
        ];
        
        return $this->formatResponse($analysis);
    }
    
    private function analyzeStructure($data = null, $path = '') {
        $data = $data ?? $this->data;
        $structure = [];
        
        foreach ($data as $key => $value) {
            $currentPath = $path ? "$path.$key" : $key;
            $type = gettype($value);
            
            if (is_array($value)) {
                $structure[$key] = [
                    'type' => $type,
                    'count' => count($value),
                    'children' => $this->analyzeStructure($value, $currentPath)
                ];
            } else {
                $structure[$key] = [
                    'type' => $type,
                    'path' => $currentPath
                ];
            }
        }
        
        return $structure;
    }
    
    private function calculateStatistics() {
        $stats = [
            'total_keys' => 0,
            'depth' => 0,
            'data_types' => [],
            'array_counts' => []
        ];
        
        $this->traverseData($this->data, $stats);
        
        return $stats;
    }
    
    private function findPotentialIssues() {
        $issues = [];
        
        // Check for inconsistent key naming
        $keyFormats = $this->analyzeKeyFormats();
        if (count($keyFormats) > 1) {
            $issues[] = [
                'type' => 'inconsistent_keys',
                'message' => 'Mixed key naming conventions detected',
                'formats' => $keyFormats
            ];
        }
        
        // Check for potential duplicates
        $duplicates = $this->findDuplicateValues();
        if (!empty($duplicates)) {
            $issues[] = [
                'type' => 'duplicates',
                'message' => 'Potential duplicate values found',
                'duplicates' => $duplicates
            ];
        }
        
        return $issues;
    }
    
    private function traverseData($data, &$stats, $depth = 0) {
        $stats['depth'] = max($stats['depth'], $depth);
        
        foreach ($data as $key => $value) {
            $stats['total_keys']++;
            $type = gettype($value);
            $stats['data_types'][$type] = ($stats['data_types'][$type] ?? 0) + 1;
            
            if (is_array($value)) {
                $count = count($value);
                $stats['array_counts'][] = $count;
                $this->traverseData($value, $stats, $depth + 1);
            }
        }
    }
    
    private function analyzeKeyFormats() {
        $formats = [];
        $this->collectKeyFormats($this->data, $formats);
        return array_keys($formats);
    }
    
    private function collectKeyFormats($data, &$formats) {
        foreach ($data as $key => $value) {
            if (strpos($key, '_') !== false) {
                $formats['snake_case'] = true;
            } elseif (preg_match('/[A-Z]/', $key)) {
                $formats['camelCase'] = true;
            }
            
            if (is_array($value)) {
                $this->collectKeyFormats($value, $formats);
            }
        }
    }
    
    private function findDuplicateValues() {
        $values = [];
        $duplicates = [];
        
        $this->collectValues($this->data, $values, $duplicates);
        
        return $duplicates;
    }
    
    private function collectValues($data, &$values, &$duplicates, $path = '') {
        foreach ($data as $key => $value) {
            $currentPath = $path ? "$path.$key" : $key;
            
            if (is_scalar($value)) {
                $hash = md5(serialize($value));
                if (isset($values[$hash])) {
                    $duplicates[] = [
                        'value' => $value,
                        'paths' => [$values[$hash], $currentPath]
                    ];
                } else {
                    $values[$hash] = $currentPath;
                }
            } elseif (is_array($value)) {
                $this->collectValues($value, $values, $duplicates, $currentPath);
            }
        }
    }
} 