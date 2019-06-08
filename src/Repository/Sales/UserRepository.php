<?php
/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 6/8/19
 * Time: 4:44 PM
 */

namespace App\Repository\Sales;


use App\Entity\Sales\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

}