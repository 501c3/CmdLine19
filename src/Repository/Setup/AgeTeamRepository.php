<?php
/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 11/29/18
 * Time: 6:18 PM
 */

namespace App\Repository\Setup;


use App\Entity\Setup\AgePerson;
use App\Entity\Setup\AgeTeam;
use App\Entity\Setup\AgeTeamClass;
use App\Entity\Setup\PrfTeam;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ManagerRegistry;

class AgeTeamRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry,AgeTeam::class);
    }

    /**
     * @param AgeTeamClass $ageTeamClass
     * @param array $prfTeamList
     * @param array $personList
     * @return AgeTeam
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function create(AgeTeamClass $ageTeamClass, array $personList=[], array $prfTeamList=[])
    {
        $ageTeam = new AgeTeam();
        $ageTeam->setAgeTeamClass($ageTeamClass);
        $em = $this->getEntityManager();
        $em->persist($ageTeam);
        /** @var AgePerson $person */
        $collection = $ageTeam->getAgePerson();
        foreach($personList as $person) {
            $collection->add($person);
        }
        $em->flush();
        return $ageTeam;
    }


    /**
     * @return array
     */
    public function fetchQuickSearch():array
    {
        $qb = $this->createQueryBuilder('team');
        $qb->select('team','class')
            ->innerJoin('team.ageTeamClass','class');
        $query = $qb->getQuery();
        $results = $query->getResult();
        $arr = [];
        /** @var AgeTeam $result */
        foreach($results as $result) {
            $describe = $result->getAgeTeamClass()->getDescribe();
            $type = $describe['type'];
            $status = $describe['status'];
            $age = $describe['age'];
            if(!isset($arr[$type])) {
                $arr[$type]=[];
            }
            if(!isset($arr[$type][$status])) {
                $arr[$type][$status]=[];
            }
            if(!isset($arr[$type][$status][$age])) {
                $arr[$type][$status][$age] = new ArrayCollection();
            }
            /** @var ArrayCollection $collection */
            $collection = & $arr[$type][$status][$age];
            $collection->set($result->getId(),$result);
        }
        return $arr;
    }
}