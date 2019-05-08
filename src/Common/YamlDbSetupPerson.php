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
use App\Entity\Setup\Person;
use App\Entity\Setup\PrfPerson;
use App\Entity\Setup\Value;
use App\Repository\Setup\AgePersonRepository;
use App\Repository\Setup\PersonRepository;
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
        $valueRepository = $this->entityManager->getRepository(Value::class);
        $valueQuickSearch = $valueRepository->fetchQuickSearch();
        if(!isset($this->person[$type])) {
            $this->person[$type]=[];
        }
        if(!isset($this->person[$type][$status])) {
            $this->person[$type][$status] = [];
        }
        list($lowerAge,$upperAge) = explode('-',$cache['age']);
        $lb = (int) $lowerAge; $ub=(int) $upperAge;
        /** @var PersonRepository $personRepository */
        $personRepository = $this->entityManager->getRepository(Person::class);
        foreach($cache['sex'] as $sex){
            if(!isset($this->person[$type][$status][$sex])) {
                $this->person[$type][$status][$sex]=[];
            }
            foreach($cache['designate'] as $designate) {
                if(!isset($this->person[$type][$status][$sex][$designate])) {
                    $this->person[$type][$status][$sex][$designate]=[];
                }
                foreach($cache['proficiency'] as $proficiency) {
                    if(!isset($this->person[$type][$status][$sex][$designate][$proficiency])) {
                        $this->person[$type][$status][$sex][$designate][$proficiency]=[];
                    }
                    for($years = $lb;$years<=$ub;$years++) {
                        if(!isset($this->person[$type][$status][$sex][$designate][$proficiency][$years])){
                            $values = [];
                            $describe = ['type'=>$type,
                                         'status'=>$status,
                                         'sex'=>$sex,
                                         'designate'=>$designate,
                                         'proficiency'=>$proficiency,
                                         'years'=>$years];
                            foreach(['type','status','sex','designate','proficiency'] as $domain) {
                                $values[]=$valueQuickSearch[$domain][$describe[$domain]];
                            }
                            $this->person[$type][$status][$sex][$designate][$proficiency][$years]=
                                $personRepository->create($describe,$years,$values);
                        }
                    }
                }
            }
        }
    }
}