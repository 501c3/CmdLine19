<?php
/**
 * Copyright (c) 2019. Mark Garber.  All rights reserved.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 4/5/19
 * Time: 8:35 PM
 */

namespace App\Repository\Competition;


use App\Entity\Competition\Heat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class HeatRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Heat::class);
    }
}