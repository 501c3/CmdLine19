<?php
/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 11/29/18
 * Time: 6:08 PM
 */

namespace App\Repository\Setup;


use App\Entity\Setup\Model;
use App\Entity\Setup\Value;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

class ModelRepository extends ServiceEntityRepository
{
    /** @var EntityManagerInterface */
    private $em;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Model::class);
        $this->em = $this->getEntityManager();
    }

    /**
     * @param string $name
     * @return Model
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function create(string $name) : Model
    {
        $model = new Model();
        $model->setName($name)
                ->setCreated(new \DateTime('now'));
        $this->em->persist($model);
        $this->em->flush();
        return $model;
    }

    /**
     * @param null $id
     * @return array|Model
     */
    public function read($id=null)
    {
        $result = $id?$this->find($id):$this->findAll();
        if(is_array($result)){
            $arr = [];
            /** @var Model $item */
            foreach($result as $item) {
               $arr[$item->getName()]=$item;
            }
            return $arr;
        }
        return $result;
    }

    /**
     * @param Model $new
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function update(Model $new)
    {
        $old=$this->find($new->getId());
        $old->setName($new->getName())
            ->setUpdated($new->getUpdated());
        $this->em->flush($old);
    }

    /**
     * @param int $id
     * @throws ORMException
     */
    public function delete(int $id)
    {
        $model=$this->find($id);
        $this->em->remove($model);
        $this->em->flush();
    }

    public function fetchQuickSearch():array
    {
        $qb = $this->createQueryBuilder('model');
        $qb->select('model','value','domain')
            ->innerJoin('model.value','value')
            ->innerJoin('value.domain', 'domain');
         $query = $qb->getQuery();
         $results = $query->getResult();
         $arr = [];
         /** @var Model $item */
        foreach($results as $item) {
            $modelName = $item->getName();
            if(!isset($arr[$modelName])) {
                $arr[$modelName]=[];
            }
            /** @var ArrayCollection $collection */
            $collection = $item->getValue();
            /** @var Value $current */
            $current = $collection->first();
            while($current) {
                $domName = $current->getDomain()->getName();
                $valName = $current->getName();
                if(!isset($arr[$modelName][$domName])) {
                    $arr[$modelName][$domName]=[];
                }
                if(!isset($arr[$modelName][$domName][$valName])) {
                    $arr[$modelName][$domName][$valName]=$current;
                }
                $current = $collection->next();
            }
         }
         return $arr;
    }
}