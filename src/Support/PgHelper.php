<?php 

namespace Limanweb\PgExt\Support;

class PgHelper {

    
    /**
     * Casts PHP-array to PostgreSQL array string
     *
     * @param mixed $value
     * @param string $valType
     * @return mixed|array
     * 
     * @example toPgArray([1,2]) >> '{1,2}'
     * @example toPgArray([1,2], 'string') >> '{"1","2"}'
     * @example toPgArray(['red','"blue" is not "red"']) >> '{"red","\"blue\" is not \"red\""}'
     */
    public static function toPgArray($value, $valType = null) {
        if (is_array($value)) {
            $value = self::arrayValuesToType($value, $valType);
            $value = json_encode($value);
            $value = '{'.mb_substr($value, 1, strlen($value)-2).'}';
        }
        return $value;
    }
    
    /**
     * Casts PostgreSQL array string into PHP-array
     *
     * @param mixed $value
     * @param string $valType
     * @return mixed|array
     *
     * @example fromPgArray('{1,2}') >> [1,2] 
     * @example fromPgArray('{"1","2"}', 'int') >> [1,2] 
     * @example fromPgArray('{"red","\"blue\" is not \"red\""}') >> ['red','"blue" is not "red"'] 
     */
    public static function fromPgArray($value, $valType = null) {
        if (is_string($value)) {
            $value = '['.mb_substr($value, 1, strlen($value)-2).']';
            $value = json_decode($value, false);
            $value = self::arrayValuesToType($value, $valType);
        } 
        return $value;
    }
    
    /**
     * Casts all array values to described PHP-type
     * 
     * @param array $value
     * @param null|string $valType
     * @return array
     * 
     * @example arrayValuesToType([1,'2',3], 'int') >> [1,2,3] 
     * @example arrayValuesToType([1,'2',3], 'string') >> ['1','2','3'] 
     * @example arrayValuesToType([1,'2',3], null) >> [1,'2',3] 
     */
    public static function arrayValuesToType($value, $valType = null) {
        if (!is_null($valType)) {
            $value = array_map(function ($val) use ($valType) {
                    settype($val, $valType);
                    return $val;
            }, $value);
        }
        return $value;
    }
    
}



?>