<?php
/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 11/29/18
 * Time: 6:53 PM
 */

namespace App\Repository\Setup;


use App\Entity\Setup\Domain;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

class DomainRepository extends ServiceEntityRepository
{
    /** @var EntityManagerInterface */
    private $em;
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Domain::class);
        $this->em = $this->getEntityManager();
    }

    /**
     * @param string $name
     * @return Domain
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function create(string $name) : Domain
    {
        $domain = new Domain();
        $domain->setName($name);
        $this->em->persist($domain);
        $this->em->flush();
        return $domain;
    }

    /**
     * @param null $id
     * @return array|null|object
     */
    public function read($id=null)
    {
        $result = $id?$this->find($id):$this->findAll();
        if(is_array($result)) {
            $arr = [];
            foreach($result as $item) {
                $arr[$item->getName()]=$item;
            }
            return $arr;
        }
        return $result;
    }

    /**
     * @param Domain $new
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function update(Domain $new) {
        $old = $this->find($new->getId());
        $old->setName($new->getName());
        $this->em->flush($old);
    }

    /**
     * @param int $id
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function delete(int $id)
    {
        $domain=$this->find($id);
        $this->em->remove($domain);
        $this->em->flush();
    }

}