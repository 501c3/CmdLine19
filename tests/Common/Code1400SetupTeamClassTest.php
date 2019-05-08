<?php
/**
 * Copyright (c) 2018. Mark Garber.  All rights reserved.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 12/2/18
 * Time: 11:55 PM
 */

namespace App\Tests\Common;

use App\Common\YamlDbSetupTeamClass;
use App\Entity\Setup\AgeTeam;
use App\Entity\Setup\Person;
use App\Entity\Setup\PrfTeam;
use App\Entity\Setup\Team;
use App\Kernel;
use App\Repository\Setup\AgeTeamRepository;
use App\Repository\Setup\PrfTeamRepository;
use App\Repository\Setup\TeamRepository;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Dotenv\Dotenv;

class Code1400SetupTeamClassTest extends KernelTestCase
{
    /** @var Kernel */
    protected static $kernel;

    /** @var YamlDbSetupTeamClass */
    private $setup;

    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public static function setUpBeforeClass()
    {
        (new Dotenv())->load(__DIR__ . '/../../.env');
        self::$kernel = self::bootKernel();
        self::purge();
        self::loadSetupDump(__DIR__.'/../../../TestData19/Data-1200-setup');
    }

    /**
     * @param $pathfile
     * @return array
     */
    private static function findDumpFiles(string $pathfile)
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
     * @param $dumpDirectory
     * @throws \Doctrine\DBAL\DBALException
     */
    private static function loadSetupDump($dumpDirectory)
    {
        /** @var EntityManagerInterface $em */
        $em = self::$kernel->getContainer()->get('doctrine.orm.setup_entity_manager');
        $conn = $em->getConnection();
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

    /**
     * @param array $excluded
     * @throws \Doctrine\DBAL\DBALException
     */
    protected static function purge(array $excluded = [])
    {
        /** @var EntityManagerInterface $em */
        $em = self::$kernel->getContainer()->get('doctrine.orm.setup_entity_manager');
        $purger = new ORMPurger($em, $excluded);
        $purger->setPurgeMode(ORMPurger::PURGE_MODE_TRUNCATE);
        $conn = $purger->getObjectManager()->getConnection();
        $conn->query('SET FOREIGN_KEY_CHECKS=0');
        $purger->purge();
        $conn->query('SET FOREIGN_KEY_CHECKS=1');
    }


    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public function setUp()
    {
        self::$kernel = self::bootKernel();
        $em = self::$kernel->getContainer()->get('doctrine.orm.setup_entity_manager');
        $excludedTables =['domain','value','person','person_has_value'];
        self::purge($excludedTables);
        $this->setup = new YamlDbSetupTeamClass($em);;
    }


    /**
     * @expectedException \App\Common\AppParseException
     * @expectedExceptionMessage  Found 'invalid_key' at (row:3,col:3) but expected [type|status|sex|age|proficiency]
     * @expectedExceptionCode \App\Common\AppExceptionCodes::FOUND_BUT_EXPECTED
     * @throws \App\Common\AppParseException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test1410InvalidKey()
    {
        $this->setup->parseTeams(__DIR__ . '/../../tests/Common/data-1410-invalid-key.yml');
    }

    /**
     * @expectedException \App\Common\AppParseException
     * @expectedExceptionMessage  Missing [sex] between lines 1-6 in file: data-1420-missing-key.yml.
     * @expectedExceptionCode \App\Common\AppExceptionCodes::MISSING_KEYS
     * @throws \App\Common\AppParseException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test1420MissingKey()
    {
        $this->setup->parseTeams(__DIR__ . '/../../tests/Common/data-1420-missing-key.yml');
    }

    /**
     * @expectedException \App\Common\AppParseException
     * @expectedExceptionMessage 'Invalid-Value' at (row:6,col:5) is an unrecognized value in
     * @expectedExceptionCode \App\Common\AppExceptionCodes::UNRECOGNIZED_VALUE
     * @throws \App\Common\AppParseException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test1430InvalidValue()
    {
        $this->setup->parseTeams(__DIR__ . '/../../tests/Common/data-1430-invalid-value.yml');
    }

    /**
     * @expectedException \App\Common\AppParseException
     * @expectedExceptionMessage 'x1-4' at (row:8,col:14) is an invalid numeric range in file:
     * @expectedExceptionMessage \App\Common\AppExceptionCodes::INVALID_RANGE
     * @throws \App\Common\AppParseException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test1440InvalidNumeric()
    {
        $this->setup->parseTeams(__DIR__ . '/../../tests/Common/data-1440-invalid-numeric.yml');
    }


    /**
     * @param array $expected
     */
    private function iterateThroughDatabase(array $expected)
    {
        $em = self::$kernel->getContainer()->get('doctrine.orm.setup_entity_manager');
        /** @var TeamRepository $repository */
        $repository=$em->getRepository(Team::class);
        /** @var array $actual */
        $actual = $repository->fetchQuickSearch();
        foreach($expected as $expectedType=>$expectedStatusList) {
            $this->assertArrayHasKey($expectedType,$actual);
            foreach($expectedStatusList as $expectedStatus=>$expectedSexList) {
                $this->assertArrayHasKey($expectedStatus, $actual[$expectedType]);
                foreach($expectedSexList as $expectedSex=>$expectedProficiencyList) {
                    $this->assertArrayHasKey($expectedSex, $actual[$expectedType][$expectedStatus]);
                    foreach($expectedProficiencyList as $expectedProficiency=>$expectedAgeList) {
                        $this->assertArrayHasKey($expectedProficiency, $actual[$expectedType][$expectedStatus][$expectedSex]);
                        foreach($expectedAgeList as $expectedAge=>$teamCollection){
                            /** @var Team $team */
                            foreach($teamCollection as $team) {
                                $class = $team->getTeamClass();
                                $expectedDescribe=[ 'type'=>$expectedType,
                                                    'status'=>$expectedStatus,
                                                    'sex'=>$expectedSex,
                                                    'proficiency'=>$expectedProficiency,
                                                    'age'=>$expectedAge];
                                $actualDescribe = $class->getDescribe();
                                $this->assertEquals($expectedDescribe,$actualDescribe);
                                $persons = $team->getPerson()->toArray();
                                foreach($persons as $person) {
                                    switch(count($person)){
                                        case 1:
                                            /** @var Person $soloPerson */
                                            $soloPerson = $person[0];
                                            $describe = $soloPerson->getDescribe();
                                            $this->assertEquals($expectedStatus, $describe['status']);
                                            $this->assertEquals($expectedType, $describe['type']);
                                            $this->assertEquals($expectedProficiency, $describe['proficiency']);
                                            break;
                                        case 2:
                                            /** @var Person $leftPerson */
                                            $leftPerson = $person[0];
                                            /** @var Person $rightPerson */
                                            $rightPerson= $person[1];
                                            $leftDescribe = $leftPerson->getDescribe();
                                            $rightDescribe= $rightPerson->getDescribe();
                                            $teamProficiency=$leftDescribe['proficiency']==$expectedProficiency?
                                                           $leftDescribe['proficiency']:$rightDescribe['proficiency'];
                                            $teamAge = $leftDescribe['age']==$expectedAge?
                                                            $leftDescribe['age']:$rightDescribe['age'];
                                            $this->assertEquals($expectedProficiency, $teamProficiency);
                                            $this->assertEquals($expectedAge, $teamAge);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }



    /**
     * @throws \App\Common\AppParseException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test1450ValidTeamsPartial()
    {
        $expected = $this->setup->parseTeams(__DIR__ . '/../../tests/Common/data-1450-valid-teams.yml');
        $this->iterateThroughDatabase($expected);
    }

    /**
     * @throws \App\Common\AppParseException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test1460ValidTeamsPartial()
    {
        $expected = $this->setup->parseTeams(__DIR__ . '/../../tests/Common/data-1460-valid-teams.yml');
        $this->iterateThroughDatabase($expected);
    }

    /**
     * @throws \App\Common\AppParseException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test1470ValidTeams()
    {
        $expected = $this->setup->parseTeams(__DIR__ . '/../../tests/Common/data-1470-valid-teams.yml');
        $this->iterateThroughDatabase($expected);
    }

    /**
     * @throws \App\Common\AppParseException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test1500TeamValidComplete()
    {
        $expected = $this->setup->parseTeams(__DIR__ . '/../../tests/Common/setup-06-teams.yml');
        $this->iterateThroughDatabase($expected);
    }
}