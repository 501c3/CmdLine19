<?php
/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 11/10/18
 * Time: 8:45 PM
 */

namespace App\Signal;


use Symfony\Component\EventDispatcher\Event;

class ProcessEvent extends Event
{
    const NAME = 'process.update';
    /** @var ProcessStatus  */
    private $status;
    public function __construct(ProcessStatus $status)
    {
        $this->status = $status;
    }

    public function getStatus()
    {
        return $this->status;
    }

}