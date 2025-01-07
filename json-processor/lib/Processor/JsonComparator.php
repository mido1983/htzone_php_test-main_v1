<?php
namespace App\Processor;

class JsonComparator extends BaseProcessor {
    private $data1;
    private $data2;
    
    public function __construct($data1, $data2) {
        $this->data1 = $this->normalizeData($data1);
        $this->data2 = $this->normalizeData($data2);
    }
    
    public function suggestImprovements() {
        $suggestions = [];
        
        // Analyze API Response Structure
        $structureAnalysis = $this->analyzeApiStructure();
        if (!empty($structureAnalysis)) {
            $suggestions[] = [
                'type' => 'api_structure',
                'message' => 'API Response Structure Improvements',
                'details' => $structureAnalysis,
                'example' => $this->generateImprovedApiExample()
            ];
        }
        
        // Check for Consistent Field Naming
        $namingIssues = $this->analyzeFieldNaming();
        if (!empty($namingIssues)) {
            $suggestions[] = [
                'type' => 'field_naming',
                'message' => 'Field Naming Consistency',
                'details' => $namingIssues,
                'example' => $this->generateConsistentNamingExample()
            ];
        }
        
        // Analyze Data Types and Formats
        $dataTypeIssues = $this->analyzeDataTypes();
        if (!empty($dataTypeIssues)) {
            $suggestions[] = [
                'type' => 'data_types',
                'message' => 'Data Type Consistency',
                'details' => $dataTypeIssues,
                'example' => $this->generateConsistentDataTypesExample()
            ];
        }
        
        return $suggestions;
    }
    
    private function normalizeData($data) {
        if (isset($data['data'])) {
            return $data['data'];
        }
        return $data;
    }
    
    private function analyzeApiStructure() {
        $analysis = [];
        
        // Compare categories and items structure
        if (isset($this->data1[0]['category_id']) && isset($this->data2['category_id'])) {
            $analysis[] = [
                'issue' => 'Different Response Formats',
                'description' => 'Categories endpoint returns an array while item endpoint returns a single object',
                'recommendation' => 'Consider using consistent response formats with a data wrapper',
                'current' => [
                    'categories' => 'Array of category objects',
                    'item' => 'Single item object'
                ],
                'suggested' => [
                    'format' => [
                        'data' => 'Object or Array',
                        'meta' => [
                            'total' => 'integer',
                            'page' => 'integer',
                            'per_page' => 'integer'
                        ]
                    ]
                ]
            ];
        }
        
        return $analysis;
    }
    
    private function analyzeFieldNaming() {
        $issues = [];
        
        // Check for inconsistent field naming
        $fields = [
            ['category_id', 'categoryId'],
            ['parent_id', 'parentId'],
            ['title_category', 'category_title'],
            ['sub_category_api_url', 'subcategoryApiUrl']
        ];
        
        foreach ($fields as $fieldVariations) {
            $found = [];
            foreach ($fieldVariations as $field) {
                if ($this->findField($this->data1, $field) || $this->findField($this->data2, $field)) {
                    $found[] = $field;
                }
            }
            
            if (count($found) > 1) {
                $issues[] = [
                    'issue' => 'Inconsistent Field Naming',
                    'fields' => $found,
                    'recommendation' => 'Use consistent naming convention (preferably snake_case)',
                    'example' => $this->generateFieldExample($found[0])
                ];
            }
        }
        
        return $issues;
    }
    
    private function analyzeDataTypes() {
        $issues = [];
        
        // Common fields to check
        $fieldsToCheck = [
            'price' => 'numeric',
            'active' => 'boolean',
            'category_id' => 'integer',
            'expiration_date' => 'datetime'
        ];
        
        foreach ($fieldsToCheck as $field => $expectedType) {
            $value1 = $this->findFieldValue($this->data1, $field);
            $value2 = $this->findFieldValue($this->data2, $field);
            
            if ($value1 !== null || $value2 !== null) {
                $type1 = $this->getValueType($value1);
                $type2 = $this->getValueType($value2);
                
                if ($type1 !== $type2 || $type1 !== $expectedType) {
                    $issues[] = [
                        'issue' => "Inconsistent Data Type for '$field'",
                        'current' => [
                            'api1' => $type1,
                            'api2' => $type2
                        ],
                        'expected' => $expectedType,
                        'recommendation' => "Standardize '$field' to use $expectedType type",
                        'example' => $this->generateTypeExample($field, $expectedType)
                    ];
                }
            }
        }
        
        return $issues;
    }
    
    private function generateImprovedApiExample() {
        return [
            'data' => [
                'id' => 'integer',
                'category_id' => 'integer',
                'title' => 'string',
                'price' => 'decimal',
                'active' => 'boolean',
                'created_at' => 'datetime',
                'updated_at' => 'datetime'
            ],
            'meta' => [
                'total' => 'integer',
                'page' => 'integer',
                'per_page' => 'integer'
            ]
        ];
    }
    
    private function findField($data, $field) {
        if (!is_array($data)) {
            return false;
        }
        
        if (isset($data[$field])) {
            return true;
        }
        
        foreach ($data as $value) {
            if (is_array($value) && $this->findField($value, $field)) {
                return true;
            }
        }
        
        return false;
    }
    
    private function findFieldValue($data, $field) {
        if (!is_array($data)) {
            return null;
        }
        
        if (isset($data[$field])) {
            return $data[$field];
        }
        
        foreach ($data as $value) {
            if (is_array($value)) {
                $result = $this->findFieldValue($value, $field);
                if ($result !== null) {
                    return $result;
                }
            }
        }
        
        return null;
    }
    
    private function getValueType($value) {
        if ($value === null) return 'null';
        if (is_bool($value)) return 'boolean';
        if (is_int($value)) return 'integer';
        if (is_float($value)) return 'decimal';
        if (is_string($value)) {
            if (strtotime($value) !== false) return 'datetime';
            return 'string';
        }
        return gettype($value);
    }
    
    private function generateFieldExample($field) {
        return [
            'current' => $field,
            'suggested' => $this->toSnakeCase($field),
            'example_response' => [
                'data' => [
                    $this->toSnakeCase($field) => 'value'
                ]
            ]
        ];
    }
    
    private function generateTypeExample($field, $type) {
        $example = [
            $field => null
        ];
        
        switch ($type) {
            case 'integer':
                $example[$field] = 1;
                break;
            case 'decimal':
                $example[$field] = 19.99;
                break;
            case 'boolean':
                $example[$field] = true;
                break;
            case 'datetime':
                $example[$field] = date('Y-m-d H:i:s');
                break;
            default:
                $example[$field] = 'string value';
        }
        
        return $example;
    }
    
    private function toSnakeCase($input) {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $input));
    }
    
    private function generateConsistentNamingExample() {
        return [
            'before' => [
                'categoryId' => 1,
                'parentId' => 2,
                'titleCategory' => 'Example',
                'subCategoryApiUrl' => '/api/subcategories/1'
            ],
            'after' => [
                'category_id' => 1,
                'parent_id' => 2,
                'category_title' => 'Example',
                'sub_category_api_url' => '/api/subcategories/1'
            ],
            'benefits' => [
                'Consistent naming makes the API more predictable',
                'Easier integration with different programming languages',
                'Better readability and maintainability'
            ]
        ];
    }
    
    private function generateConsistentDataTypesExample() {
        return [
            'before' => [
                'price' => '19.99',          // string instead of decimal
                'active' => '1',             // string instead of boolean
                'category_id' => '123',      // string instead of integer
                'expiration_date' => '1234567890' // timestamp instead of datetime
            ],
            'after' => [
                'price' => 19.99,            // decimal
                'active' => true,            // boolean
                'category_id' => 123,        // integer
                'expiration_date' => '2024-03-14 12:00:00' // formatted datetime
            ],
            'benefits' => [
                'Consistent data types across all endpoints',
                'Predictable data handling in client applications',
                'Reduced type conversion overhead',
                'Better validation and error handling'
            ]
        ];
    }
} 