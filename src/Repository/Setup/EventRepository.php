<?php
/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 11/29/18
 * Time: 6:20 PM
 */

namespace App\Repository\Setup;


use App\Entity\Setup\Event;
use App\Entity\Setup\Model;
use App\Entity\Setup\TeamClass;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ManagerRegistry;

class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    /**
     * @param Model $model
     * @param array $describe
     * @param array $values
     * @return Event
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function create(Model $model, array $describe, array $values): Event
    {

        $em = $this->getEntityManager();
        $event = new Event();
        $event->setModel($model)
                ->setDescribe($describe);
        $collection = $event->getValue();
        foreach($values as $value) {
            $collection->add($value);
        }
        $em->persist($event);
        $em->flush();
        return $event;
    }

    private function getQueryLocal(array $values, array $prior)
    {
        $qb = $this->createQueryBuilder('event');
        $qb->select('model','team','event','value')
            ->innerJoin('event.model','model')
            ->innerJoin('event.teamClass','teamClass')
            ->innerJoin('event.value','value')
            ->where('model=:model')
            ->andWhere('teamClass=:teamClass');
        if(count($values)) {
            $qb->andWhere('value=:value');
        }
        if(count($prior)) {
            $qb->andWhere('event IN :prior');
        }
        $query = $qb->getQuery();
        return $query;
    }

    public function fetchTeamEvents(Model $model, TeamClass $teamClass,array $values=[]) {

        $query = $this->getQueryLocal($values,[]);
        $value  = count($values)?array_shift($values):null;
        $query->setParameters([':model'=>$model,':teamClass'=>$teamClass]);
        if($value) {
            $query->setParameter(':value',$value);
        }
        $prior = $query->getResult();
        return $this->fetchRecursive($prior,$model,$teamClass,$values);
    }

    public function fetchRecursive($prior,Model $model,TeamClass $teamClass,array $values)
    {
        if(count($values)==0) {
            return $prior;
        }
        $query = $this->getQueryLocal($values,$prior);
        $value = count($values)?array_shift($values):null;
        $query->setParameters([':model'=>$model,':teamClass'=>$teamClass, ':value'=>$value]);
        $prior = $query->getResult();
        return $this->fetchRecursive($prior, $model, $teamClass, $values);
    }

    private function updateEligibilityArray(Event $event, array &$arr)
    {
        $modelName = $event->getModel()->getName();
        if(!isset($arr[$modelName])) {
            $arr[$modelName] = [];
        }
        $describe = $event->getDescribe();
        $type = $describe['type'];
        $status = $describe['status'];
        $sex = $describe['sex'];
        $age = $describe['age'];
        $proficiency=$describe['proficiency'];

        if(!isset($arr[$modelName][$type])) {
            $arr[$modelName][$type] = [];
        }
        if(!isset($arr[$modelName][$type][$status])) {
            $arr[$modelName][$type][$status]=[];
        }
        if(!isset($arr[$modelName][$type][$status][$sex])) {
            $arr[$modelName][$type][$status][$sex]=[];
        }
        if(!isset($arr[$modelName][$type][$status][$sex][$age])) {
            $arr[$modelName][$type][$status][$sex][$age]=[];
        }
        if(!isset($arr[$modelName][$type][$status][$sex][$age][$proficiency])) {
            $arr[$modelName][$type][$status][$sex][$age][$proficiency] = new ArrayCollection();
        }
        /** @var ArrayCollection $collection */
        $collection = & $arr[$modelName][$type][$status][$sex][$age][$proficiency];
        $collection->add($event);
    }

    /**
     * @return array
     */
    public function fetchPreEligibility() : array
    {
        $arr = [];
        $qb = $this->createQueryBuilder('event');
        $qb->select('event','model')
            ->innerJoin('event.model','model');
        $query = $qb->getQuery();
        $results = $query->getResult();
        /** @var Event $result */
        foreach($results as $result) {
            $this->updateEligibilityArray($result,$arr);
        }
        return $arr;
    }


    public function fetchEligibility() : array
    {
        $arr = [];
        $qb = $this->createQueryBuilder('event');
        $qb->select('event','model','teamClass')
            ->innerJoin('event.model','model')
            ->innerJoin('event.teamClass','teamClass');
        $query = $qb->getQuery();
        $results = $query->getResult();
        /** @var Event $result */
        foreach($results as $result) {
            $this->updateEligibilityArray($result,$arr);
        }
        return $arr;
    }

    public function fetchQuickSearch(Model $model)
    {
        $arr = [];
             $qb = $this->createQueryBuilder('event');
        $qb->select('event','model')
            ->innerJoin('event.model','model')
            ->where('model=:model');
        $query = $qb->getQuery();
        $query->setParameter(':model',$model);
        $results = $query->getResult();
        /** @var Event $result */
        foreach($results as $result) {
            $describe = $result->getDescribe();
            $type=$describe['type'];
            $status=$describe['status'];
            $sex=$describe['sex'];
            $age=$describe['age'];
            $proficiency = $describe['proficiency'];
            $style= $describe['style'];
            if(!isset($arr[$type])){
                $arr[$type]=[];
            }
            if(!isset($arr[$type][$status])){
                $arr[$type][$status]=[];
            }
            if(!isset($arr[$type][$status][$sex])) {
                $arr[$type][$status][$sex] = [];
            }
            if(!isset($arr[$type][$status][$sex][$age])){
                $arr[$type][$status][$sex][$age]=[];
            }
            if(!isset($arr[$type][$status][$sex][$age][$proficiency])){
                $arr[$type][$status][$sex][$age][$proficiency]=[];
            }
            if(!isset($arr[$type][$status][$sex][$age][$proficiency][$style])){
                $arr[$type][$status][$sex][$age][$proficiency][$style]=[];
            }
            array_push($arr[$type][$status][$sex][$age][$proficiency][$style],$result);
        }
        return $arr;
    }

}