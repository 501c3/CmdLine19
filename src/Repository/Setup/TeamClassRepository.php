<?php
/**
 * Copyright (c) 2018. Mark Garber.  All rights reserved.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 12/28/18
 * Time: 9:33 PM
 */

namespace App\Repository\Setup;


use App\Entity\Setup\TeamClass;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ManagerRegistry;

class TeamClassRepository extends ServiceEntityRepository
{
   public function __construct(ManagerRegistry $registry)
   {
       parent::__construct($registry, TeamClass::class);
   }

    public function fetchQuickSearch()
    {
        $classes = $this->findAll();
        $arr = [];
        /** @var TeamClass $class */
       foreach($classes as $class) {
            $describe = $class->getDescribe();
            $type = $describe['type'];
            $status = $describe['status'];
            $sex = $describe['sex'];
            $age = $describe['age'];
            $proficiency = $describe['proficiency'];
            if(!isset($arr[$type])) {
                $arr[$type] = [];
            }
            if(!isset($arr[$type][$status])) {
                $arr[$type][$status] = [];
            }
            if(!isset($arr[$type][$status][$sex])) {
                $arr[$type][$status][$sex] = [];
            }
            if(!isset($arr[$type][$status][$sex][$age])){
                $arr[$type][$status][$sex][$age]=[];
            }
            $arr[$type][$status][$sex][$age][$proficiency] = $class;
        }
        return $arr;
   }
}