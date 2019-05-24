<?php
/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 5/7/19
 * Time: 5:17 PM
 */

namespace App\Repository\Setup;


use App\Entity\Setup\Person;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ManagerRegistry;

class PersonRepository extends ServiceEntityRepository
{
    /** @var array */
    private $proficiencyYears;

    /** @var array */
    private $yearsProficiency;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Person::class);
    }

    /**
     * @param array $describe
     * @param int $years
     * @param array $values
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function create(array $describe, int $years, array $values=[]) {
        $person = new Person();
        $person->setDescribe($describe)
                ->setYears($years);
        /** @var ArrayCollection $collection */
        $collection = $person->getValue();
        if(count($values)) {
            foreach($values as $value) {
                $collection->add($value);
            }
        }
        $this->_em->persist($person);
        $this->_em->flush();
    }

    public function fetchQuickSearch(){
        /** @var Person $result */
        $arr =[];
        /** @var Person $rec */
        foreach($this->findAll() as $rec){
          $d = $rec->getDescribe();
          $type = $d['type'];
          $status=$d['status'];
          $sex=$d['sex'];
          $designate=$d['designate'];
          $proficiency=$d['proficiency'];
          $years = $d['years'];
          if(!isset($arr[$type])) {
              $arr[$type]=[];
          }
          if(!isset($arr[$type][$status])) {
              $arr[$type][$status]=[];
          }
          if(!isset($arr[$type][$status][$sex])) {
              $arr[$type][$status][$sex]=[];
          }
          if(!isset($arr[$type][$status][$sex][$designate])) {
              $arr[$type][$status][$sex][$designate]=[];
          }
          if(!isset($arr[$type][$status][$sex][$designate][$proficiency])) {
              $arr[$type][$status][$sex][$designate][$proficiency]=[];
          }
          $arr[$type][$status][$sex][$designate][$proficiency][$years]=$rec;
        }
        return $arr;
    }

    public function fetchProficiencyYears()
    {
        if(isset($this->proficiencyYears)){
            return $this->proficiencyYears;
        }
        $arr = [];
        /** @var Person $rec */
        foreach($this->findAll() as $rec) {
            $d = $rec->getDescribe();
            $designate = $d['designate'];
            $proficiency = $d['proficiency'];
            $years = $d['years'];
            if(!isset($arr[$proficiency])) {
                $arr[$proficiency]=[];
            }
            if(!isset($arr[$proficiency][$years])) {
                $arr[$proficiency][$years]=[];
            }
            if(!isset($arr[$proficiency][$years][$designate])) {
                $arr[$proficiency][$years][$designate] = new ArrayCollection();
            }
            /** @var ArrayCollection $collection */
            $collection = $arr[$proficiency][$years][$designate];
            $collection->set($rec->getId(),$rec);
        }
        $this->proficiencyYears=$arr;
        return $arr;
    }

    public function fetchYearsProficiency()
    {
        if(isset($this->proficiencyYears)) {
            return $this->proficiencyYears;
        }
        $arr = [];
        /** @var Person $rec */
        foreach($this->findAll() as $rec) {
            $d = $rec->getDescribe();
            $designate = $d['designate'];
            $years = $d['years'];
            $proficiency = $d['proficiency'];
            if(!isset($arr[$years])) {
                $arr[$years]=[];
            }
            if(!isset($arr[$years][$proficiency])) {
                $arr[$years][$proficiency]=[];
            }
            if(!isset($arr[$years][$proficiency][$designate])) {
                $arr[$years][$proficiency][$designate]=new ArrayCollection();
            }
            /** @var ArrayCollection $collection */
            $collection = $arr[$years][$proficiency][$designate];
            $collection->set($rec->getId(),$rec);
        }
        $this->yearsProficiency=$arr;
        return $arr;
    }

}