<?php
namespace Tests;

use PHPUnit\Framework\TestCase;
use App\Processor\JsonProcessor;
use App\Processor\JsonCleaner;
use App\Processor\JsonAnalyzer;

class JsonProcessorTest extends TestCase {
    private $testData1;
    private $testData2;
    
    protected function setUp(): void {
        $this->testData1 = [
            'id' => 1,
            'test_field' => 'value',
            'nested' => [
                'someKey' => 'value'
            ]
        ];
        
        $this->testData2 = [
            'id' => 2,
            'testField' => 'value',
            'nested' => [
                'some_key' => 'value'
            ]
        ];
    }
    
    public function testJsonCleaning() {
        $cleaner = new JsonCleaner($this->testData1);
        $result = $cleaner->formatKeys('snake');
        
        $this->assertArrayHasKey('test_field', $result);
        $this->assertArrayHasKey('nested', $result);
        $this->assertArrayHasKey('some_key', $result['nested']);
    }
    
    public function testJsonAnalysis() {
        $analyzer = new JsonAnalyzer($this->testData1);
        $analysis = $analyzer->analyze();
        
        $this->assertArrayHasKey('structure', $analysis['data']);
        $this->assertArrayHasKey('statistics', $analysis['data']);
        $this->assertArrayHasKey('issues', $analysis['data']);
    }
} 