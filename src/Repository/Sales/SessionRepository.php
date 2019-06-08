<?php
/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 6/8/19
 * Time: 4:47 PM
 */

namespace App\Repository\Sales;


use App\Entity\Sales\Session;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class SessionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Session::class);
    }
}