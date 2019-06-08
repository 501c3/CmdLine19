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


use App\Entity\Setup\AgeClass;
use App\Entity\Setup\AgeTeam;
use App\Entity\Setup\Person;
use App\Entity\Setup\PrfClass;
use App\Entity\Setup\PrfTeam;
use App\Entity\Setup\TeamClass;
use App\Entity\Setup\Tss;
use App\Entity\Setup\Value;
use App\Repository\Setup\AgeClassRepository;
use App\Repository\Setup\AgeTeamClassRepository;
use App\Repository\Setup\AgeTeamRepository;
use App\Repository\Setup\PersonRepository;
use App\Repository\Setup\PrfClassRepository;
use App\Repository\Setup\PrfTeamRepository;
use App\Repository\Setup\TeamRepository;
use App\Repository\Setup\TssRepository;
use App\Repository\Setup\ValueRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use Symfony\Component\EventDispatcher\EventDispatcher;

class YamlDbSetupTeamClass extends YamlDbSetupPerson
{
    const TEAM_DOMAIN_KEYS = ['type', 'status', 'sex', 'age', 'proficiency'];

    private $teamClass = [];

    /** @var PrfTeamRepository */
    private $prfTeamRepository;

    /** @var AgeTeamRepository */
    private $ageTeamRepository;

    /** @var PrfClassRepository */
    private $prfClassRepository;

    /** @var AgeClassRepository */
    private $ageClassRepository;

    /** @var TssRepository */
    private $tssRepository;

    public function __construct(EntityManagerInterface $entityManager, EventDispatcher $dispatcher = null)
    {
        parent::__construct($entityManager, $dispatcher);
        /** @var ValueRepository $valueRepository */
        $valueRepository = $this->entityManager->getRepository(Value::class);
        /** @var PersonRepository $personRepository */
        $personRepository = $this->entityManager->getRepository(Person::class);
        $this->value = $valueRepository->fetchQuickSearch();
        $this->person = $personRepository->fetchQuickSearch();
        $this->prfTeamRepository = $this->entityManager->getRepository(PrfTeam::class);
        $this->ageTeamRepository = $this->entityManager->getRepository(AgeTeam::class);
        $this->prfClassRepository = $this->entityManager->getRepository(PrfClass::class);
        $this->ageClassRepository = $this->entityManager->getRepository(AgeClass::class);
        $this->tssRepository = $this->entityManager->getRepository(Tss::class);
    }

    /**
     * @param string $file
     * @return array
     * @throws AppParseException
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws Exception
     */
    public function parseTeams(string $file)
    {
        $agePrfPositionArray = YamlPosition::yamlAddPosition($file);
        foreach ($agePrfPositionArray as $records) {
            foreach ($records as $keyPosition => $valueList) {
                list($key, $position) = explode('|', $keyPosition);
                if (!in_array($key, self::TEAM_DOMAIN_KEYS)) {
                    throw new AppParseException(AppExceptionCodes::FOUND_BUT_EXPECTED,
                        [$file, $key, $position, self::TEAM_DOMAIN_KEYS]);
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

            $this->tssPrfAgeClassBuild($cache);
            $this->teamClassJsonBuild($cache);
            $this->tableBuild();
            $this->sendWorkingStatus();
        }
        return $this->teamClass;
    }

    /**
     * @param $file
     * @param $key
     * @param $valuesPositions
     * @return array|string
     * @throws AppParseException
     * @throws Exception
     */
    public function teamClassValuesCheck($file, $key, $valuesPositions)
    {
        switch ($key) {
            case 'type':
            case 'status':
                if (is_array($valuesPositions)) {
                    $array = explode('|', $valuesPositions[0]);
                    throw new AppParseException(AppExceptionCodes::FOUND_BUT_EXPECTED,
                        [$file, '[', $array[1], 'scaler']);
                }
                list($value, $position) = explode('|', $valuesPositions);
                if (!isset($this->value[$key][$value])) {
                    throw new AppParseException(AppExceptionCodes::UNRECOGNIZED_VALUE,
                        [$file, $value, $position]);
                }
                break;
            case 'age':
                foreach ($valuesPositions as $valuePos => $yearRanges) {
                    list($value, $position) = explode('|', $valuePos);
                    if (!isset($this->value[$key][$value])) {
                        throw new AppParseException(AppExceptionCodes::UNRECOGNIZED_VALUE,
                            [$file, $value, $position]);
                    }
                    foreach ($yearRanges as $rangePosition) {

                        list($range, $position) = explode('|', $rangePosition);
                        $result = preg_match('/(?P<lower>\w+)\-(?P<upper>\w+)/', $range, $bound);
                        if (!$result ||
                            (!is_numeric($bound['lower'])) || !is_numeric($bound['upper']) ||
                            ($bound['lower'] > $bound['upper'])) {
                            throw new AppParseException(AppExceptionCodes::INVALID_RANGE, [$file, $range, $position]);
                        }
                    }
                }
                break;
            case 'sex':
                foreach ($valuesPositions as $valuePos) {
                    list($value, $position) = explode('|', $valuePos);
                    /** @var YamlDbSetupBase $this */
                    if (!isset($this->value[$key][$value])) {
                        throw new AppParseException(AppExceptionCodes::UNRECOGNIZED_VALUE,
                            [$file, $value, $position]);
                    }
                }
                break;
            case 'proficiency':
                foreach ($valuesPositions as $leftProficiencyPosition => $rightProficiencyPositionList) {
                    list($leftValue, $leftPosition) = explode('|', $leftProficiencyPosition);
                    if (!isset($this->value[$key][$leftValue])) {
                        throw new AppParseException(AppExceptionCodes::UNRECOGNIZED_VALUE,
                            [$file, $leftValue, $leftPosition]);
                    }
                    foreach ($rightProficiencyPositionList as $rightProficiencyPosition) {
                        list($rightValue, $rightPosition) = explode('|', $rightProficiencyPosition);
                        if (!isset($this->value[$key][$rightValue])) {
                            throw new AppParseException(AppExceptionCodes::UNRECOGNIZED_VALUE,
                                [$file, $rightValue, $rightPosition]);
                        }
                    }
                }
        }
        return YamlPosition::isolate($valuesPositions);
    }


    /**
     * @param array $cache
     * @throws ORMException
     * @throws OptimisticLockException
     */
    private function teamClassJsonBuild(array $cache)
    {
        $classRepository = $this->entityManager->getRepository(TeamClass::class);
        /** @var TeamRepository $teamRepository */
        //$teamRepository = $this->entityManager->getRepository(Team::class);
        $type = $cache['type'];
        $status = $cache['status'];
        if (!isset($this->teamClass[$type])) {
            $this->teamClass[$type] = [];
        }
        if (!isset($this->teamClass[$type][$status])) {
            $this->teamClass[$type][$status] = [];
        }
        foreach ($cache['sex'] as $sex) {
            $describe = ['type' => $type, 'status' => $status, 'sex' => $sex];
            if (!isset($this->teamClass[$type][$status][$sex])) {
                $this->teamClass[$type][$status][$sex] = [];

            }
            foreach ($cache['proficiency'] as $teamProficiency => $partnerProficiencyList) {
                $describe1 = $describe;
                $describe1['proficiency'] = $teamProficiency;
                if (!isset($this->teamClass[$type][$status][$sex][$teamProficiency])) {
                    $this->teamClass[$type][$status][$sex][$teamProficiency] = [];
                }
                foreach ($cache['age'] as $teamAge => $teamAgeRanges) {
                    $describe2 = $describe1;
                    $describe2['age'] = $teamAge;
                    $teamClass = $classRepository->create($describe2);
                    $this->teamClass[$type][$status][$sex][$teamProficiency][$teamAge] = $teamClass;
                }
            }
        }
    }


    /**
     * @param $cache
     * @throws ORMException
     * @throws OptimisticLockException
     */
    private function tssPrfAgeClassBuild($cache)
    {
        $type = $cache['type'];
        $status = $cache['status'];
        foreach ($cache['sex'] as $sex) {
            /** @var Tss $tss */
            $describe = ['type' => $type, 'status' => $status, 'sex' => $sex];
            $tss = $this->tssRepository->fetch($describe);
            $prfTeams = [];
            $prfClassList = [];
            foreach ($cache['proficiency'] as $teamProficiency => $partnerProficiencyList) {
                /** @var PrfClass $prfClass */
                $prfClass = $this->prfClassRepository->fetch($tss, $teamProficiency);
                $prfClassList[] = $prfClass;
                $prfTeams[$teamProficiency]
                    = $this->prfTeamRepository->createTeams($tss, $prfClass, $partnerProficiencyList);
            }
            $ageTeams = [];
            foreach ($cache['age'] as $teamAge => $personAgeRanges) {
                $ageClass = $this->ageClassRepository->fetch($tss, $teamAge);
                /** @var PrfClass $prfClass */
                foreach ($prfClassList as $prfClass) {
                    $prfClass->addAgeClass($ageClass);
                }
                switch (count(explode('-', $type))) {
                    case 1:
                        list($lb, $ub) = explode('-', $personAgeRanges[0]);
                        $_lb = (int)$lb;
                        $_ub = (int)$ub;
                        $ageTeams[$teamAge]
                            = $this->ageTeamRepository->createTeams($tss, $ageClass, [$_lb, $_ub]);
                        break;
                    case 2:
                        list($lb0, $ub0) = explode('-', $personAgeRanges[0]);
                        list($lb1, $ub1) = explode('-', $personAgeRanges[1]);
                        $_lb0 = (int)$lb0;
                        $_ub0 = (int)$ub0;
                        $_lb1 = (int)$lb1;
                        $_ub1 = (int)$ub1;
                        $ageTeams[$teamAge]
                            = $this->ageTeamRepository->createTeams($tss, $ageClass, [$_lb0, $_ub0], [$_lb1, $_ub1]);
                }
            }
        }
    }

    private function tableBuild()
    {
        $conn = $this->entityManager->getConnection();
    }

}