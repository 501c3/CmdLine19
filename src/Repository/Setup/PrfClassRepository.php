<?php
/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 5/14/19
 * Time: 6:19 PM
 */

namespace App\Repository\Setup;

use App\Entity\Setup\PrfClass;
use App\Entity\Setup\Tss;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method getOnOrNullResult()
 */
class PrfClassRepository extends ServiceEntityRepository
{
    private $queryWithTss;
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PrfClass::class);
        $qb = $this->createQueryBuilder('class');
        $qb->select('class','tss')
            ->innerJoin('class.tss','tss')
            ->where('class.name=:name')
            ->andWhere('tss=:tss');
        $query = $qb->getQuery();
        $this->queryWithTss=$query;
    }



    /**
     * @param Tss $tss
     * @param string $proficiencyClass
     * @return PrfClass|object|null
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function fetch(Tss $tss, string $proficiencyClass)
    {
        $this->queryWithTss->setParameters([':name'=>$proficiencyClass, ':tss'=>$tss]);
        $class = $this->queryWithTss->getOneOrNullResult();
        if($class) {
            return $class;
        }
        $class = $this->findOneBy(['name'=>$proficiencyClass]);
        if($class) {
            $class->addTss($tss);
            $this->_em->flush();
            return $class;
        }
        $class = new PrfClass();
        $class->setName($proficiencyClass)
                ->addTss($tss);
        $this->_em->persist($class);
        $this->_em->flush();
        return $class;
    }
}