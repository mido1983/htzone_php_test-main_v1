<?php
namespace App\Util;

class ArrayUtil {
    public static function flatten(array $array, $prefix = '') {
        $result = [];
        
        foreach ($array as $key => $value) {
            $newKey = $prefix ? "{$prefix}.{$key}" : $key;
            
            if (is_array($value)) {
                $result = array_merge($result, self::flatten($value, $newKey));
            } else {
                $result[$newKey] = $value;
            }
        }
        
        return $result;
    }
    
    public static function diff(array $array1, array $array2) {
        $flat1 = self::flatten($array1);
        $flat2 = self::flatten($array2);
        
        return [
            'added' => array_diff_assoc($flat2, $flat1),
            'removed' => array_diff_assoc($flat1, $flat2),
            'modified' => array_filter(
                array_intersect_key($flat1, $flat2),
                function($key) use ($flat1, $flat2) {
                    return $flat1[$key] !== $flat2[$key];
                },
                ARRAY_FILTER_USE_KEY
            )
        ];
    }
} 