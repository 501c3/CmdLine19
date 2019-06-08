<?php
/**
 * Copyright (c) 2018. Mark Garber.  All rights reserved.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 12/21/18
 * Time: 9:15 PM
 */

namespace App\Tests\Common;


use App\Common\AppBuildException;
use App\Common\AppExceptionCodes;
use App\Common\YamlDbSetupEventTeam;
use App\Entity\Setup\Event;
use App\Kernel;
use App\Repository\Setup\EventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Dotenv\Dotenv;

class Code1900SetupEventTeamTest extends KernelTestCase
{

  const QUALIFICATIONS_NOT_FOUND = <<<HEREDOC
Qualifications of Team(type:%s, status: %s, sex: %s, age:%s, proficiency:%s) was not found for 
Event(model:%s, type:%s, status:%s, sex:%s, age:%s, proficiency:%s).";
HEREDOC;
    /** @var Kernel */
    protected static $kernel;

    /** @var EntityManagerInterface */
    protected static $em;

    /** @var  YamlDbSetupEventTeam*/
    private $setup;

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public static function setUpBeforeClass()
    {
        (new Dotenv())->load(__DIR__ . '/../../.env');
        self::$kernel = self::bootKernel();
        /** @var EntityManagerInterface $em */
        self::$em = self::$kernel->getContainer()->get('doctrine.orm.setup_entity_manager');
        self::purge();
        self::loadSetupDump(__DIR__.'/../../../TestData19/Data-1600-setup');
    }


    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    protected static function purge()
    {
        /** @var EntityManagerInterface $em */
        $purger = new ORMPurger(self::$em);
        $purger->setPurgeMode(ORMPurger::PURGE_MODE_TRUNCATE);
        $conn = $purger->getObjectManager()->getConnection();
        $conn->query('SET FOREIGN_KEY_CHECKS=0');
        $purger->purge();
        $conn->query('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * @param $dumpDirectory
     * @throws \Doctrine\DBAL\DBALException
     */
    private static function loadSetupDump($dumpDirectory)
    {

        $conn = self::$em->getConnection();
        $conn->query('set GLOBAL net_buffer_length=3000000');
        $conn->query('SET foreign_key_checks = 0');
        $dumpFiles = self::findDumpFiles($dumpDirectory);
        foreach($dumpFiles as $file) {
            $sql = file_get_contents(($file));
            $conn->query($sql);
        }
        $conn->query('SET foreign_key_checks = 1');
        $conn->query('UNLOCK TABLES');
    }



    private static function findDumpFiles($pathfile)
    {
        $dumpFiles = [];
        if(is_dir($pathfile))  {
            $predump=scandir($pathfile);
            array_shift($predump);
            array_shift($predump);
            foreach($predump as $file){
                $parts = pathinfo($file);
                if($parts['extension']=='sql'){
                    $file=$pathfile.'/'.$parts['filename'].'.'.$parts['extension'];
                    $dumpFiles[]=$file;
                }
            }
        }
        return $dumpFiles;
    }


    /**
     * @return YamlDbSetupEventTeam
     */
    protected function getYamlDbSetupEventTeam()
    {
        return new YamlDbSetupEventTeam(self::$em);
    }


    public function setUp()
    {
        $this->setup = $this->getYamlDbSetupEventTeam();
    }

    /**
     * @throws AppBuildException
     * @throws \App\Common\AppParseException
     */
    public function test1900ValidEventTeam()
    {
        /** @noinspection PhpComposerExtensionStubsInspection */
        $expectedRelations = yaml_parse_file(__DIR__.'/setup-08-event-team.yml');
        $this->setup->parseEventsTeams(__DIR__.'/setup-08-event-team.yml');
        /** @var EventRepository $repository */
        $repository = self::$em->getRepository(Event::class);
        $actual = $repository->fetchEligibility();
        foreach($expectedRelations as $modelName => $groupingList) {
            foreach($groupingList as $grouping) {
                $types = $grouping['type'];
                $statii= $grouping['status'];
                $sexes = $grouping['sex'];
                $ages  = $grouping['age'];
                $proficiencies = $grouping['proficiency'];
                foreach($types as $expectedEventType => $expectedTeamTypes) {
                    foreach($statii as $expectedEventStatus => $expectedTeamStatii) {
                        foreach($sexes as $expectedEventSex => $expectedTeamSexes) {
                            foreach($proficiencies as $expectedEventProficiency => $expectedTeamProficiencies) {
                                foreach($ages as $expectedEventAge => $expectedTeamAges) {
                                    $this->compareActualExpected($actual,$modelName,
                                        $expectedEventType, $expectedTeamTypes,
                                        $expectedEventStatus, $expectedTeamStatii,
                                        $expectedEventSex, $expectedTeamSexes,
                                        $expectedEventAge, $expectedTeamAges,
                                        $expectedEventProficiency, $expectedTeamProficiencies);
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @param array $actual
     * @param string $modelName
     * @param $expectedEventType
     * @param $expectedTeamTypes
     * @param $expectedEventStatus
     * @param $expectedTeamStatii
     * @param $expectedEventSex
     * @param $expectedTeamSexes
     * @param $expectedEventAge
     * @param $expectedTeamAges
     * @param $expectedEventProficiency
     * @param $expectedTeamProficiencies
     * @throws AppBuildException
     */

    private function compareActualExpected(array $actual, string $modelName,
                                           $expectedEventType, $expectedTeamTypes,
                                           $expectedEventStatus, $expectedTeamStatii,
                                           $expectedEventSex, $expectedTeamSexes,
                                           $expectedEventAge, $expectedTeamAges,
                                           $expectedEventProficiency, $expectedTeamProficiencies)
    {
        /** @var ArrayCollection $actualEventCollection */
        if(!isset($actual[$modelName][$expectedEventType][$expectedEventStatus]
                [$expectedEventSex][$expectedEventProficiency][$expectedEventAge])){
            $index=[$modelName,$expectedEventType,$expectedEventStatus,$expectedEventSex,
                $expectedEventProficiency,$expectedEventAge];
            throw new AppBuildException(AppExceptionCodes::EXPECTED_STRUCTURE,
                    [__FILE__,__LINE__,'$actual',$index]);
        }
        $actualEventCollection = $actual[$modelName][$expectedEventType][$expectedEventStatus]
                                        [$expectedEventSex][$expectedEventProficiency][$expectedEventAge];

        /** @var Event $eventCurrent */
        $eventCurrent = $actualEventCollection->first();
        while($eventCurrent) {
            $actualDescribe = $eventCurrent->getDescribe();
            $expectedDescribe = ['type'=>$expectedEventType,'status'=>$expectedEventStatus,
                                 'sex'=>$expectedEventSex,'age'=>$expectedEventAge,
                                 'proficiency'=>$expectedEventProficiency];
            $this->assertArraySubset($expectedDescribe,$actualDescribe);
            /** @var ArrayCollection $teamClassCollection */
            $teamClassCollection = $eventCurrent->getTeamClass();
            foreach($expectedTeamTypes as $expectedTeamType) {
                foreach($expectedTeamStatii as $expectedTeamStatus) {
                    foreach($expectedTeamSexes as $expectedTeamSex) {
                        foreach($expectedTeamProficiencies as $expectedTeamProficiency) {
                            foreach($expectedTeamAges as $expectedTeamAge) {
                                $expectedDescribe=['type'=>$expectedTeamType,
                                                 'status'=>$expectedTeamStatus,
                                                 'sex'=>$expectedTeamSex,
                                                 'age'=>$expectedTeamAge,
                                                 'proficiency'=>$expectedTeamProficiency];
                                $this->assertTrue(
                                    $teamClassCollection->exists(/**
                                     * @param $key
                                     * @param $teamClass
                                     * @return bool
                                     */
                                        function(/** @noinspection PhpUnusedParameterInspection */
                                            $key, $teamClass) use ($expectedDescribe) {
                                            /** @noinspection PhpUndefinedMethodInspection */
                                            $actualDescribe=$teamClass->getDescribe();
                                        return $actualDescribe == $expectedDescribe;
                                    }),sprintf(self::QUALIFICATIONS_NOT_FOUND,
                                                $expectedTeamType,
                                                $expectedTeamStatus,
                                                $expectedTeamSex,
                                                $expectedTeamAge,
                                                $expectedTeamProficiency,
                                                $modelName,
                                                $expectedEventType,
                                                $expectedEventStatus,
                                                $expectedEventSex,
                                                $expectedEventAge,
                                                $expectedEventProficiency));
                            }
                        }

                    }
                }
            }
            $eventCurrent = $actualEventCollection->next();
        }

    }
}