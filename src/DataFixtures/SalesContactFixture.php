<?php

namespace App\DataFixtures;

use App\Entity\Sales\Channel;
use App\Entity\Sales\Contact;
use App\Entity\Sales\Pricing;
use App\Entity\Sales\Tag;
use App\Entity\Sales\Workarea;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\Persistence\ObjectManager;

class SalesContactFixture extends AppFixtures implements FixtureGroupInterface
{
    const TAGS = ['competition','contact','participant','entry','xtra','pricing','payment','finish'];

    /**
     * @param ObjectManager $manager
     */
    public function loadData(ObjectManager $manager)
    {
        $this->createMany(1, 'channel', function($i){
            /** @var Channel $channel */
            $heading = ['name'=>'Georgia DanceSport Competition & ISTD Medal Exams',
                        'city'=> 'Sandy Springs',
                        'state'=>'Georgia',
                        'venue'=>'Ballroom Impact'];
            $logo = file_get_contents(__DIR__.'/../../assets/dancers-icon.png');
            $channel = new Channel();
            $channel->setName('georgia-dancesport')
                    ->setHeading($heading)
                    ->setLogo($logo)
                    ->setLive(false)
                    ->setCreatedAt(new \DateTime('now'));
            return $channel;
        });


        $this->createOne(0, 'pricing', function() {
            /** @var Channel $channel */
            $channel = $this->getReference('channel_0');
            /** @var Pricing $pricing */
            $pricing = new Pricing();
            $inventory =
                ['participant' => [
                    'comp-dance-adult' => 18,
                    'comp-dance-child' => 12,
                    'exam-dance' => 30],
                    'extra' => ['spectator-adult' => 10,
                                'spectator-child' => 7,
                                'program' => 7]];
            $pricing->setStartAt(new \DateTime('2019-09-01'))
                    ->setInventory($inventory)
                    ->setChannel($channel);
            return $pricing;
        });

        $this->createOne(1, 'pricing', function() {
            /** @var Channel $channel */
            $channel = $this->getReference('channel_0');
            /** @var Pricing $pricing */
            $pricing = new Pricing();
            $inventory =
                ['participant' => [
                    'comp-dance-adult' => 10,
                    'comp-dance-child' => 7,
                    'exam-dance' => 21],
                    'extra' => ['spectator-adult' => 10,
                        'spectator-child' => 7,
                        'program' => 7]];

            $pricing->setStartAt(new \DateTime('2019-06-01'))
                ->setInventory($inventory)
                ->setChannel($channel);
            return $pricing;
        });


        $this->createMany(50, 'contact', function($i){
            $first = $this->faker->firstName;
            $last = $this->faker->lastName;

            $info = ['studio'=>$this->faker->company,
                     'street'=>$this->faker->streetAddress,
                     'city'=>$this->faker->city,
                     'state'=>$this->faker->randomElement(['GA','FL','AL','TN','SC','NC']),
                     'country'=>'United States'];
            $contact = new Contact();
            $contact->setName($last.', '.$first)
                    ->setEmail($this->faker->email)
                    ->setInfo($info)
                    ->setPin($this->faker->numberBetween(1000,9999))
                    ->setCreatedAt(new \DateTime('now'));
            return $contact;
        });
        $manager->flush();
        $this->createMany(8,'tag',function($i){
            $tag = new Tag();
            $tag->setName(self::TAGS[$i]);
            return $tag;
        });


        $this->createMany(50, 'workarea', function($i){
            /** @var Channel $channel */
            $channel = $this->getReference('channel_0');
            /** @var Tag $tag */
            $tag = $this->getReference('tag_1'); //Contact tag
            $workarea = new Workarea();
            $workarea->setChannel($channel)
                    ->setChannel($channel)
                    ->setTag($tag)
                    ->setCreatedAt(new \DateTime('now'));
            return $workarea;
        });

        $manager->flush();
    }

    /**
     * This method must return an array of groups
     * on which the implementing class belongs to
     *
     * @return string[]
     */
    public static function getGroups(): array
    {
        return ['sales'];
    }
}
