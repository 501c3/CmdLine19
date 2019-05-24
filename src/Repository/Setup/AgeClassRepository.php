<?php
/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 5/14/19
 * Time: 6:20 PM
 */

namespace App\Repository\Setup;


use App\Entity\Setup\AgeClass;
use App\Entity\Setup\Tss;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class AgeClassRepository extends ServiceEntityRepository
{
    private $queryWithTss;
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AgeClass::class);
        $qb = $this->createQueryBuilder('class');
        $qb->select('class','tss')
            ->innerJoin('class.tss','tss')
            ->where('class.name=:name')
            ->andWhere('tss=:tss');
        $this->queryWithTss=$qb->getQuery();

    }

    /**
     * @param Tss $tss
     * @param string $teamAge
     * @return AgeClass
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function fetch(Tss $tss, string $teamAge): AgeClass
    {
        $this->queryWithTss->setParameters([':name'=>$teamAge, ':tss'=>$tss]);
        $class = $this->queryWithTss->getOneOrNullResult();
        if($class) {
            return $class;
        }
        /** @var AgeClass $class */
        $class = $this->findOneBy(['name'=>$teamAge]);
        if($class) {
            $class->addTss($tss);
            $this->_em->flush();
            return $class;
        }
        $class = new AgeClass();
        $class->setName($teamAge)
              ->addTss($tss);
        $this->_em->persist($class);
        $this->_em->flush();
        return $class;
    }
}