<?php
namespace App\Util;

class StringUtil {
    public static function toCamelCase($string) {
        return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $string))));
    }
    
    public static function toSnakeCase($string) {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $string));
    }
    
    public static function similarity($str1, $str2) {
        $len1 = strlen($str1);
        $len2 = strlen($str2);
        
        if ($len1 < $len2) {
            return self::similarity($str2, $str1);
        }
        
        if ($len2 == 0) {
            return $len1;
        }
        
        $previous_row = range(0, $len2);
        
        for ($i = 0; $i < $len1; $i++) {
            $current_row = array();
            $current_row[0] = $i + 1;
            
            for ($j = 0; $j < $len2; $j++) {
                $insertions = $previous_row[$j + 1] + 1;
                $deletions = $current_row[$j] + 1;
                $substitutions = $previous_row[$j] + ($str1[$i] != $str2[$j]);
                
                $current_row[] = min($insertions, $deletions, $substitutions);
            }
            
            $previous_row = $current_row;
        }
        
        return 1 - ($previous_row[$len2] / max($len1, $len2));
    }
} 