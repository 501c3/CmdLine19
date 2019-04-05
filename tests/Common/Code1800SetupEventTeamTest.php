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


use App\Common\YamlDbSetupEventTeam;
use App\Kernel;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Dotenv\Dotenv;

class Code1800SetupEventTeamTest extends KernelTestCase
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
     * @expectedException \App\Common\AppParseException
     * @expectedExceptionMessage Found 'Invalid Model' at (row:1,col:1) but expected [ISTD Medal Exams-2019
     * @expectedExceptionCode \App\Common\AppExceptionCodes::FOUND_BUT_EXPECTED
     * @throws \App\Common\AppParseException
     */
    public function test1810InvalidModel()
    {
        $this->setup->parseEventsTeams(__DIR__ . '/data-1810-invalid-model.yml');
    }


    /**
     * @expectedException \App\Common\AppParseException
     * @expectedExceptionMessage  Found 'invalid_key' at (row:2,col:5) but expected [type|status|sex|proficiency|age]
     * @expectedExceptionCode  \App\Common\AppExceptionCodes::FOUND_BUT_EXPECTED
     * @throws \App\Common\AppParseException
     */
    public function test1820InvalidDomainKey()
    {
        $this->setup->parseEventsTeams(__DIR__ . '/data-1820-invalid-domain-key.yml');
    }

    /**
     * @expectedException \App\Common\AppParseException
     * @expectedExceptionMessage  'Invalid Value Event' at (row:3,col:7) is an unrecognized value in file:
     * @expectedExceptionCode \App\Common\AppExceptionCodes::UNRECOGNIZED_VALUE
     * @throws \App\Common\AppParseException
     */

    public function test1830InvalidValueEvent()
    {
        $this->setup->parseEventsTeams(__DIR__ . '/data-1830-invalid-value-event.yml');
    }


    /**
     * @expectedException \App\Common\AppParseException
     * @expectedExceptionMessage  'Invalid Value Team' at (row:4,col:9) is an unrecognized
     * @expectedExceptionCode \App\Common\AppExceptionCodes::UNRECOGNIZED_VALUE
     * @throws \App\Common\AppParseException
     */
    public function test1840InvalidValueTeam()
    {
        $this->setup->parseEventsTeams(__DIR__ . '/data-1840-invalid-value-team.yml');
    }


    /**
     * @expectedException \App\Common\AppParseException
     * @expectedExceptionMessage  Expected structure following 'Open Silver' at (row:145,col:7)
     * @expectedExceptionCode  \App\Common\AppExceptionCodes::EXPECTED_STRUCTURE
     * @throws \App\Common\AppParseException
     */
    public function test1850InvalidExpectStructure()
    {
        $this->setup->parseEventsTeams(__DIR__ . '/data-1850-invalid-expect_structure.yml');
    }


    /**
     * @expectedException \App\Common\AppParseException
     * @expectedExceptionMessage  'Invalid Value' at (row:209,col:18) is an unrecognized value in file:
     * @expectedExceptionCode \App\Common\AppExceptionCodes::UNRECOGNIZED_VALUE
     *
     * @throws \App\Common\AppParseException
     */
    public function test1860InvalidErrorDelayed()
    {
        $this->setup->parseEventsTeams(__DIR__ . '/data-1860-invalid-error-delayed.yml');
    }

}