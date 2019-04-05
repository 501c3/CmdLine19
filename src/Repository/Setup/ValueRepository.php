<?php
/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 11/29/18
 * Time: 6:52 PM
 */

namespace App\Repository\Setup;


use App\Entity\Setup\Domain;
use App\Entity\Setup\Value;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

class ValueRepository extends ServiceEntityRepository
{
    /** @var EntityManagerInterface */
    private $em;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Value::class);
        $this->em = $this->getEntityManager();
    }

    /**
     * @param Domain $domain
     * @param $name
     * @param $abbr
     * @return Value
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function create(Domain $domain, $name, $abbr) : Value
    {
        $value = new Value();
        $value->setDomain($domain)
            ->setName($name)
            ->setAbbr($abbr);
        $this->em->persist($value);
        $this->em->flush();
        return $value;
    }

    /**
     * @return \Doctrine\ORM\Query
     */
    private function fetchQuery()
    {
        $qb=$this->createQueryBuilder('value');
        $qb->select('value','domain')
            ->innerJoin('value.domain','domain');
        $query=$qb->getQuery();
        return $query;
    }

    /**
     * @param null $id
     * @return array|null|object
     */
    public function read($id=null)
    {
        if($id) {
            return $this->find($id);
        }
        $query = $this->fetchQuery();
        $result = $query->getResult();
        $arr = [];
        /** @var Value $item */
        foreach($result as $item) {
            $domName = $item->getDomain()->getName();
            if(!isset($arr[$domName])) {
                $arr[$domName]=[];
            }
            $arr[$domName][$item->getName()]=$item;
        }
        return $arr;
    }

    /**
     * @param Value $new
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function update(Value $new)
    {
        $old = $this->find($new->getId());
        $old->setDomain($new->getDomain())
            ->setName($new->getDomain())
            ->setAbbr($new->getAbbr());
        $this->em->flush($old);
    }

    /**
     * @param int $id
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function delete(int $id)
    {
        $value = $this->find($id);
        $this->em->remove($value);
        $this->em->flush();
    }



    public function fetchQuickSearch()
    {
        $qb = $this->createQueryBuilder('value');
        $qb->select('value','domain')
            ->innerJoin('value.domain','domain');
        $query = $qb->getQuery();
        $results = $query->getResult();
        $arr=[];
        /** @var Value $result */
        foreach($results as $result) {
            $domain = $result->getDomain();
            $domName = $domain->getName();
            $valName = $result->getName();
            if(!isset($arr[$domName])) {
                $arr[$domName] = [];
            }
            $arr[$domName][$valName] = $result;
        }
        return $arr;
    }
}