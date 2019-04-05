<?php
/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 10/13/18
 * Time: 8:04 PM
 */

namespace App\Common;
use Throwable;

class AppParseException extends \Exception
{

    const
        MESSAGE_FPE = "Found '%s' at (row:%d,col:%d) but expected %s in file: %s. Reference: %d",
        MESSAGE_FP  ="'%s' at (row:%d,col:%d) is an unrecognized value in file: %s. Reference: %d",
        MESSAGE_MK  ="Missing %s between lines %d-%d in file: %s. Reference: %d",
        MESSAGE_IR  ="'%s' at (row:%d,col:%d) is an invalid numeric range in file: %s. Reference: %d",
        MESSAGE_UC  ="Unhandled condition in source file: %s. Reference: %d",
        MESSAGE_ES  ="Expected structure following '%s' at (row:%d,col:%d) in file: %s. Reference: %d",
        MESSAGE_NF  ="File location not found at (row:%d,col:%d) in yaml file: %s. Reference: %d",
        MESSAGE_DO  ="'%s' at (row:%d,col:%d) in yaml file: %s. Should this be a later date?. Reference: %d",
        MESSAGE_IP  ="'%s' at (row:%d,col:%d) is an invalid parameter in file: %s. Reference: %d",
        MESSAGE_EM  ="'%s' at (row:%d,col:%d) is an invalid email in file: %s. Reference: %d";

    /**
     * AppParseException constructor.
     * @param int $code
     * @param $parameters
     * @param Throwable|null $exception
     * @throws AppParseException
     */
    public function __construct(int $code,$parameters,Throwable $exception=null)
    {
        $message = $this->message($code, ...$parameters);
        parent::__construct($message, $code, $exception);
    }/** @noinspection SpellCheckingInspection */


    /**
     * @param string $string
     * @return array
     * @throws AppParseException
     */
    public static function strToPos(string $string) {
        $pos=[];
        $result = preg_match('/R(?P<row>\d+)C(?P<col>\d+)/',$string, $pos);
        if(!$result) {
            throw new AppParseException(AppExceptionCodes::UNHANDLED_CONDITION, [__FILE__]);
        }
        return $pos;
    }


    /**
     * @param $code
     * @param null $pathfile
     * @param null $found
     * @param null $position
     * @param null $expected
     * @return string
     * @throws AppParseException
     */
    private function message($code,$pathfile=null,$found=null,$position=null,$expected=null)
    {

        $parts = pathinfo($pathfile);
        $extension = isset($parts['extension'])?$parts['extension']:'';
        $file = $parts['filename'].'.'.$extension;
        switch($code) {
            case AppExceptionCodes::FOUND_BUT_EXPECTED:
                return self::messageFPE($code, $file, $found, $position, $expected);
            case AppExceptionCodes::INVALID_RANGE:
                return self::messageIR($code,$file,$found,$position);
            case AppExceptionCodes::UNRECOGNIZED_VALUE:
                return self::messageFP($code,$file,$found,$position);
            case AppExceptionCodes::MISSING_KEYS:
                return self::messageMK($code,$file,$found,$position);
            case AppExceptionCodes::EXPECTED_STRUCTURE:
                return self::messageES($code,$file,$found,$position);
            case AppExceptionCodes::FILE_NOT_FOUND:
                return self::messageNF($code,$file,$position);
            case AppExceptionCodes::INVALID_PARAMETER:
                return self::messageIP($code,$file,$found,$position);
            case AppExceptionCodes::BAD_DATE_ORDER:
                return self::messageDO($code,$file,$found,$position);
            case AppExceptionCodes::INVALID_EMAIL:
                return self::messageEM($code,$file,$found,$position);
            default:
                return self::messageUC($code,$file);
        }
    }


    /**
     * @param $code
     * @param $file
     * @param $found
     * @param $position
     * @param $expected
     * @return string
     * @throws AppParseException
     */
    public static function messageFPE($code,$file,$found,$position,$expected)
    {
        $str=is_array($expected)?'['.join("|",$expected).']':$expected;
        $pos = self::strToPos($position);
        return sprintf(self::MESSAGE_FPE,$found,$pos['row'],$pos['col'],$str,$file,$code);
    }

    /**
     * @param $code
     * @param $file
     * @param $found
     * @param $position
     * @return string
     * @throws AppParseException
     */
    public static function messageIR($code,$file,$found,$position)
    {
        $pos = self::strToPos($position);
        return sprintf(self::MESSAGE_IR,$found,$pos['row'],$pos['col'],$file,$code);
    }


    /**
     * @param $code
     * @param $file
     * @param $found
     * @param $position
     * @return string
     * @throws AppParseException
     */
    public static function messageFP($code,$file,$found,$position)
    {
        $pos = self::strToPos($position);
        return sprintf(self::MESSAGE_FP,$found,$pos['row'],$pos['col'],$file,$code);
    }


    /**
     * @param $code
     * @param $file
     * @param $missingKeys
     * @param $positions
     * @return string
     * @throws AppParseException
     */
    public static function messageMK($code,$file,$missingKeys,$positions)
    {
        $lines = [];
        foreach($positions as $position) {
            $pos=self::strToPos($position);
            $lines[] = $pos['row'];
        }
        $max = max($lines);
        $min = min($lines);
        return sprintf(self::MESSAGE_MK,'['.join('|',$missingKeys).']',$min,$max,$file,$code);
    }

    /**
     * @param $code
     * @param $file
     * @return string
     */
    public static function messageUC($code,$file)
    {
        return sprintf(self::MESSAGE_UC, $file, $code);
    }

    /**
     * @param $code
     * @param $file
     * @param $found
     * @param $position
     * @return string
     * @throws AppParseException
     */
    public static function messageES($code,$file,$found,$position)
    {
        $pos = self::strToPos($position);
        return sprintf(self::MESSAGE_ES, $found, $pos['row'],$pos['col'],  $file, $code);
    }


    public static function messageIP($code,$file,$found,$position)
    {
        $pos = self::strToPos($position);
        return sprintf(self::MESSAGE_IP, $found, $pos['row'],$pos['col'], $file, $code);
    }


    /**
     * @param $code
     * @param $file
     * @param $position
     * @return string
     * @throws AppParseException
     */
    public static function messageNF($code,$file,$position)
    {
        $pos = self::strToPos($position);
        return sprintf(self::MESSAGE_NF, $pos['row'],$pos['col'], $file, $code);
    }

    /**
     * @param $code
     * @param $file
     * @param $date
     * @param $position
     * @return string
     * @throws AppParseException
     */
    public static function messageDO($code,$file, $date,$position)
    {
        $pos = self::strToPos($position);
        return sprintf(self::MESSAGE_DO,$date,$pos['row'],$pos['col'],$file,$code);
    }

    public static function messageEM($code,$file,$email,$position)
    {
        $pos = self::strToPos($position);
        return sprintf(self::MESSAGE_EM,$email,$pos['row'],$pos['col'],$file,$code);
    }


}