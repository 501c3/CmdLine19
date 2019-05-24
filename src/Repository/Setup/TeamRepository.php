<?php
/**
 * Copyright (c) 2018. Mark Garber.  All rights reserved.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 12/29/18
 * Time: 3:12 PM
 */

namespace App\Repository\Setup;

use App\Entity\Setup\Team;
use App\Entity\Setup\TeamClass;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class TeamRepository extends ServiceEntityRepository
{
   public function __construct(ManagerRegistry $registry)
   {
       parent::__construct($registry, Team::class);
   }

    /**
     * @param TeamClass $teamClass
     * @param array $personCollections
     * @return array
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
   public function createTeamList(TeamClass $teamClass, array $personCollections):array
   {
       $teamCollection = [];
       foreach($personCollections as $persons) {
           $team = new Team();
           $team->setTeamClass($teamClass);
           switch(count($persons)) {
               case 1:
                   $team->addPerson($persons[0]);
                   break;
               case 2:
                   $team->addPerson($persons[0])
                        ->addPerson($persons[1]);
           }
           $this->_em->persist($team);
           $teamCollection[]=$team;
       }
       $this->_em->flush();
       return $teamCollection;
   }

   public function fetchQuickSearch()
   {
       $arr=[];
       $qb = $this->createQueryBuilder('team');
       $qb->select('team','class','person')
           ->innerJoin('team.teamClass','class')
           ->innerJoin('team.person','person');
       $results = $qb->getQuery()->getResult();
       /** @var Team $result */
       foreach($results as $result)
       {
           $describe = $result->getTeamClass()->getDescribe();
           $type=$describe['type'];
           $status=$describe['status'];
           $sex=$describe['sex'];
           $proficiency=$describe['proficiency'];
           $age=$describe['age'];
           if(!isset($arr[$type])) {
               $arr[$type]=[];
           }
           if(!isset($arr[$type][$status])) {
               $arr[$type][$status]=[];
           }
           if(!isset($arr[$type][$status][$sex])) {
               $arr[$type][$status][$sex]=[];
           }
           if(!isset($arr[$type][$status][$sex][$proficiency])) {
               $arr[$type][$status][$sex][$proficiency]=[];
           }
           if(!isset($arr[$type][$status][$sex][$proficiency][$age])) {
               $arr[$type][$status][$sex][$proficiency][$age]=[];
           }
           $arr[$type][$status][$sex][$proficiency][$age][]=$result;
       }
       return $arr;
   }
}