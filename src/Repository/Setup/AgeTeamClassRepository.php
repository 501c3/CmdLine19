<?php
/**
 * Copyright (c) 2018. Mark Garber.  All rights reserved.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 11/30/18
 * Time: 7:30 PM
 */

namespace App\Repository\Setup;


use App\Entity\Setup\AgeTeamClass;
use App\Entity\Setup\PrfTeam;
use App\Entity\Setup\PrfTeamClass;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ManagerRegistry;

class AgeTeamClassRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AgeTeamClass::class);
    }

    /**
     * @param array $describe
     * @param array $prfClasses
     * @param array $values
     * @return AgeTeamClass
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function create(array $describe, array $prfClasses, array $values=[])
    {
        $em = $this->getEntityManager();
        $ageTeamClass = new AgeTeamClass();
        $ageTeamClass->setDescribe($describe);
        $em->persist($ageTeamClass);
        $collectionClass = $ageTeamClass->getPrfTeamClass();
        foreach($prfClasses as $prfClass) {
            $collectionClass->add($prfClass);
        }
        $collectionValue = $ageTeamClass->getValue();
        foreach($values as $value) {
            $collectionValue->add($value);
        }
        $em->flush();
        return $ageTeamClass;
    }

    public function fetchQuickSearch()
    {
        $arr = [];
        $results = $this->findAll();
        /** @var AgeTeamClass $result */
        foreach($results as $result){
            $describe=$result->getDescribe();
            $type = $describe['type'];
            $status = $describe['status'];
            $age = $describe['age'];
            if(!isset($arr[$type])) {
                $arr[$type] = [];
            }
            if(!isset($arr[$type][$status])) {
                $arr[$type][$status]=[];
            }
            if(!isset($arr[$type][$status])) {
                $arr[$type][$status]=[];
            }
            $arr[$type][$status][$age]=$result;
        }
        return $arr;
    }
}