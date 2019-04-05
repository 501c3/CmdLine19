<?php
/**
 * Copyright (c) 2018. Mark Garber.  All rights reserved.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 12/1/18
 * Time: 9:31 PM
 */

namespace App\Common;


use App\Entity\Setup\AgePerson;
use App\Entity\Setup\PrfPerson;
use App\Repository\Setup\AgePersonRepository;
use App\Repository\Setup\PrfPersonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

class YamlDbSetupPerson extends YamlDbSetupBase
{
    const PERSON_DOMAIN_KEYS = ['type','status','sex','age','proficiency','designate'];

    protected $person = [];

    public function __construct(EntityManagerInterface $entityManager, EventDispatcher $dispatcher=null)
    {
        parent::__construct($entityManager, $dispatcher);
    }

    /**
     * @param string $file
     * @return array
     * @throws AppParseException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public function parsePersons(string $file)
    {
        $agePrfPositionArray = YamlPosition::yamlAddPosition($file);
        foreach($agePrfPositionArray as $records){
            foreach($records as $keyPosition=>$valueList) {
                list($key,$position) = explode('|',$keyPosition);
                if(!in_array($key,self::PERSON_DOMAIN_KEYS)) {
                    throw new AppParseException(AppExceptionCodes::FOUND_BUT_EXPECTED,
                        [$file,$key,$position,self::PERSON_DOMAIN_KEYS]);
                }
            }
            $keysPositions = array_keys($records);
            $keysFound = YamlPosition::isolate($keysPositions);
            $difference = array_diff(self::PERSON_DOMAIN_KEYS, $keysFound);
            if (count($difference)) {
                throw new AppParseException(AppExceptionCodes::MISSING_KEYS,
                    [$file, $difference, $keysPositions]);
            }
            $cache = [];
            foreach ($records as $keyPosition => $dataPosition) {
                list($key) = explode('|', $keyPosition);
                $cache[$key] = $this->personValuesCheck($file, $key, $dataPosition);
            }
            $this->personValuesBuild($cache);
            $this->sendWorkingStatus();
        }
        return $this->person;
    }

    /**
     * @param string $file
     * @param string $key
     * @param $valuesPositions
     * @return array|string
     * @throws AppParseException
     * @throws \Exception
     *
     */
    private function personValuesCheck(string $file, string $key, $valuesPositions)
    {
        switch($key) {
            case 'type':
            case 'status':
                list($value,$position) = explode('|',$valuesPositions);
                if(!isset($this->value[$key][$value])) {
                    throw new AppParseException(AppExceptionCodes::UNRECOGNIZED_VALUE,
                        [$file, $value, $position]);
                }
                break;
            case 'age':
                list($range,$position) = explode('|',$valuesPositions);
                $result = preg_match('/(?P<lower>\w+)\-(?P<upper>\w+)/',$range, $bound);
                if(!$result ||
                    (!is_numeric($bound['lower'])) || !is_numeric($bound['upper']) ||
                    ($bound['lower']>$bound['upper'])) {
                    throw new AppParseException(AppExceptionCodes::INVALID_RANGE, [$file,$range,$position]);
                }
                break;
            case 'sex':
            case 'proficiency':
            case 'designate':
                foreach($valuesPositions as $valuePos) {
                    list($value,$position)=explode('|',$valuePos);
                    /** @var YamlDbSetupBase $this */
                    if(!isset($this->value[$key][$value])) {
                        throw new AppParseException(AppExceptionCodes::UNRECOGNIZED_VALUE,
                            [$file,$value,$position]);
                    }
                }
        }
        return YamlPosition::isolate($valuesPositions);
    }

    /**
     * @param array $cache
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function personValuesBuild(array $cache)
    {
        $type = $cache['type'];
        $status = $cache['status'];
        if(!isset($this->person[$type])) {
            $this->person[$type]=[];
        }
        if(!isset($this->person[$type][$status])) {
            $this->person[$type][$status] = [];
        }
        if(!isset($this->person[$type][$status])){
            $this->person[$type][$status] = ['age'=>[],'prf'=>[]];
        }
        $prfPersons = $this->prfPersonValuesBuild($type,$status,$cache['sex'],
                                            $cache['proficiency'],$cache['designate']);
        $this->agePersonValuesBuild($type,$status,$cache['age'],$cache['designate'],$prfPersons);
    }

    /**
     * @param string $type
     * @param string $status
     * @param string $ageRange
     * @param array $designates
     * @param array $prfPersons
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function agePersonValuesBuild(string $type,
                                          string $status,
                                          string $ageRange,
                                          array $designates,
                                          array $prfPersons)
    {
          /** @var AgePersonRepository $repository */
        $repository = $this->entityManager->getRepository(AgePerson::class);
        list($s1,$s2) = explode('-',$ageRange);
        $lb = intval($s1);
        $ub = intval($s2);
        $describe = ['type'=>$type,'status'=>$status];
        $values = [$this->value['type'][$type],$this->value['status'][$status]];
        for($i = $lb; $i<=$ub; $i++) {
            $describe1 = $describe;
            $describe1['years']=$i;
            if(!isset($this->person[$type][$status]['age'][$i])){
                $this->person[$type][$status]['age'][$i]=[];
                foreach($designates as $designate) {
                    $describe2 = $describe1;
                    $describe2['designate']=$designate;
                    if(!isset($this->person[$type][$status]['age'][$i][$designate])) {
                        $agePerson = $repository->create($describe2,$values,$prfPersons);
                        $this->person[$type][$status]['age'][$i][$designate]=$agePerson;
                    }
                }
            }
        }
    }

    /**
     * @param $type
     * @param $status
     * @param $sexes
     * @param $proficiencies
     * @param $designates
     * @return array
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function prfPersonValuesBuild($type,$status,$sexes,$proficiencies,$designates)
    {
        $arr = [];
        /** @var PrfPersonRepository $repository */
        $repository = $this->entityManager->getRepository(PrfPerson::class);
        $describe = ['type'=>$type,'status'=>$status];
        foreach($sexes as $sex){
            $describe1 = $describe;
            $describe1['sex']=$sex;
            if(!isset($this->person[$type][$status]['prf'][$sex])){
                $this->person[$type][$status]['prf'][$sex]=[];
            }
            foreach($proficiencies as $proficiency) {
                $describe2 = $describe1;
                $describe2['proficiency']=$proficiency;
                if(!isset($this->person[$type][$status]['prf'][$sex][$proficiency])) {
                    $this->person[$type][$status]['prf'][$sex][$proficiency]=[];
                }
                foreach($designates as $designate) {
                    $describe3 = $describe2;
                    $describe3['designate']=$designate;
                    $collection = [];
                    foreach($describe3 as $key=>$value) {
                        $collection[]=$this->value[$key][$value];
                    }
                    if(!isset($this->person[$type][$status]['prf'][$sex][$proficiency][$designate])){
                        $prfPerson = $repository->create($describe3,$collection);
                        $this->person[$type][$status]['prf'][$sex][$proficiency][$designate]=$prfPerson;
                        $arr[]=$prfPerson;
                    }
                }
            }
        }
        return $arr;
    }
}