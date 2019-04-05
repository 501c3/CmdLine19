<?php
/**
 * Copyright (c) 2019. Mark Garber.  All rights reserved.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 1/9/19
 * Time: 12:05 AM
 */

namespace App\Common;


use App\Entity\Model\Event;
use App\Entity\Model\Subevent;
use App\Entity\Model\Value;
use App\Repository\Model\EventRepository;
use App\Repository\Model\SubeventRepository;
use App\Repository\Model\ValueRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

class YamlDbModelSubevent
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var EventDispatcher
     */
    private $dispatcher;

    /** @var array */
    private $event;

    /** @var array */
    private $value;

    /** @var string */
    private $file;

    /** @var SubeventRepository */
    private $subeventRepository;

    public function __construct(EntityManagerInterface $entityManager, EventDispatcher $dispatcher = null)
    {
        $this->entityManager = $entityManager;
        $this->dispatcher = $dispatcher;
    }

    public function initialize()
    {
        $em = $this->entityManager;
        /** @var EventRepository $repositoryEvent */
        $repositoryEvent = $em->getRepository(Event::class);
        $this->event=$repositoryEvent->fetchQuickSearch();
        /** @var  ValueRepository $repositoryValue */
        $repositoryValue = $em->getRepository(Value::class);
        $this->value = $repositoryValue->fetchQuickSearch();
        $this->subeventRepository=$em->getRepository(Subevent::class);
    }

    public function parseEvents(string $file)
    {
        $this->file = $file;
        $this->initialize();
        $data = YamlPosition::yamlAddPosition($file);
        foreach($data as $block) {
            foreach($block as $modelPosition=>$stylePositionList) {
                list($model,$positionModel) = explode('|',$modelPosition);
                if(!isset($this->value[$model])) {
                    throw new AppParseException(AppExceptionCodes::UNRECOGNIZED_VALUE,
                            [$file,$model,$positionModel]);
                }
                foreach($stylePositionList as $styleBlock){
                    foreach($styleBlock as $stylePosition=>$substylePositionList) {
                        list($style,$positionStyle) = explode('|',$stylePosition);
                        if(!isset($this->value[$model]['style'][$style])) {
                            throw new AppParseException(AppExceptionCodes::UNRECOGNIZED_VALUE,
                                [$file,$style,$positionStyle]);
                        }
                        foreach($substylePositionList as $substylePosition=>$proficiencyPositionList) {
                            list($substyle,$positionSubstyle) = explode('|',$substylePosition);
                            if(!isset($this->value[$model]['substyle'][$substyle])) {
                                throw new AppParseException(AppExceptionCodes::UNRECOGNIZED_VALUE,
                                    [$file,$substyle,$positionSubstyle]);
                            }
                            if(!is_array($proficiencyPositionList)) {
                                throw new AppParseException(AppExceptionCodes::EXPECTED_STRUCTURE,
                                    [$file,$substyle,$positionSubstyle]);
                            }
                            foreach($proficiencyPositionList as $proficiencyPosition=>$agePositionList) {
                                list($proficiency,$positionProficiency) = explode('|',$proficiencyPosition);
                                if(!isset($this->value[$model]['proficiency'][$proficiency])) {
                                    throw new AppParseException(AppExceptionCodes::UNRECOGNIZED_VALUE,
                                        [$file,$proficiency,$positionProficiency]);
                                }
                                foreach($agePositionList as $agePosition) {
                                    list($age,$positionAge) = explode('|',$agePosition);
                                    if(!isset($this->value[$model]['age'][$age])) {
                                        throw new AppParseException(AppExceptionCodes::UNRECOGNIZED_VALUE,
                                            [$file,$age,$positionAge]);
                                    }
                                }
                            }
                        }

                    }
                }

            }
        }
        $data = yaml_parse_file($file);
        $this->buildSubevents($data);
    }

    private function buildSubevents($data)
    {
        foreach($data as $block) {
            foreach($block as $model=>$styleBlock) {
                foreach($styleBlock as $styleList) {
                    foreach($styleList as $style=>$substyleList) {
                        foreach($substyleList as $substyle=>$proficiencyList) {
                            foreach($proficiencyList as $proficiency=>$ageList) {
                                /**
                                 * @var string  $age
                                 * @var ArrayCollection $collection
                                 */
                                foreach($ageList as $age) {
                                    if(!isset($this->event[$model][$style][$substyle][$proficiency][$age])) {
                                        $index = [$model,$style,$substyle,$proficiency,$age];
                                        throw new AppBuildException(AppExceptionCodes::BAD_INDEX,
                                            [__FILE__,__LINE__-3,'$this->event',$index]);
                                    }
                                    $collection = $this->event[$model][$style][$substyle][$proficiency][$age];
                                    $event = $collection->first();
                                    while($event) {
                                        $this->subeventRepository->create($substyle,$event);
                                        $event = $collection->next();
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}