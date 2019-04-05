<?php
/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 11/29/18
 * Time: 6:17 PM
 */

namespace App\Repository\Setup;


use App\Entity\Setup\AgePerson;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class AgePersonRepository extends ServiceEntityRepository
{
   public function __construct(ManagerRegistry $registry)
   {
       parent::__construct($registry, AgePerson::class);
   }

    /**
     * @param array $describe
     * @param array $values
     * @param array $prfPersons
     * @return AgePerson
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function create(array $describe, array $values, array $prfPersons) {
       $em = $this->getEntityManager();
       $agePerson = new AgePerson();
       $agePerson->setDescribe($describe);
       $valCollection = $agePerson->getValue();
       $prfCollection = $agePerson->getPrfPerson();
       $em->persist($agePerson);
       foreach($values as $value){
            $valCollection->add($value);
        }
        foreach($prfPersons as $prfPerson) {
            $prfCollection->add($prfPerson);
        }
        $em->flush();
       return $agePerson;
    }

    /**
     * @return array
     */
   public function fetchQuickSearch():array
   {
       $results = $this->findAll();
       $arr = [];
       /** @var AgePerson $result */
       foreach($results as $result) {
           $describe = $result->getDescribe();
           $type = $describe['type'];
           $status = $describe['status'];
           $years = $describe['years'];
           $designate = $describe['designate'];
           if(!isset($arr[$type])) {
               $arr[$type]=[];
           }
           if(!isset($arr[$type][$status])) {
               $arr[$type][$status] = [];
           }
           if(!isset($arr[$type][$status][$years])) {
               $arr[$type][$status][$years]=[];
           }
           $arr[$type][$status][$years][$designate]=$result;
       }
       return $arr;
   }
}