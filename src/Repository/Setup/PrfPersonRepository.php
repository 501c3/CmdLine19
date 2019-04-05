<?php
/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 11/29/18
 * Time: 6:21 PM
 */

namespace App\Repository\Setup;


use App\Entity\Setup\PrfPerson;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class PrfPersonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PrfPerson::class);
    }

    /**
     * @param array $describe
     * @param array $values
     * @return PrfPerson
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function create(array $describe, array $values)
    {
        $prfPerson = new PrfPerson();
        $prfPerson->setDescribe($describe);
        $collection = $prfPerson->getValue();
        $em = $this->getEntityManager();
        $em->persist($prfPerson);
        foreach($values as $value){
            $collection->add($value);
        }
        $em->flush();
        return $prfPerson;
    }

    /**
     * @return array
     */
    public function fetchQuickSearch():array
    {
        $results = $this->findAll();
        $arr = [];
        /** @var PrfPerson $result */
        foreach($results as $result) {
            $describe = $result->getDescribe();
            $type = $describe['type'];
            $status = $describe['status'];
            $sex = $describe['sex'];
            $proficiency = $describe['proficiency'];
            $designate = $describe['designate'];
            if(!isset($arr[$type])) {
                $arr[$type]=[];
            }
            if(!isset($arr[$type][$status])) {
                $arr[$type][$status]=[];
            }
            if(!isset($arr[$type][$status][$sex])) {
                $arr[$type][$status][$sex] = [];
            }
            if(!isset($arr[$type][$status][$sex][$proficiency])){
                $arr[$type][$status][$sex][$proficiency]=[];
            }
            $arr[$type][$status][$sex][$proficiency][$designate]=$result;
        }
        return $arr;
    }
}