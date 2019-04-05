<?php
/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 11/29/18
 * Time: 6:23 PM
 */

namespace App\Repository\Setup;


use App\Entity\Setup\PrfPerson;
use App\Entity\Setup\PrfTeam;
use App\Entity\Setup\PrfTeamClass;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ManagerRegistry;

class PrfTeamRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PrfTeam::class);
    }

    /**
     * @param PrfTeamClass $prfTeamClass
     * @param array $personList
     * @return PrfTeam
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function create(PrfTeamClass $prfTeamClass, array $personList=[]) : PrfTeam
    {
        $prfTeam = new PrfTeam();
        $prfTeam->setPrfTeamClass($prfTeamClass);
        $em = $this->getEntityManager();
        $em->persist($prfTeam);
        /** @var PrfPerson $person */
        foreach($personList as $person){
           $person->getPrfTeam()->add($prfTeam);
           $prfTeam->getPrfPerson()->add($person);
        }
        $em->flush();
        return $prfTeam;
    }

    public function fetchQuickSearch() {
        $qb = $this->createQueryBuilder('team');
        $qb->select('team','class')
            ->innerJoin('team.prfTeamClass','class');
        $query = $qb->getQuery();
        $results = $query->getResult();
        $arr = [];
        /** @var PrfTeam $result */
        foreach($results as $result) {
            $describe = $result->getPrfTeamClass()->getDescribe();
            $type = $describe['type'];
            $status = $describe['status'];
            $sex  = $describe['sex'];
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
            if(!isset($arr[$type][$status][$sex][$proficiency])){
                $arr[$type][$status][$proficiency]=[];
            }
            if(!isset($arr[$type][$status][$sex][$proficiency])) {
                $arr[$type][$status][$sex][$proficiency] = new ArrayCollection();
            }
            /** @var ArrayCollection $collection */
            $collection= & $arr[$type][$status][$sex][$proficiency];
            $collection->set($result->getId(),$result);
        }
       return $arr;
    }
}