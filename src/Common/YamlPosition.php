<?php
/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 2/26/18
 * Time: 5:32 PM
 */

namespace App\Common;

class YamlPosition
{
    const
        POSITION = false,
        STRING = true;

    private static $positions;

    private static $lineCount;


    /**
     * Expected string is of form 'R\d+C\d+'
     * Returns ['row'=> <int>, 'col'=> <int> ]
     *
     * @param string $string
     * @return array
     * @throws \Exception
     */
    public static function strToPos(string $string) {
        $pos=[];
        $result = preg_match('/R(?P<row>\d+)C(?P<col>\d+)/',$string, $pos);
        if(!$result) {
            $message = sprintf('"%s" passed to exception.  Expected string of form "R\d+C\d+" where \d in [0-9]',$string);
            throw new \Exception($message, AppExceptionCodes::INVALID_POSITION);
        }
        return $pos;
    }


    /**
     * @param string $file
     * @return array|string
     * @throws \Exception
     */
    public static function yamlAddPosition(string $file)
    {
        $string = file_exists($file)?file_get_contents($file):false;
        if(!$string) {
            throw new AppParseException(AppExceptionCodes::MISSING_FILE,[$file]);
        }
        $data=yaml_parse_file($file);
        if(is_null($data)) {
            throw new AppParseException(AppExceptionCodes::MISSING_FILE, [$file]);
        }
        $rowColumns=self::rowColumn($string);
        $positions=yaml_parse($rowColumns);
        $stringPosition = self::stringPosition($data,$positions);
        return $stringPosition;
    }

    /**
     * @param string $string
     * @param array $collection
     * @param string|null $file
     * @return bool
     * @throws AppParseException
     */
    public static function inCollection(string $string, array $collection, string $file=null) : bool
    {
        if(strpos( $string, '|')) {
            list($str,$pos) = explode('|', $string);
            if (in_array($str,$collection)) {
                return true;
            }
            if ($pos) {
                throw new AppParseException(AppExceptionCodes::FOUND_BUT_EXPECTED,[$file, $str,$pos,$collection]);
            }
        }
        if(in_array($string, $collection)) {
            return true;
        }
        return false;
    }

    /**
     * @param array $arr
     * @return bool
     */
    private static function isAssoc(array $arr) : bool
    {
        if (is_array($arr)) {
            return array_keys($arr) !== range(0, count($arr) - 1);
        }
        return false;
    }


    /**
     * @param $mixed
     * @param bool $return
     * @return array|string
     * @throws \Exception
     */
    public static function isolate($mixed, bool $return = self::STRING)
    {
        if(is_array($mixed)){
            if(self::isAssoc($mixed)){
                $result = [];
                foreach($mixed as $key=>$value) {
                    list($string,$position)=explode('|',$key);
                    $result[$return?$string:$position] = self::isolate($value);
                }
                return $result;
            } else {
                $result = [];
                foreach($mixed as $value) {
                    $result[]=self::isolate($value, $return);
                }
                return $result;
            }
        } elseif (is_string($mixed)) {
            list($string,$position) = explode('|',$mixed);
            return $return?$string:$position;
        }
        throw new AppParseException(AppExceptionCodes::UNHANDLED_CONDITION,[__FILE__]);
    }


    private static function stringPosition($data, $positions) {
        if(is_array($data) && is_array($positions)) {
            list($key,$keyPos,$dataPart,$dataPartPos) = self::current($data,$positions);
            if(self::isAssoc($data)) {
                $result = [];
                while($key) {
                    $recursiveResult = self::stringPosition($dataPart, $dataPartPos);
                    $result[$key.'|'.$keyPos] = $recursiveResult;
                    list($key, $keyPos, $dataPart, $dataPartPos) = self::next($data,$positions);
                }
                return $result;
            }else{
                $result = [];
                foreach($data as $i=>$dataPart) {
                    $result[]=self::stringPosition($dataPart,$positions[$i]);
                }
                return $result;
            }
        } elseif ((is_string($data)||is_numeric($data)||is_bool($data)) && is_string($positions)) {
            return $data.'|'.$positions;
        }
        return null;
        // throw new \Exception('Error in parsing');
    }


    public static function getLineCount()
    {
        return self::$lineCount;
    }

    /**
     * @return mixed|null
     */
    public static function positionYaml()
    {
        return self::$positions;
    }

    /**
     * @param string $string
     * @return string
     */
    public static function rowColumn(string $string)
    {
        /** @var array $lines */
        $lines=explode("\n",$string);
        self::$lineCount=count($lines);
        $row=0;
        $positions = [];
        foreach($lines as $line)
        {
            $row++;
            array_push($positions, self::position($row, $line));
        }
        self::$positions=join("\n",$positions);
        return self::$positions;
    }

    public static function position(int $row, $string)
    {
        $len=strlen($string);
        $col=0;
        $array=[];
        $match=[];
        $char=substr($string,$col,1);
        $rest=substr($string,$col);
        if(preg_match('/state/',$string)){
            $a=$string;
        }
        while($col<$len){
            if(in_array($char, [' ',',','{','}','[',']','-',':'])){
                $col++;
                array_push($array,$char);
            }elseif (preg_match('/#.*/', $rest, $match)) {
                $size = strlen($match[0]);
                $position = sprintf('R%dC%d', $row, $col + 1);
                array_push($array, $position);
                $col += $size;
            }elseif (preg_match('/[\<]*\w+([\s\-\/\.\@\<\>\+\&]*\w?)*/',$rest, $match)) {
                $size = strlen($match[0]);
                $position = sprintf('R%dC%d',$row,$col+1);
                array_push($array, $position);
                $col += $size;
//                if($size==1 and $match[0]==$rest){
//                  return;
//                }
            }else{
                $col++;
            }
            $char=substr($string,$col,1);
            $rest=substr($string,$col);
        }
        return join('',$array);
    }

    private static function current(&$data, &$position)
    {
        $dataValue=current($data);
        $dataPos=current($position);
        $keyValue=key($data);
        $keyPos=key($position);
        return [$keyValue,$keyPos,$dataValue,$dataPos];
    }

    private static function next(array &$data, array &$position) {
        $dataValue=next($data);
        $dataPos=next($position);
        $keyValue=key($data);
        $keyPos=key($position);
        return [$keyValue,$keyPos,$dataValue,$dataPos];
    }
}