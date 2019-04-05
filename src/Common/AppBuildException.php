<?php
/**
 * Copyright (c) 2018. Mark Garber.  All rights reserved.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 12/5/18
 * Time: 8:16 AM
 */

namespace App\Common;


use Throwable;

class AppBuildException extends \Exception
{

  public function __construct(int $code = 0,$parameters,Throwable $previous = null)
  {
      $message =  $this->message(...$parameters);
      parent::__construct($message, $code, $previous);
  }

    /**
     * @param string $pathfile
     * @param int $line
     * @param string $arrName
     * @param array $index
     * @return string
     * @throws \Exception
     */
  private function message(string $pathfile,int $line, string $arrName,array $index)
  {
      $parts = pathinfo($pathfile);
      $file = $parts['filename'].'.'.$parts['extension'];
      $variable = $arrName."['".join("']['",$index)."']";
      return "$variable could not be found in file:$file at line:$line.  "
                .(new \DateTime('now'))->format('Y-m-d H:i:s');
  }
}