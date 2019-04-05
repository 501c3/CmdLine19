<?php
/**
 * Copyright (c) 2019. Mark Garber.  All rights reserved.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 3/3/19
 * Time: 6:11 PM
 */

namespace App\Repository\Access;


use App\Entity\Access\Person;
use App\Entity\Access\User;
use App\Form\Model\Registration;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class PersonRepository extends ServiceEntityRepository
{
    /**
     * PersonRepository constructor.
     * @param ManagerRegistry $registry
     */
   public function __construct(ManagerRegistry $registry)
   {
       parent::__construct($registry, Person::class);
   }

    /**
     * @param Registration $registration
     * @param User $user
     * @throws \Exception
     */
   public function register(Registration $registration,User $user)
   {
        $person = new Person();
        $name = $registration->getName();
        $address = $registration->getAddress();
        $contact = $registration->getContact();
        $date = new \DateTime('now');
        $person->setEmail($contact->getEmail())
                ->setMobile($contact->getMobile())
                ->setHome($contact->getPhone())
                ->setTitle($name->getTitle())
                ->setFirst($name->getFirst())
                ->setMiddle($name->getMiddle())
                ->setLast($name->getLast())
                ->setSuffix($name->getSuffix())
                ->setStreet($address->getStreet())
                ->setAddress($address->getDepartment())
                ->setCountry($address->getCountry())
                ->setCity($address->getCity())
                ->setState($address->getState())
                ->setPostal($address->getPostal())
                ->setAgreeTerms($date)
                ->setCreatedAt($date)
                ->setUser($user);
        $em = $this->getEntityManager();
        $em->persist($person);
        $em->flush();
   }
}