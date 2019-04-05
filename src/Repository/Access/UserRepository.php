<?php
/**
 * Copyright (c) 2019. Mark Garber.  All rights reserved.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 2/9/19
 * Time: 6:03 PM
 */

namespace App\Repository\Access;


use App\DataTransformer\RegistrationToUserTransformer;
use App\Entity\Access\User;
use App\Form\Model\Address;
use App\Form\Model\Contact;
use App\Form\Model\Name;
use App\Form\Model\Registration;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserRepository extends ServiceEntityRepository
{

    /**
     * @var UserPasswordEncoderInterface
     */
    private $userPasswordEncoder;

    public function __construct(ManagerRegistry $registry,
                                UserPasswordEncoderInterface $userPasswordEncoder)
    {
        parent::__construct($registry,User::class);
        $this->userPasswordEncoder = $userPasswordEncoder;
    }

    /**
     * @param Registration $registration
     * @return User
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function register(Registration $registration) : User
    {
        $contact = $registration->getContact();
        $user = new User();
        $user->setUsername($contact->getUsername());
        $user->setCreatedAt(new \DateTime('now'))
            ->setEnabled(true)
            ->setRoles(['ROLE_USER']);
        $user->setPassword($this->userPasswordEncoder->encodePassword($user,$contact->getPassword()))
            ->setEnabled(false);
        $em=$this->getEntityManager();
        $em->persist($user);
        $em->flush();
        return $user;
    }
}