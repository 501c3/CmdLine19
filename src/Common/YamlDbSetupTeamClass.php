<?php
/**
 * Copyright (c) 2018. Mark Garber.  All rights reserved.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 12/2/18
 * Time: 11:53 PM
 */

namespace App\Common;
use App\Entity\Setup\AgeTeam;
use App\Entity\Setup\AgeTeamClass;
use App\Entity\Setup\PrfTeam;
use App\Entity\Setup\PrfTeamClass;
use App\Repository\Setup\AgeTeamClassRepository;
use App\Repository\Setup\AgeTeamRepository;
use App\Repository\Setup\PrfTeamClassRepository;
use App\Repository\Setup\PrfTeamRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\EventDispatcher\EventDispatcher;

class YamlDbSetupTeamClass extends YamlDbSetupPerson
{
   const TEAM_DOMAIN_KEYS = ['type','status','sex','age','proficiency'];

   private $team = [];

   private $prfTeamClass = [];

   private $ageTeamClass = [];

   public function __construct(EntityManagerInterface $entityManager, EventDispatcher $dispatcher = null)
   {
       parent::__construct($entityManager, $dispatcher);
   }

    /**
     * @param string $file
     * @return array
     * @throws AppParseException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws Exception
     */
   public function parseTeams(string $file) {
       $agePrfPositionArray = YamlPosition::yamlAddPosition($file);
       foreach($agePrfPositionArray as $records){
           foreach($records as $keyPosition=>$valueList) {
               list($key,$position) = explode('|',$keyPosition);
               if(!in_array($key,self::TEAM_DOMAIN_KEYS)) {
                   throw new AppParseException(AppExceptionCodes::FOUND_BUT_EXPECTED,
                       [$file,$key,$position,self::TEAM_DOMAIN_KEYS]);
               }
           }
           $keysPositions = array_keys($records);
           $keysFound = YamlPosition::isolate($keysPositions);
           $difference = array_diff(self::TEAM_DOMAIN_KEYS, $keysFound);
           if (count($difference)) {
               throw new AppParseException(AppExceptionCodes::MISSING_KEYS,
                   [$file, $difference, $keysPositions]);
           }
           $cache = [];
           foreach ($records as $keyPosition => $dataPosition) {
               list($key) = explode('|', $keyPosition);
               $cache[$key] = $this->teamClassValuesCheck($file, $key, $dataPosition);
           }
           $this->teamClassValuesBuild($cache);
           $this->sendWorkingStatus();
       }
       return $this->team;
   }

    /**
     * @param $file
     * @param $key
     * @param $valuesPositions
     * @return array|string
     * @throws AppParseException
     * @throws Exception
     */
   public function teamClassValuesCheck($file,$key,$valuesPositions)
   {
       switch($key) {
           case 'type':
           case 'status':
               if(is_array($valuesPositions)) {
                  $array = explode('|',$valuesPositions[0]);
                  throw new AppParseException(AppExceptionCodes::FOUND_BUT_EXPECTED,
                      [$file,'[',$array[1],'scaler']);
               }
               list($value,$position) = explode('|',$valuesPositions);
               if(!isset($this->value[$key][$value])) {
                   throw new AppParseException(AppExceptionCodes::UNRECOGNIZED_VALUE,
                       [$file, $value, $position]);
               }
               break;
           case 'age':
               foreach($valuesPositions as $valuePos=>$yearRanges) {
                   list($value,$position) = explode('|',$valuePos);
                  if(!isset($this->value[$key][$value])) {
                      throw new AppParseException(AppExceptionCodes::UNRECOGNIZED_VALUE,
                          [$file, $value, $position]);
                  }
                  foreach($yearRanges as $rangePosition) {

                      list($range,$position) = explode('|',$rangePosition);
                      $result = preg_match('/(?P<lower>\w+)\-(?P<upper>\w+)/',$range, $bound);
                      if(!$result ||
                          (!is_numeric($bound['lower'])) || !is_numeric($bound['upper']) ||
                          ($bound['lower']>$bound['upper'])) {
                          throw new AppParseException(AppExceptionCodes::INVALID_RANGE, [$file,$range,$position]);
                      }
                  }
               }
               break;
           case 'sex':
               foreach($valuesPositions as $valuePos) {
                   list($value,$position)=explode('|',$valuePos);
                   /** @var YamlDbSetupBase $this */
                   if(!isset($this->value[$key][$value])) {
                       throw new AppParseException(AppExceptionCodes::UNRECOGNIZED_VALUE,
                           [$file,$value,$position]);
                   }
               }
               break;
           case 'proficiency':
               foreach($valuesPositions as $leftProficiencyPosition=>$rightProficiencyPositionList) {
                   list($leftValue,$leftPosition) = explode('|',$leftProficiencyPosition);
                   if(!isset($this->value[$key][$leftValue])) {
                       throw new AppParseException(AppExceptionCodes::UNRECOGNIZED_VALUE,
                           [$file,$leftValue,$leftPosition]);
                   }
                   foreach($rightProficiencyPositionList as $rightProficiencyPosition) {
                       list($rightValue,$rightPosition)=explode('|',$rightProficiencyPosition);
                       if(!isset($this->value[$key][$rightValue])) {
                           throw new AppParseException(AppExceptionCodes::UNRECOGNIZED_VALUE,
                               [$file,$rightValue,$rightPosition]);
                       }
                   }
               }
       }
       return YamlPosition::isolate($valuesPositions);
   }

    /**
     * @param array $cache
     * @throws AppBuildException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function teamClassValuesBuild(array $cache)
    {

        $type = $cache['type'];
        $status = $cache['status'];
        if(!isset($this->team[$type])) {
            $this->team[$type]=[];
        }
        if(!isset($this->team[$type][$status])) {
            $this->team[$type][$status] = [];
        }
        if(!isset($this->team[$type][$status])) {
            $this->team[$type][$status] = ['age'=>[],'prf'=>[]];
        }
        $prfTeamClasses = $this->prfTeamCollectionBuild($type,$status,$cache['sex'],$cache['proficiency']);
        $this->ageTeamCollectionBuild($type,$status,$cache['age'],$prfTeamClasses);
    }

    /**
     * @param string $type
     * @param string $status
     * @param array $ages
     * @param array $prfTeamClasses
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function ageTeamCollectionBuild(string $type,
                                            string $status,
                                            array $ages,
                                            array $prfTeamClasses)
    {
        foreach($ages as $teamAge=>$personAgeRanges){
            $describe = ['type'=>$type,'status'=>$status,'age'=>$teamAge];
            /** @var AgeTeamClass $ageTeamClass */
                $ageTeamClass = $this->fetchAgeTeamClass($describe, $prfTeamClasses); //,$prfCollection['class']);
                $this->collectAgePersonsIntoTeams(
                                $ageTeamClass,
                                $personAgeRanges);
        }
    }

    /**
     * @param $describe
     * @param array $prfTeamClasses
     * @return AgeTeamClass|mixed
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function fetchAgeTeamClass($describe, array $prfTeamClasses)
    {
        $valueList  = [];
        /** @var AgeTeamClassRepository $repository */
        $repository = $this->entityManager->getRepository(AgeTeamClass::class);
        if(!$this->ageTeamClassExists($describe)) {
            foreach($describe as $key=>$value) {
                $valueList[]=$this->value[$key][$value];
            }
            $ageTeamClass = $repository->create($describe,$prfTeamClasses,$valueList);
            $this->setAgeTeamClass($describe,$ageTeamClass);
            return $ageTeamClass;
        }
        $ageTeamClass = $this->getAgeTeamClass($describe);
        return $ageTeamClass;

    }

    /**
     * @param array $describe
     * @return bool
     */
    private function ageTeamClassExists(array $describe)
    {
        $type = $describe['type'];
        $status = $describe['status'];
        $age = $describe['age'];
        if(isset($this->ageTeamClass[$type][$status][$age])) {
            return true;
        }
        return false;
    }

    /**
     * @param $describe
     * @param AgeTeamClass $ageTeamClass
     */
    private function setAgeTeamClass($describe,AgeTeamClass $ageTeamClass){
        $type = $describe['type'];
        $status = $describe['status'];
        $age = $describe['age'];
        if(!isset($this->ageTeamClass[$type])){
            $this->ageTeamClass[$type]=[];
        }
        if(!isset($this->ageTeamClass[$type][$status])){
            $this->ageTeamClass[$type][$status]=[];
        }
        $this->ageTeamClass[$type][$status][$age]=$ageTeamClass;
    }

    /**
     * @param array $describe
     * @return mixed
     */
    private function getAgeTeamClass(array $describe) {
        $type = $describe['type'];
        $status = $describe['status'];
        $age = $describe['age'];
        return $this->ageTeamClass[$type][$status][$age];
    }



    /**
     * @param array $describe
     * @return PrfTeamClass
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function fetchPrfTeamClass(array $describe) : PrfTeamClass
    {
        /** @var PrfTeamClassRepository $repository */
        $repository = $this->entityManager->getRepository(PrfTeamClass::class);
        $valArray = [];
        if (!$this->prfTeamClassExists($describe)) {
            foreach ($describe as $key => $value) {
                $valArray[] = $this->value[$key][$value];
            }
            /** @var PrfTeamClass $prfTeamClass */
            $prfTeamClass = $repository->create($describe, $valArray);
            $this->setPrfTeamClass($describe,$prfTeamClass);
            return $prfTeamClass;
        }
        return $this->getPrfTeamClass($describe);
    }


    private function prfTeamClassExists(array $describe)
    {
        $type=$describe['type'];
        $status=$describe['status'];
        $sex=$describe['sex'];
        $proficiency=$describe['proficiency'];
        if(isset($this->prfTeamClass[$type][$status][$sex][$proficiency])){
            return true;
        }
        return false;
    }


    /**
     * @param array $describe
     * @param PrfTeamClass $prfTeamClass
     */
    private function setPrfTeamClass(array $describe, PrfTeamClass $prfTeamClass)
    {
        $type=$describe['type'];
        $status=$describe['status'];
        $sex=$describe['sex'];
        $proficiency=$describe['proficiency'];
        if(!isset($this->prfTeamClass[$type])){
            $this->prfTeamClass[$type]=[];
        }
        if(!isset($this->prfTeamClass[$type][$status])) {
            $this->prfTeamClass[$type][$status]=[];
        }
        if(!isset($this->prfTeamClass[$type][$status][$sex])) {
            $this->prfTeamClass[$type][$status][$sex]=[];
        }
        $this->prfTeamClass[$type][$status][$sex][$proficiency]=$prfTeamClass;
    }

    private function getPrfTeamClass(array $describe)
    {
        $type = $describe['type'];
        $status = $describe['status'];
        $sex = $describe['sex'];
        $proficiency = $describe['proficiency'];
        return $this->prfTeamClass[$type][$status][$sex][$proficiency];
    }

    /**
     * @param string $type
     * @param string $status
     * @param array $sexes
     * @param array $proficiencies
     * @return array
     * @throws AppBuildException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function prfTeamCollectionBuild(string $type, string $status,array $sexes,array $proficiencies)
    {
        $teamClasses = new ArrayCollection();
        foreach ($sexes as $sex) {
            foreach ($proficiencies as $teamProficiency => $partnerProficiencyList) {
                $describe = ['type' => $type, 'status' => $status, 'sex' => $sex, 'proficiency' => $teamProficiency];
                $prfTeamClass = $this->fetchPrfTeamClass($describe);
                $prfTeamClassId= $prfTeamClass->getId();
                if(!$teamClasses->containsKey($prfTeamClassId)) {
                    $teamClasses->set($prfTeamClassId, $prfTeamClass);
                }
                $this->collectPrfPersonsIntoPrfTeams(
                                    $prfTeamClass,
                                    $partnerProficiencyList);
            }
        }
        return $teamClasses->toArray();
    }

    /**
     * @param AgeTeamClass $ageTeamClass
     * @param array $personAgeRanges
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function collectAgePersonsIntoTeams(
        AgeTeamClass $ageTeamClass,
        array $personAgeRanges)
    {
        $describe = $ageTeamClass->getDescribe();
        $type = $describe['type'];
        $status = $describe['status'];

        $typeList = explode('-',$type);
        $statusList = explode('-',$status);
        $collectionOfCollection = [];
        $designateList= ['A','B'];
        foreach($personAgeRanges as $idx=>$ageRange) {
            list($lower,$upper) = explode('-',$ageRange);
            $lb = intval($lower);
            $ub = intval($upper);
            $collection = [];
            for($i = $lb; $i<=$ub; $i++) {
                $collection[] = $this->person[$typeList[$idx]][$statusList[$idx]]['age'][$i][$designateList[$idx]];
            }
            $collectionOfCollection[]=$collection;
        }
        /** @var AgeTeamRepository $repository */
        $repository = $this->entityManager->getRepository(AgeTeam::class);
        switch(count($collectionOfCollection)) {
            case 1:
                foreach($collectionOfCollection[0] as $person){
                    $team=$repository->create($ageTeamClass,[$person],[]);
                    $this->addAgeTeamToInventory($team);
                }
                break;
            case 2:
                foreach($collectionOfCollection[0] as $personLeft) {
                    foreach($collectionOfCollection[1] as $personRight) {
                        $team = $repository->create($ageTeamClass,[$personLeft,$personRight],[]);
                        $this->addAgeTeamToInventory($team);
                    }
                }
        }

    }



    private function addPrfTeamToInventory(PrfTeam $team)
    {
        $describe=$team->getPrfTeamClass()->getDescribe();
        $type = $describe['type'];
        $status = $describe['status'];
        $sex = $describe['sex'];
        $proficiency = $describe['proficiency'];
        if(!isset($this->team[$type][$status]['prf'][$sex])){
            $this->team[$type][$status]['prf'][$sex]=[];
        }
        if(!isset($this->team[$type][$status]['prf'][$sex][$proficiency])){
            $this->team[$type][$status]['prf'][$sex][$proficiency]=new ArrayCollection();
        }
        /** @var ArrayCollection $collection */
        $collection = $this->team[$type][$status]['prf'][$sex][$proficiency];
        $collection->set($team->getId(),$team);
    }

    private function addAgeTeamToInventory(AgeTeam $team)
    {
        $describe=$team->getAgeTeamClass()->getDescribe();
        $type = $describe['type'];
        $status = $describe['status'];
        $age = $describe['age'];
        if(!isset($this->team[$type][$status]['age'][$age])) {
            $this->team[$type][$status]['age'][$age]=new ArrayCollection();
        }
        /** @var ArrayCollection $collection */
        $collection = $this->team[$type][$status]['age'][$age];
        $collection->set($team->getId(),$team);
    }

    /**
     * @param PrfTeamClass $prfTeamClass
     * @param array $partnerProficiencyList
     * @return array
     * @throws AppBuildException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function collectPrfPersonsIntoPrfTeams(
        PrfTeamClass $prfTeamClass,
        array $partnerProficiencyList)
    {
        $teamList = [];
        /** @var PrfTeamRepository $repository */
        $repository = $this->entityManager->getRepository(PrfTeam::class);
        $personCouplingCollection = $this->findPrfPersonsForPrfTeams($prfTeamClass,$partnerProficiencyList);
        foreach($personCouplingCollection as $couplingOrSolo) {
           $team=$repository->create($prfTeamClass,$couplingOrSolo);
           $this->addPrfTeamToInventory($team);
           $teamList[]=$team;
        }
        return $teamList;
    }

    /**
     * @param PrfTeamClass $prfTeamClass
     * @param array $partnerProficiencyList
     * @return array
     * @throws AppBuildException
     */
    private function findPrfPersonsForPrfTeams(PrfTeamClass $prfTeamClass,
                                               array $partnerProficiencyList)
    {
        $arr = [];
        $describe = $prfTeamClass->getDescribe();
        $type=$describe['type'];
        $status = $describe['status'];
        $sex = $describe['sex'];
        $teamProficiency = $describe['proficiency'];
        $typeList = explode('-',$type);
        $statusList = explode('-',$status);
        $sexList = explode('-',$sex);
        switch(count($statusList)) {
            case 1:
                if(!isset($this->person[$typeList[0]][$statusList[0]]['prf'][$sexList[0]][$teamProficiency]['A'])) {
                    $indexing = [$typeList[0], $statusList[0], 'prf', $sexList[0], $teamProficiency, 'A'];
                    throw new AppBuildException(AppExceptionCodes::BAD_INDEX,
                        [__FILE__, __LINE__ - 3, 'person', $indexing]);
                }
                $arr[] =  [$this->person[$typeList[0]][$statusList[0]]['prf'][$sexList[0]][$teamProficiency]['A']];
                break;
            case 2:
                foreach($partnerProficiencyList as $partnerProficiency) {
                    list($leftProficiency,$rightProficiency)
                            =$this->proficiencySelection($status,$teamProficiency,$partnerProficiency);
                    if(!isset($this->person[$typeList[0]][$statusList[0]]['prf'][$sexList[0]][$leftProficiency]['A'])) {
                        $indexing = [$typeList[0], $statusList[0], 'prf', $sexList[0], $leftProficiency, 'A'];
                        throw new AppBuildException(AppExceptionCodes::BAD_INDEX,
                            [__FILE__, __LINE__ - 3, 'person', $indexing]);
                    }
                    if(!isset($this->person[$typeList[1]][$statusList[1]]['prf'][$sexList[1]][$rightProficiency]['B'])) {
                        $indexing = [$typeList[1], $statusList[1], 'prf', $sexList[1], $rightProficiency, 'B'];
                        throw new AppBuildException(AppExceptionCodes::BAD_INDEX,
                            [__FILE__, __LINE__ - 3, 'person', $indexing]);
                    }
                    $a = $this->person[$typeList[0]][$statusList[0]]['prf'][$sexList[0]][$leftProficiency]['A'];
                    $b = $this->person[$typeList[1]][$statusList[1]]['prf'][$sexList[1]][$rightProficiency]['B'];
                    $arr[]=[$a,$b];
                    if($sexList[0]!=$sexList[1]){
                        if($leftProficiency!=$rightProficiency){
                            $a = $this->person[$typeList[0]][$statusList[0]]['prf'][$sexList[1]][$leftProficiency]['A'];
                            $b = $this->person[$typeList[1]][$statusList[1]]['prf'][$sexList[0]][$rightProficiency]['B'];
                            $arr[]=[$a,$b];
                        }
                    }
                }
                break;
        }
        return $arr;
    }

    /**
     * @param $status
     * @param $teamProficiency
     * @param $partnerProficiency
     * @return array
     * @throws AppBuildException
     */
    private function proficiencySelection($status,$teamProficiency,$partnerProficiency)
    {
        switch($status){
            case 'Teacher-Student':
                return [$partnerProficiency,$teamProficiency];
            case 'Student-Student':
            case 'Teacher-Teacher':
                return [$teamProficiency,$partnerProficiency];
        }
        $indexing=[$status,$teamProficiency,$partnerProficiency];
        throw new AppBuildException(AppExceptionCodes::UNHANDLED_CONDITION,
            [__FILE__,__LINE__-10, 'proficiencySelection',$indexing]);
    }
}