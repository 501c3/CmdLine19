<?php
/**
 * Copyright (c) 2018. Mark Garber.  All rights reserved.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 12/9/18
 * Time: 2:41 PM
 */

namespace App\Common;


use App\Entity\Setup\Event;
use App\Entity\Setup\Model;
use App\Repository\Setup\EventRepository;
use App\Repository\Setup\ModelRepository;
use App\Signal\ProcessEvent;
use App\Signal\ProcessStatus;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Component\EventDispatcher\EventDispatcher;

class YamlDbSetupEvent
{
    const FIRST_KEYS = ['proficiency', 'age', 'sex', 'type', 'status'],
          SECOND_KEYS = ['tag', 'style'],
          THIRD_KEYS = ['disposition', 'substyle'];
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var string */
    private $file;

    private $modelValues;

    private $disposition;

    /** @var EventRepository */
    private $eventRepository;

    /** @var array */
    private $event;

    /** @var EventDispatcher */
    private $dispatcher;

    public function __construct(EntityManagerInterface $em, EventDispatcher $dispatcher=null)
    {
        $this->entityManager = $em;
        $this->dispatcher = $dispatcher;
    }

    public function initialize()
    {
        /** @var ModelRepository $repository */
        $repository = $this->entityManager->getRepository(Model::class);
        $this->modelValues = $repository->fetchQuickSearch();
        $this->eventRepository = $this->entityManager->getRepository(Event::class);
    }

    /**
     * @param string $file
     * @return mixed
     * @throws \Exception
     */
    public function parseEvents(string $file)
    {
        $this->file = $file;
        $this->initialize();
        $modelsPositions = YamlPosition::yamlAddPosition($file);
        if(is_null($this->modelValues)) {
            /** @var ModelRepository $repository */
            $repository=$this->entityManager->getRepository(Model::class);
            $this->modelValues=$repository->fetchQuickSearch();
        }
        foreach ($modelsPositions as $modelPos => $records) {
            list($model, $position) = explode('|', $modelPos);
            if (!in_array($model, array_keys($this->modelValues))) {
                throw new AppParseException(AppExceptionCodes::UNRECOGNIZED_VALUE,
                    [$this->file, $model, $position]);
            }
            foreach ($records as $record) {
                foreach ($record as $firstKeyPosition => $dataPosition) {
                    list($firstKey, $firstKeyPosition) = explode('|', $firstKeyPosition);
                    if (!in_array($firstKey, self::FIRST_KEYS)) {
                        throw new AppParseException(AppExceptionCodes::FOUND_BUT_EXPECTED,
                            [$this->file, $firstKey, $firstKeyPosition, self::FIRST_KEYS]);
                    }
                    switch ($firstKey) {
                        case 'proficiency':
                            foreach ($dataPosition as $proficiencyPosition => $nextDataPosition) {
                                $this->checkProficiencyData($file, $model, $proficiencyPosition, $nextDataPosition);
                            }

                    }
                }
            }
        }
        $buildData = YamlPosition::isolate($modelsPositions);
        $this->buildEvents($buildData);
        return $this->event;
    }

    /**
     * @param string $file
     * @param string $model
     * @param string $proficiencyPosition
     * @param array $dataPosition
     * @throws AppParseException
     * @throws \Exception
     */
    public function checkProficiencyData(string $file,
                                         string $model,
                                         string $proficiencyPosition,
                                         array $dataPosition)
    {
        list($proficiency, $position) = explode('|', $proficiencyPosition);
        $proficiencies = array_keys($this->modelValues[$model]['proficiency']);
        if (!in_array($proficiency, $proficiencies)) {
            throw new AppParseException(AppExceptionCodes::FOUND_BUT_EXPECTED,
                [$file, $proficiency, $position, $proficiencies]);
        }
        foreach ($dataPosition as $recordList) {
            $keysFound = [];
            $keysPositionsFound = [];
            foreach ($recordList as $keyPosition => $record) {
                list($key, $position) = explode('|', $keyPosition);
                if (!in_array($key, self::SECOND_KEYS)) {
                    throw new AppParseException(AppExceptionCodes::FOUND_BUT_EXPECTED,
                        [$file, $key, $position, self::SECOND_KEYS]);
                }
                switch ($key) {
                    case 'tag':
                        list($tag, $tagPosition) = explode('|', $record);
                        $tags = array_keys($this->modelValues[$model]['tag']);
                        if (!in_array($tag, $tags)) {
                            throw new AppParseException(AppExceptionCodes::FOUND_BUT_EXPECTED,
                                [$file, $tag, $tagPosition, $tags]);
                        }
                        break;
                    case 'style':
                        $this->checkStyleEvents($file, $model, $record);
                }
                array_push($keysFound, $key);
                array_push($keysPositionsFound, $keyPosition);
            }
            $diff = array_diff(self::SECOND_KEYS, $keysFound);
            $deficientArea = YamlPosition::isolate($keysPositionsFound, YamlPosition::POSITION);
            if (count($diff)) {
                throw new AppParseException(AppExceptionCodes::MISSING_KEYS,
                    [$file, $diff, $deficientArea]);
            }
        }
    }

    /**
     * @param string $file
     * @param string $model
     * @param array $record
     * @throws AppParseException
     */
    private function checkStyleEvents(string $file, string $model, array $record)
    {
        foreach ($record as $styleKeyPosition => $eventsPositions) {
            list($style, $position) = explode('|', $styleKeyPosition);
            $styleList = array_keys($this->modelValues[$model]['style']);
            if (!in_array($style, $styleList)) {
                throw new AppParseException(AppExceptionCodes::FOUND_BUT_EXPECTED,
                    [$file, $style, $position, $styleList]);
            }
            $this->checkEventDances($file, $model, $eventsPositions);
        }
    }

    /**
     * @param string $file
     * @param string $model
     * @param array $eventsPositions
     * @throws AppParseException
     */
    private function checkEventDances(string $file, string $model, array $eventsPositions)
    {
        $validKeyFound = [];
        $validKeyPositions = [];
        foreach($eventsPositions as $thirdKeyPosition=>$dataPosition) {
            list($key,$position) = explode('|',$thirdKeyPosition);
            if(!in_array($key,self::THIRD_KEYS)) {
                throw new AppParseException(AppExceptionCodes::FOUND_BUT_EXPECTED,
                    [$file,$key,$position,self::THIRD_KEYS]);
            }
            switch($key) {
                case 'disposition':
                    list($disposition,$dispositionPos) = explode('|',$dataPosition);
                    $this->disposition = $disposition;
                    $dispositionList = ['multiple-events','single-event'];
                    if(!in_array($disposition,$dispositionList)){
                        throw new AppParseException(AppExceptionCodes::FOUND_BUT_EXPECTED,
                            [$file,$disposition,$dispositionPos,$dispositionList]);
                    }
                    $validKeyFound[] = $disposition;
                    $validKeyPositions[] = $dispositionPos;
                    break;
                case 'substyle':
                    $this->checkSubstyleDances($file,$model,$this->disposition,$dataPosition);

            }
        }

    }

    /**
     * @param string $file
     * @param string $model
     * @param string $disposition
     * @param array $dataPositions
     * @throws AppParseException
     */
    private function checkSubstyleDances(string $file, string $model, string $disposition, array $dataPositions)
    {
        foreach($dataPositions as $substylePosition=>$array){

            list($substyle,$position) = explode('|',$substylePosition);
            $substyleList = array_keys($this->modelValues[$model]['substyle']);
            $danceList = array_keys($this->modelValues[$model]['dance']);
            if(!in_array($substyle, $substyleList)) {
                throw new AppParseException(AppExceptionCodes::FOUND_BUT_EXPECTED,
                    [$file,$substyle,$position,$substyleList]);
            }

            switch($disposition) {
                case 'multiple-events':
                    foreach($array as $subArray) {
                        if(is_scalar($subArray)) {
                            list($scalar,$position) = explode('|',$subArray) ;
                            throw new AppParseException(AppExceptionCodes::FOUND_BUT_EXPECTED,
                                [$file,$scalar,$position,["left square bracket"]]);
                        }
                        if(is_array($subArray)) {
                            foreach($subArray as $dancePosition) {
                                list($dance,$position) = explode('|',$dancePosition);
                                if(!in_array($dance,$danceList)) {
                                    throw new AppParseException(AppExceptionCodes::UNRECOGNIZED_VALUE,
                                        [$file,$dance,$position]);
                                }
                            }
                        }
                    }
                    break;
                case 'single-event':
                    foreach($array as $subArray) {
                        if(is_array($subArray)) {
                            $scalerPosition = $subArray[0];
                            list($scaler,$position) = explode('|',$scalerPosition);
                            throw new AppParseException(AppExceptionCodes::FOUND_BUT_EXPECTED,
                                [$file,$scaler,$position,['square left bracket']]);
                        }
                        if(is_scalar($subArray)) {
                            list($scalar,$position) = explode('|',$subArray);
                            if(!in_array($scalar,$danceList)) {
                                throw new AppParseException(AppExceptionCodes::UNRECOGNIZED_VALUE,
                                    [$file,$scalar,$position]);
                            }
                        }

                    }
            }
        }
    }

    /**
     * @param array $data
     * @throws ORMException
     * @throws OptimisticLockException
     */
    private function buildEvents(array $data) {

       foreach($data as $modelName=>$blockRecords) {
           foreach($blockRecords as $block) {
               $proficienciesStylesDances = $block['proficiency'];
               $ageList =$block['age'];
               $sexList =$block['sex'];
               $type = $block['type'];
               $statusList = $block['status'];
               $this->buildEventBlock(
                   $modelName,
                   $type,
                   $statusList,
                   $sexList,
                   $ageList,
                   $proficienciesStylesDances);
               $this->sendWorkingStatus();
           }
       }
    }

    /**
     * @param string $modelName
     * @param string $type
     * @param array $statusList
     * @param array $sexList
     * @param array $ageList
     * @param array $proficienciesStylesDances
     * @throws ORMException
     * @throws OptimisticLockException
     */
    private function buildEventBlock(
        string $modelName,
        string $type,
        array $statusList,
        array $sexList,
        array $ageList,
        array $proficienciesStylesDances)
    {
        $modelRepository = $this->entityManager->getRepository(Model::class);
        /** @var Model $model */
        $model = $modelRepository->findOneBy(['name'=>$modelName]);
        if(!isset($this->event[$modelName])) {
            $this->event[$modelName]=[];
        }
        if(!isset($this->event[$modelName][$type])) {
            $this->event[$modelName][$type]=[];
        }
        foreach($statusList as $status) {
            if(!isset($this->event[$modelName][$type][$status])){
                $this->event[$modelName][$type][$status]=[];
            }
            foreach($sexList as $sex) {
                if(!isset($this->event[$modelName][$type][$status][$sex])){
                    $this->event[$modelName][$type][$status][$sex]=[];
                }
                foreach($ageList as $age) {
                    if(!isset($this->event[$modelName][$type][$status][$sex][$age])){
                        $this->event[$modelName][$type][$status][$sex][$age]=[];
                    }
                    foreach($proficienciesStylesDances as $proficiency=>$records) {
                        if(!isset($this->event[$modelName][$type][$status][$sex][$age][$proficiency])){
                            $this->event[$modelName][$type][$status][$sex][$age][$proficiency]=[];
                        }
                        $this->buildIndividualEvents($model,$type,$status,$sex,$age,$proficiency,$records);
                    }
                }
            }
        }
    }

    /**
     * @param Model $model
     * @param string $type
     * @param string $status
     * @param string $sex
     * @param string $age
     * @param string $proficiency
     * @param array $records
     * @throws ORMException
     * @throws OptimisticLockException
     */
    private function buildIndividualEvents(
        Model $model,
        string $type,
        string $status,
        string $sex,
        string $age,
        string $proficiency,
        array $records)
    {
       $describe = ['type'=>$type,'status'=>$status,'sex'=>$sex,'age'=>$age,'proficiency'=>$proficiency];
       $modelName = $model->getName();
       foreach($records as $rec) {
           $describe1=$describe;
           $describe1['tag']=$rec['tag'];
           foreach($rec['style'] as $style=>$substyleDances) {
               if(!isset($this->event[$modelName][$type][$status][$sex][$age][$proficiency][$style])){
                   $this->event[$modelName][$type][$status][$sex][$age][$proficiency][$style]=[];
               }
               switch($substyleDances['disposition']) {
                   case 'single-event':
                       $describe2 = $describe1;
                       $describe2['dances']=$substyleDances['substyle'];
                       $describe2['style']=$style;
                       $event=$this->eventRepository->create($model,$describe2,[]);
                       array_push($this->event[$modelName][$type][$status]
                                         [$sex][$age][$proficiency][$style],$event);
                       break;
                   case 'multiple-events':
                        $describe2 = $describe1;
                        foreach($substyleDances['substyle'] as $substyle=>$danceCollections) {
                            foreach($danceCollections as $dances) {
                                $describe3 = $describe2;
                                $describe3['dances']=[$substyle=>$dances];
                                $describe3['style']=$style;
                                $event=$this->eventRepository->create($model,$describe3,[]);
                                array_push($this->event[$modelName][$type][$status]
                                        [$sex][$age][$proficiency][$style],$event);
                            }
                        }
               }
           }
       }
    }

    protected function sendWorkingStatus()
    {
        if(isset($this->dispatcher)) {
            $event = new ProcessEvent(new ProcessStatus(ProcessStatus::WORKING, 1));
            $this->dispatcher->dispatch('process.update',$event);
        }
    }
}