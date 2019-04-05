<?php /** @noinspection SpellCheckingInspection */
/**
 * Copyright (c) 2018. Mark Garber.  All rights reserved.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 11/30/18
 * Time: 3:12 PM
 */

namespace App\Common;


use App\Entity\Setup\Domain;
use App\Entity\Setup\Model;
use App\Entity\Setup\Value;
use App\Repository\Setup\ValueRepository;
use App\Repository\Setup\DomainRepository;
use App\Repository\Setup\ModelRepository;
use App\Signal\ProcessEvent;
use App\Signal\ProcessStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

class YamlDbSetupBase
{
    const VALUE_KEYS = ['abbr', 'note', 'domain','label'],
          EVENT_DOMAINS = ['type','status','sex','genre','style','substyle','proficiency','age','tag','dance'];

    protected $model = [];

    protected $domain = [];

    protected $value = [];

    protected $modelValue = [];

    /** @var EntityManagerInterface */
    protected $entityManager;

    /** @var EventDispatcher */
    protected $dispatcher;


    public function __construct(EntityManagerInterface $entityManager, EventDispatcher $dispatcher=null)
   /**
     * @param string $file
     * @return mixed
     * @throws AppParseException
     */
    {
        $this->entityManager=$entityManager;
        $this->dispatcher = $dispatcher;
    }


    /**
     * @param string $file
     * @return mixed
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function parseModels(string $file)
    {
      /** @var ModelRepository $repository */
      $repository=$this->entityManager->getRepository(Model::class);
      $names =  yaml_parse_file($file);
      foreach($names as $name){
          $model=$repository->create($name);
          $this->model[$name] = $model;
      }
      $this->sendWorkingStatus();
      return $this->model;
    }

    /**
     * @param string $file
     * @return mixed
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function parseDomains(string $file)
    {
        /** @var DomainRepository $repository */
        $repository=$this->entityManager->getRepository(Domain::class);
        $names =  yaml_parse_file($file);
        foreach($names as $name){
            $domain=$repository->create($name);
            $this->domain[$name] = $domain;
            $this->value[$name] = [];

        }
        $this->sendWorkingStatus();
        return $this->domain;
    }


    /**
     * @param string $file
     * @return array
     * @throws AppParseException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public function parseValues(string $file)
    {
        $domainValuePositionArray = YamlPosition::yamlAddPosition($file);
        $domainPositionArray = array_keys($domainValuePositionArray);
        $validKeys = array_keys($this->value);
        foreach ($domainPositionArray as $domainPosition) {
            list($domain, $position) = explode('|', $domainPosition);
            if (!in_array($domain, $validKeys)) {
                throw new AppParseException(AppExceptionCodes::FOUND_BUT_EXPECTED,
                    [$file, $domain, $position, $validKeys]);
            }
        }
        /** @var ValueRepository $repository */
        $repository=$this->entityManager->getRepository(Value::class);
        foreach ($domainValuePositionArray as $domainPosition => $valuesPositions) {
            $array = explode('|', $domainPosition);
            list($domainKey) = $array;
            foreach ($valuesPositions as $valuePosition => $descriptorPosition) {
                list($valueKey,$valuePosition) = explode('|', $valuePosition);
                $descriptor = $this->valueDescriptor($descriptorPosition,$file);
                $domain = $this->domain[$domainKey];
                $abbr=$this->fetchAbbr($descriptor,$valueKey,$valuePosition);
                $value = $repository->create($domain,$valueKey,$abbr);
                $this->value[$domainKey][$valueKey] = $value;
            }
        }
        $this->sendWorkingStatus();
        return $this->value;
    }

    /**
     * @param $descriptorIn
     * @param $file
     * @return array
     * @throws AppParseException
     */
    private function valueDescriptor($descriptorIn,$file) {
        $descriptorOut = [];
        foreach($descriptorIn as $keyPosition=>$valuePosition) {
            list($key,$keyPos) = explode('|', $keyPosition);
            list($value) = explode('|', $valuePosition);
            if(!in_array($key, self::VALUE_KEYS)) {

                throw new AppParseException(AppExceptionCodes::FOUND_BUT_EXPECTED,
                    [$file,$key, $keyPos, self::VALUE_KEYS]);
            }
            $descriptorOut[$key] = $value;
        }
        return $descriptorOut;
    }

    /**
     * @param array $descriptor
     * @param string $valueKey
     * @param string $valuePosition
     * @return string
     * @throws AppParseException
     */
    private function fetchAbbr(array $descriptor, string $valueKey, string $valuePosition) : string
    {

        if(isset($descriptor['domain'])) {
            /** @var Value $object */
            $object=$this->value[$descriptor['domain']][$valueKey];
            return $object->getAbbr();
        } elseif (isset($descriptor['abbr'])) {
            return $descriptor['abbr'];
        } else {
            throw new AppParseException(AppExceptionCodes::UNRECOGNIZED_VALUE, [$valueKey, $valuePosition]);
        }
    }

    /**
     * @param string $file
     * @return array
     * @throws AppParseException
     * @throws \Exception
     */
    public function parseModelValues(string $file) : array
    {
        $modelsPositions = YamlPosition::yamlAddPosition($file);
        foreach ($modelsPositions as $modelPos => $records) {
            list($model, $position) = explode('|', $modelPos);
            if (!in_array($model, array_keys($this->model))) {
                throw new AppParseException(AppExceptionCodes::UNRECOGNIZED_VALUE, [$file, $model, $position]);
            }
            foreach ($records as $keyPosition=>$valueList) {
                /** @var string $position */
                list($key, $position) = explode('|', $keyPosition);
                if (!in_array($key, self::EVENT_DOMAINS)) {
                    throw new AppParseException(AppExceptionCodes::FOUND_BUT_EXPECTED,
                        [$file, $key, $position, self::EVENT_DOMAINS]);
                }

            }
            $keysPositions = array_keys($records);
            $keysFound = YamlPosition::isolate($keysPositions, YamlPosition::STRING);
            $keysPositions = YamlPosition::isolate($keysPositions, YamlPosition::POSITION);
            $difference = array_diff(self::EVENT_DOMAINS, $keysFound);
            if (count($difference)) {
                throw new AppParseException(AppExceptionCodes::MISSING_KEYS,
                    [$file, $difference, $keysPositions]);
            }
            $cache = [];
            foreach ($records as $keyPosition => $dataPosition) {
                list($key) = explode('|', $keyPosition);
                $cache[$key] = $this->modelValuesCheck($file, $key, $dataPosition);
            }
        $this->modelValuesBuild($cache, $model);
        }
        $this->sendWorkingStatus();
        return $this->modelValue;
    }


    /**
     * @param string $file
     * @param string $key
     * @param $valuesPositions
     * @return array|string
     * @throws AppParseException
     * @throws \Exception
     */
    protected function modelValuesCheck(string $file, string $key, array $valuesPositions)
    {
        foreach($valuesPositions as $valuePos) {
            list($value,$position)=explode('|',$valuePos);
            /** @var YamlDbSetupBase $this */
            if(!isset($this->value[$key][$value])) {
                throw new AppParseException(AppExceptionCodes::UNRECOGNIZED_VALUE,
                    [$file,$value,$position]);
            }
        }
        return YamlPosition::isolate($valuesPositions);
    }

    /**
     * @param $values
     * @param string $model
     */
    protected function modelValuesBuild($values,string $model)
    {
        $modelObj = $this->model[$model];
        if(!isset($this->modelValue[$model])) {
            $this->modelValue[$model]=[];
        }
        foreach($values as $domain=>$valueList) {
            if (!isset($this->modelValue[$model][$domain])) {
                $this->modelValue[$model][$domain] = [];
            }
            foreach($valueList as $value){
                if(!isset($this->modelValue[$model][$domain][$value])) {
                    $valObj = $this->value[$domain][$value];
                    /** @var Model $modelObj */
                    $modelObj->getValue()->add($valObj);
                    $this->modelValue[$model][$domain][$value]=$valObj;
                }
            }
        }
        $this->entityManager->flush();
    }


    protected function sendWorkingStatus()
    {
        if(isset($this->dispatcher)) {
            $event = new ProcessEvent(new ProcessStatus(ProcessStatus::WORKING, 1));
            $this->dispatcher->dispatch('process.update',$event);
        }
    }
}