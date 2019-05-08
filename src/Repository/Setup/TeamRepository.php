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


use App\Entity\Setup\Person;
use App\Entity\Setup\Team;
use App\Entity\Setup\TeamClass;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ManagerRegistry;

class TeamRepository extends ServiceEntityRepository
{
   public function __construct(ManagerRegistry $registry)
   {
       parent::__construct($registry, Team::class);
   }

    /**
     * @param TeamClass $class
     * @param array $personCollections
     * @return array
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
   public function createTeamList(TeamClass $class, array $personCollections):array
   {
       $teamCollection = [];
       foreach($personCollections as $persons) {
           $team = new Team();
           $team->setTeamClass($class);
           $this->_em->persist($team);
           /** @var ArrayCollection $collection */
           $collection = $team->getPerson();
           switch(count($persons)) {
               case 1:
                   /** @var Person $_person */
                   $_person = $persons[0];
                   $team->getPerson()->add($persons[0]);
                   $team->setPersons([$_person->getId()]);
                   break;
               case 2:
                   /** @var Person $_person1 */
                   $_person1 = $persons[0];
                   /** @var Person $_person2 */
                   $_person2 = $persons[1];
                   $team->getPerson()->add($_person1);
                   $team->getPersons()->add($_person2);
                   $team->setPersons([$_person1->getId(),$_person2->getId()]);
           }
            $this->_em->flush();
           $teamCollection[]=$team;
       }
       return $teamCollection;
   }

   public function fetchQuickSearch()
   {
       $arr=[];
       $qb = $this->createQueryBuilder('team');
       $qb->select('team','class','person')
           ->innerJoin('team.class','class')
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
   }
}