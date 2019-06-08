<?php
/**
 * Copyright (c) 2018. Mark Garber.  All rights reserved.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 12/21/18
 * Time: 9:27 PM
 */

namespace App\Common;


use App\Entity\Setup\Event;
use App\Entity\Setup\Model;
use App\Entity\Setup\TeamClass;
use App\Entity\Setup\Value;
use App\Repository\Setup\EventRepository;
use App\Repository\Setup\ModelRepository;
use App\Repository\Setup\TeamClassRepository;
use App\Repository\Setup\ValueRepository;
use App\Signal\ProcessEvent;
use App\Signal\ProcessStatus;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

class YamlDbSetupEventTeam
{
    const DOMAIN_KEYS = ['type','status','sex','proficiency','age'];


    /** @var EntityManagerInterface */
    private $entityManager;

    private $models = [];

    private $events = [];

    private $modelValues = [];

    private $teamClasses;

    private $values;

    private $file;
    /**
     * @var EventDispatcher
     */
    private $dispatcher;

    public function __construct(EntityManagerInterface $em, EventDispatcher $dispatcher = null)
    {
        $this->entityManager = $em;
        $this->dispatcher = $dispatcher;
    }

    private function initialize()
    {
        /** @var ModelRepository $modelRepository */
        $modelRepository = $this->entityManager->getRepository(Model::class);
        /** @var EventRepository $eventsRepository */
        $eventsRepository = $this->entityManager->getRepository(Event::class);
        $allModels = $modelRepository->findAll();
        /** @var Model $model */
        foreach ($allModels as $model) {
            $modelName = $model->getName();
            $this->models[$modelName] = $model;
            $this->events[$modelName] = $eventsRepository->fetchQuickSearch($model);
        }
        $this->modelValues = $modelRepository->fetchQuickSearch();
        /** @var TeamClassRepository $teamClassRepository */
        $teamClassRepository = $this->entityManager->getRepository(TeamClass::class);
        $this->teamClasses = $teamClassRepository->fetchQuickSearch();
        /** @var ValueRepository $valueRepository */
        $valueRepository = $this->entityManager->getRepository(Value::class);
        $this->values = $valueRepository->fetchQuickSearch();
    }

    /**
     * @param string $file
     * @return array
     * @throws AppParseException
     * @throws \Exception
     */
    public function parseEventsTeams(string $file)
    {
        $this->file = $file;
        $this->initialize();
        $modelRelations = YamlPosition::yamlAddPosition($file);
        foreach ($modelRelations as $modelPosition => $relationDataPositionList) {
            list($model, $position) = explode('|', $modelPosition);
            $validModelNames = array_keys($this->models);
            if (!in_array($model, $validModelNames)) {
                throw new AppParseException(AppExceptionCodes::FOUND_BUT_EXPECTED,
                    [$this->file, $model, $position, $validModelNames]);
            }
            foreach ($relationDataPositionList as $grouping) {
                $this->validateRelationValues($model, $grouping);
            }
        }
        $preBuild = YamlPosition::isolate($modelRelations);
        $this->buildRelation($preBuild);
        return $this->events;
    }

    /**
     * @param string $model
     * @param array $grouping
     * @throws AppParseException
     */
    private function validateRelationValues(string $model, array $grouping)
    {
        foreach ($grouping as $domainKeyPosition => $domainDataPosition) {
            list($key, $position) = explode('|', $domainKeyPosition);
            if (!in_array($key, self::DOMAIN_KEYS)) {
                throw new AppParseException(AppExceptionCodes::FOUND_BUT_EXPECTED,
                    [$this->file, $key, $position, self::DOMAIN_KEYS]);
            }
            $this->validateDomainData($model, $key, $domainDataPosition);
        }
    }

    /**
     * @param string $model
     * @param string $domainKey
     * @param array $domainDataPosition
     * @throws AppParseException
     */

    private function validateDomainData(string $model, string $domainKey, array $domainDataPosition)
    {
        foreach ($domainDataPosition as $eventValuePosition => $teamValuePositionList) {
            list($eventValue, $eventPosition) = explode('|', $eventValuePosition);
            if (!isset($this->modelValues[$model][$domainKey][$eventValue])) {
                throw new AppParseException(AppExceptionCodes::UNRECOGNIZED_VALUE,
                    [$this->file, $eventValue, $eventPosition]);
            }
            if (!is_array($teamValuePositionList)) {
                throw new AppParseException(AppExceptionCodes::EXPECTED_STRUCTURE,
                    [$this->file, $eventValue, $eventPosition]);
            }
            foreach ($teamValuePositionList as $teamValuePosition) {
                list($teamValue, $teamPosition) = explode('|', $teamValuePosition);
                if (!isset($this->values[$domainKey][$teamValue])) {
                    throw new AppParseException(AppExceptionCodes::UNRECOGNIZED_VALUE,
                        [$this->file, $teamValue, $teamPosition]);
                }
            }
        }
    }

    /**
     * @param array $preBuild
     * @throws AppBuildException
     * @throws \Exception
     */
    private function buildRelation(array $preBuild)
    {
        /** @var TeamClassRepository $repositoryTeamClass */
        $repositoryTeamClass = $this->entityManager->getRepository(TeamClass::class);
        $teamClassesHash = $repositoryTeamClass->fetchQuickSearch();
        /** @var EventRepository $repositoryEvents */
        $repositoryEvents = $this->entityManager->getRepository(Event::class);
        $eventHash = $repositoryEvents->fetchPreEligibility();
        foreach($preBuild as $model=>$relationDataList) {
            foreach($relationDataList as $grouping) {
                $types = $grouping['type'];
                $statuses = $grouping['status'];
                $sexes = $grouping['sex'];
                $ages = $grouping['age'];
                $proficiencies = $grouping['proficiency'];
                foreach($types as $eventType => $teamTypes){
                    foreach($statuses as $eventStatus=>$teamStatuses) {
                        foreach($sexes as $eventSex=>$teamSexes) {
                            foreach($proficiencies as $eventProficiency=>$teamProficiencies) {
                                foreach($ages as $eventAge=>$teamAges) {
                                    if(!isset($eventHash[$model][$eventType][$eventStatus]
                                                        [$eventSex][$eventProficiency][$eventAge])) {
                                        $index = [$model,$eventType,$eventStatus,$eventSex,$eventProficiency,$eventAge];
                                        throw new AppBuildException(AppExceptionCodes::BAD_INDEX,
                                                    [__FILE__,__LINE__-4,'eventHash',$index]);
                                    }
                                    $eventCollection = $eventHash[$model][$eventType][$eventStatus]
                                                                 [$eventSex][$eventProficiency][$eventAge];
                                    $this->addTeamsToEventCollection(
                                            $eventCollection,
                                            $teamClassesHash,
                                            $teamTypes,
                                            $teamStatuses,
                                            $teamSexes,
                                            $teamProficiencies,
                                            $teamAges);
                                }
                            }
                        }
                    }
                }
                $this->sendWorkingStatus();
            }
        }
        $this->entityManager->flush();
    }

    /**
     * @param ArrayCollection $eventCollection
     * @param array $teamClassesHash
     * @param array $teamTypes
     * @param array $teamStatuses
     * @param array $teamSexes
     * @param array $teamProficiencies
     * @param array $teamAges
     */
    private function addTeamsToEventCollection(
        ArrayCollection $eventCollection,
        array $teamClassesHash,
        array $teamTypes,
        array $teamStatuses,
        array $teamSexes,
        array $teamProficiencies,
        array $teamAges)
    {
        /** @var Event $event */
        $event = $eventCollection->first();
        while($event) {
            $eventTeams = $event->getTeamClass();
            foreach($teamTypes as $type) {
                /** @var string $status */
                foreach($teamStatuses as $status) {
                    /** @var string $sex */
                    foreach($teamSexes as $sex) {
                        /** @var string $proficiency */
                        foreach($teamProficiencies as $proficiency) {
                            /** @var string $age */
                            foreach($teamAges as $age) {
                                if(isset($teamClassesHash[$type][$status][$sex][$proficiency][$age])) {
                                    /** @var TeamClass $teamClass */
                                    $teamClass = $teamClassesHash[$type][$status][$sex][$proficiency][$age];
                                    $eventTeams->set($teamClass->getId(), $teamClass);
                                }
//                                else {
//                          TODO: Write to database
//                                    $index = [$type,$status,$sex,$proficiency,$age];
//                                    throw new AppBuildException(AppExceptionCodes::BAD_INDEX,
//                                        [__FILE__,__LINE__-2,'teamClassesHash',$index]);
//                                }
                            }
                        }
                    }
                }
            }
            $event = $eventCollection->next();
        }
    }

    private function sendWorkingStatus()
    {
        if(isset($this->dispatcher)) {
            $event = new ProcessEvent(new ProcessStatus(ProcessStatus::WORKING, 1));
            $this->dispatcher->dispatch('process.update',$event);
        }
    }
}