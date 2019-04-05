<?php
/**
 * Copyright (c) 2018. Mark Garber.  All rights reserved.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 12/19/18
 * Time: 6:22 PM
 */

namespace App\Command;


use Throwable;

class AppCommandException extends \Exception
{
   const FOUND_BUT_EXPECTED = 2010,
         MISSING_KEYS = 2020,
         MISSING_FILE = 2030,
         MISSING_MASTER = 2040;

   public function __construct( int $code = 0, $parameters, Throwable $previous = null)
   {
       $messageProcessor = $this->messageProcessor($code);
       $message = $this->$messageProcessor(...$parameters);
       parent::__construct($message, $code, $previous);
   }

   private function messageProcessor(int $code)
   {
       switch($code) {
           case self::FOUND_BUT_EXPECTED:
                return 'messageFoundButExpected';
           case self::MISSING_KEYS:
                return 'messageMissingKeys';
           case self::MISSING_FILE:
                 return 'messageMissingFile';
           case self::MISSING_MASTER:
                 return 'messageMissingMaster';

       }

   }



    /** @noinspection PhpUnusedPrivateMethodInspection */
    private function messageFoundButExpected(string $found, array $expected, string $masterFile)
    {
       $baseFile = basename($masterFile);
       $expectStr = '['.join(',',$expected).']';
       $message = sprintf("Found %s but expected %s in file:%s.",$found,$expectStr,$baseFile);
       return $message;
    }


    /** @noinspection PhpUnusedPrivateMethodInspection */
    private function messageMissingKeys(array $keysMissing, string $masterFile)
    {
        $str = join(',', $keysMissing);
        return sprintf('Missing keys: %s in %s.',$str,$masterFile);
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    private function messageMissingFile(string $missingFile, string $masterFile)
    {
        return sprintf('Missing %s in %s.',$missingFile,$masterFile);
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    private function messageMissingMaster(string $masterFile)
    {
        return sprintf('Missing master file: %s',$masterFile);
    }

}