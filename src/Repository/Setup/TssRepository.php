<?php
/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 5/15/19
 * Time: 11:49 AM
 */

namespace App\Repository\Setup;


use App\Entity\Setup\Tss;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class TssRepository extends ServiceEntityRepository
{
    private $query;
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tss::class);
        $qb = $this->createQueryBuilder('tss');
        $qb->where("JSON_EXTRACT(tss.describe,'$.type')=:type")
            ->andWhere("JSON_EXTRACT(tss.describe,'$.status')=:status")
            ->andWhere("JSON_EXTRACT(tss.describe,'$.sex')=:sex");
        $this->query=$qb->getQuery();

    }

    /**
     * @param array $describe
     * @return Tss
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function fetch(array $describe): Tss
    {
        $type = $describe['type'];
        $status = $describe['status'];
        $sex = $describe['sex'];
        $this->query->setParameters([':type'=>$type,':status'=>$status,':sex'=>$sex]);
        $tss = $this->query->getOneOrNullResult();
        if($tss) {
            return $tss;
        }
        $tss = new Tss();
        $tss->setDescribe($describe);
        $this->_em->persist($tss);
        $this->_em->flush();
        return $tss;

    }

    public function fetchQuickSearch()
    {
       if(isset($this->tss)) {
            return $this->tss;
       }
       $arr = [];
       $results = $this->findAll();
       /** @var Tss $result */
        foreach($results as $result) {
            $describe=$result->getDescribe();
            $type = $describe['type'];
            $status=$describe['status'];
            $sex = $describe['sex'];
            if(!isset($arr[$type])) {
                $arr[$type]=[];
            }
            if(!isset($arr[$type][$status])) {
                $arr[$type][$status]=[];
            }
            if(!isset($arr[$type][$status][$sex])) {
                $arr[$type][$status][$sex]=$result;
            }
       }
        $this->tss = $arr;
        return $arr;
    }

    public function teamClassBuild()
    {
        $qb = $this->createQueryBuilder('tss');
        $qb->select('tss','tssPrfClass','tssAgeClass')
            ->innerJoin('tss.prfClass','tssPrfClass')
            ->innerJoin('tss.ageClass','tssAgeClass')
            ->innerJoin('prfClass.ageClass','prfAge');
        $result = $qb->getQuery()->getResult();
        return $result;

    }
}