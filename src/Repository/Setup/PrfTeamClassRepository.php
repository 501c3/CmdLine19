<?php
/**
 * Copyright (c) 2018. Mark Garber.  All rights reserved.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 11/30/18
 * Time: 7:33 PM
 */

namespace App\Repository\Setup;


use App\Entity\Setup\PrfTeamClass;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class PrfTeamClassRepository extends ServiceEntityRepository
{
   public function __construct(ManagerRegistry $registry)
   {
       parent::__construct($registry, PrfTeamClass::class);
   }

    /**
     * @param array $describe
     * @param array $values
     * @return PrfTeamClass
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
   public function create(array $describe,array $values) : PrfTeamClass
   {
       if(count($values)==0){
           die('There are no values to add.');
       }
       $em = $this->getEntityManager();
       $prfTeamClass = new PrfTeamClass();
       $prfTeamClass->setDescribe($describe);
       $collection = $prfTeamClass->getValue();
       foreach($values as $value) {
           $collection->add($value);
       }
       $em->persist($prfTeamClass);
       $em->flush();
       return $prfTeamClass;
   }



   public function fetchQuickSearch() {
       $qb = $this->createQueryBuilder('class');
       $qb->select('class','team','person')
           ->innerJoin('class.prfTeam','team')
           ->innerJoin('team.prfPerson','person');
       $query = $qb->getQuery();
       $results = $query->getResult();
       $arr = [];
       /** @var PrfTeamClass $class */
       foreach($results as $class){
           $describe = $class->getDescribe();
           $type = $describe['type'];
           $status = $describe['status'];
           $sex = $describe['sex'];
           $proficiency = $describe['proficiency'];
           if(!isset($arr[$type])) {
               $arr[$type]=[];
           }
           if(!isset($arr[$type][$status])) {
               $arr[$type][$status]=[];
           }
           if(!isset($arr[$type][$status][$sex])){
               $arr[$type][$status][$sex]=[];
           }
           $arr[$type][$status][$sex][$proficiency]=$class;
       }
       return $arr;
   }
}