<?php

namespace App\DataFixtures;

use App\Entity\Access\Person;
use App\Entity\Access\User;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AccessUserFixture extends AppFixtures
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }


    protected function loadData(ObjectManager $manager)
    {
        $this->createMany(100,'users', function($i) {
           $user = new User();
           $user->setUsername($this->faker->Username);
           $user->setEnabled(true);
           $user->setRoles(['ROLE_USER']);
           $user->setPassword($this->passwordEncoder->encodePassword(
               $user,
               'engage'
           ));
           $reference = sprintf('%s_%s','users',str_pad($i, 3, '0',STR_PAD_LEFT));
           $this->addReference(
               $reference,
               $user);
           return $user;
        });

        $this->createMany(100, 'persons',function($i){
           $person = new Person();
           /** @var User $user */
           $user = $this->getReference(sprintf('%s_%d','users',$i));
           $person->setTitle('Mr.')
                ->setFirst($this->faker->firstName)
                ->setMiddle($this->faker->firstName)
                ->setLast($this->faker->lastName)
                ->setStreet($this->faker->streetAddress)
                ->setAddress($this->faker->company)
                ->setCountry('US')
                ->setCity($this->faker->city)
                ->setState('GA')
                ->setPostal($this->faker->postcode)
                ->setHome('(999) 999-9999')
                ->setMobile('(999) 999-9999')
                ->setEmail($this->faker->email)
                ->setUser($user);
           return $person;
        });

        $this->createMany(3,'admin_users', function($i) use ($manager) {
            $user = new User();
            $user->setUsername($this->faker->Username);
            $user->setEnabled(true);
            $user->setRoles(['ROLE_ADMIN']);
            $user->setPassword($this->passwordEncoder->encodePassword(
                $user,
                'engage'
            ));
            return $user;
        });
        $manager->flush();
    }
}
