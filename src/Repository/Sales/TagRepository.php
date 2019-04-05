<?php
/**
 * Copyright (c) 2019. Mark Garber.  All rights reserved.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 3/8/19
 * Time: 11:43 AM
 */

namespace App\Repository\Sales;


use App\App\Entity\Sales\Tag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class TagRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $registry)
  {
      parent::__construct($registry, Tag::class);
  }
}