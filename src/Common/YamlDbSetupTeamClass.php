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


use App\Entity\Setup\Person;
use App\Entity\Setup\Team;
use App\Entity\Setup\TeamClass;
use App\Entity\Setup\Value;
use App\Repository\Setup\AgeTeamClassRepository;
use App\Repository\Setup\AgeTeamRepository;
use App\Repository\Setup\PersonRepository;
use App\Repository\Setup\PrfTeamRepository;
use App\Repository\Setup\TeamRepository;
use App\Repository\Setup\ValueRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\EventDispatcher\EventDispatcher;

class YamlDbSetupTeamClass extends YamlDbSetupPerson
{
   const TEAM_DOMAIN_KEYS = ['type','status','sex','age','proficiency'];

   private $team = [];

   public function __construct(EntityManagerInterface $entityManager, EventDispatcher $dispatcher = null)
   {
       parent::__construct($entityManager, $dispatcher);
       /** @var ValueRepository $valueRepository */
       $valueRepository=$this->entityManager->getRepository(Value::class);
       /** @var PersonRepository $personRepository */
       $personRepository=$this->entityManager->getRepository(Person::class);
       $this->value=$valueRepository->fetchQuickSearch();
       $this->person=$personRepository->fetchQuickSearch();
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
        $classRepository = $this->entityManager->getRepository(TeamClass::class);
        /** @var TeamRepository $teamRepository */
        $teamRepository = $this->entityManager->getRepository(Team::class);
        $type = $cache['type'];
        $status = $cache['status'];
        if(!isset($this->team[$type])) {
            $this->team[$type]=[];
        }
        if(!isset($this->team[$type][$status])) {
            $this->team[$type][$status] = [];
        }
        foreach($cache['sex'] as $sex)  {
            $describe = ['type'=>$type, 'status'=>$status, 'sex'=>$sex];
            if(!isset($this->team[$type][$status][$sex])) {
                $this->team[$type][$status][$sex] = [];
            }
            foreach($cache['proficiency'] as $teamProficiency=>$partnerProficiencyList) {
                $describe1 = $describe;
                $describe1['proficiency']=$teamProficiency;
                if(!isset($this->team[$type][$status][$sex][$teamProficiency])) {
                    $this->team[$type][$status][$sex][$teamProficiency]=[];
                }
                foreach($cache['age'] as $teamAge=>$teamAgeRanges) {
                    $describe2=$describe1;
                    $describe2['age']=$teamAge;
                    $class = $classRepository->create($describe2);
                    $personsInTeams = $this->personsInTeams($class,$partnerProficiencyList,$teamAgeRanges);
                    $teams = $teamRepository->createTeamList($class, $personsInTeams);
                    $this->team[$type][$status][$sex][$teamProficiency][$teamAge]=$teams;
                }
            }
        }
    }


    /**
     * @param TeamClass $class
     * @param array $partnerProficiencyList
     * @param array $personAgeRanges
     * @return array
     * @throws AppBuildException
     */
    private function personsInTeams(TeamClass $class, array $partnerProficiencyList, array $personAgeRanges): array
    {
        $describe = $class->getDescribe();
        $teamProficiency = $describe['proficiency'];
        $leadProficiency = $teamProficiency;
        $type = explode('-',$describe['type']);
        $status= explode('-',$describe['status']);
        $sex = explode('-',$describe['sex']);
        $designate = ['A','B'];
        $teamCollection = [];
        switch(count($type)) {
            case 1:
                list($lb,$ub) = explode('-',$personAgeRanges[0]);
                for($i=$lb;$i<=$ub;$i++) {
                    if(!isset($this->person[$type[0]][$status[0]][$sex[0]][$designate[0]][$teamProficiency][$i])) {
                        $indexing = [$type[0], $status[0], $sex[0], 'A', $teamProficiency, $i];
                        throw new AppBuildException(AppExceptionCodes::BAD_INDEX,
                            [__FILE__, __LINE__ - 3, 'person', $indexing]);
                    }
                    $person = $this->person[$type[0]][$status[0]][$sex[0]][$designate[0]][$teamProficiency][$i];
                    $teamCollection[]=[$person];
                }
                break;
            case 2:
                list($lb0,$ub0)=explode('-',$personAgeRanges[0]);
                list($lb1,$ub1)=explode('-',$personAgeRanges[1]);
                for($i=$lb0;$i<=$ub0;$i++) {
                    if(!isset($this->person[$type[0]][$status[0]][$sex[0]][$designate[0]][$leadProficiency][$i])) {
                        $indexing = [$type[0], $status[0], $sex[0], $designate[0],$leadProficiency , $i];
                        throw new AppBuildException(AppExceptionCodes::BAD_INDEX,
                            [__FILE__, __LINE__ - 3, 'person', $indexing]);
                    }
                    $a = $this->person[$type[0]][$status[0]][$sex[0]][$designate[0]][$leadProficiency][$i];
                    $_a= $this->person[$type[0]][$status[0]][$sex[1]][$designate[0]][$leadProficiency][$i];

                    foreach($partnerProficiencyList as $followProficiency) {
                        if(!isset($this->person[$type[0]][$status[0]][$sex[0]][$designate[0]][$followProficiency][$i])) {
                            $indexing = [$type[0], $status[0], $sex[0], $designate[0],$leadProficiency , $i];
                            throw new AppBuildException(AppExceptionCodes::BAD_INDEX,
                                [__FILE__, __LINE__ - 3, 'person', $indexing]);
                        }
                        $a_=$this->person[$type[0]][$status[0]][$sex[0]][$designate[0]][$followProficiency][$i];
                        for($j=$lb1;$j<=$ub1;$j++) {
                            if(!isset($this->person[$type[1]][$status[1]][$sex[1]][$designate[1]][$followProficiency][$j])) {
                                $indexing = [$type[0], $status[0], $sex[0], $designate[0],$followProficiency,$j];
                                throw new AppBuildException(AppExceptionCodes::BAD_INDEX,
                                    [__FILE__, __LINE__ - 3, 'person', $indexing]);
                            }
                            $b = $this->person[$type[1]][$status[1]][$sex[1]][$designate[1]][$followProficiency][$j];
                            $_b=$this->person[$type[1]][$status[1]][$sex[0]][$designate[1]][$followProficiency][$j];
                            $b_=$this->person[$type[1]][$status[1]][$sex[1]][$designate[1]][$leadProficiency][$j];
                            $teamCollection[]=[$a,$b];
                            if($leadProficiency!=$followProficiency) {
                               $teamCollection[]=[$a_,$b_];
                            }
                            if($sex[0]!=$sex[1]) {
                                $teamCollection[]=[$_a,$_b];
                            }
                        }
                    }
                }
                break;
        }
        return $teamCollection;
    }
}