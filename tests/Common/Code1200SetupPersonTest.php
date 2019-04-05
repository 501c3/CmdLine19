<?php
/**
 * Copyright (c) 2018. Mark Garber.  All rights reserved.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 12/1/18
 * Time: 9:30 PM
 */

namespace App\Tests\Common;


use App\Common\YamlDbSetupPerson;
use App\Entity\Setup\AgePerson;
use App\Entity\Setup\PrfPerson;
use App\Kernel;
use App\Repository\Setup\AgePersonRepository;
use App\Repository\Setup\PrfPersonRepository;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Dotenv\Dotenv;

class Code1200SetupPersonTest extends KernelTestCase
{
    /** @var Kernel */
    protected static $kernel;

    /** @var YamlDbSetupPerson */
    private $setup;

    public static function setUpBeforeClass()
    {
        (new Dotenv())->load(__DIR__.'/../../.env');
    }

    /**
     * @return mixed
     */
    private function getEntityManager() : EntityManagerInterface
    {
        return self::$kernel->getContainer()->get('doctrine.orm.setup_entity_manager');
    }

    /**
     * @param array $excluded
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function purge(array $excluded = [])
    {
        /** @var EntityManagerInterface $em */
        $em = $this->getEntityManager();
        $purger = new ORMPurger($em,$excluded);
        $purger->setPurgeMode(ORMPurger::PURGE_MODE_TRUNCATE);
        $conn = $purger->getObjectManager()->getConnection();
        $conn->query('SET FOREIGN_KEY_CHECKS=0');
        $purger->purge();
        $conn->query('SET FOREIGN_KEY_CHECKS=1');

    }

    protected function getYamlDbPersonSetup()
    {
        /** @var EntityManagerInterface $em */
        $em = $this->getEntityManager();
        return new YamlDbSetupPerson($em);
    }


    /**
     * @throws \App\Common\AppParseException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function parseModelsDomainsValues()
    {
        /** @var YamlDbSetupPerson $setup */
        $setup = $this->getYamlDbPersonSetup();
        $setup->parseModels(__DIR__ . '/../../tests/Common/setup-01-models.yml');
        $setup->parseDomains(__DIR__ . '/../../tests/Common/setup-02-domains.yml');
        $setup->parseValues(__DIR__ . '/../../tests/Common/setup-03-values.yml');
        $this->setup=$setup;
    }


    /**
     * @throws \App\Common\AppParseException
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function setUp()
    {
        self::$kernel = self::bootKernel();
        $this->purge();
        $this->parseModelsDomainsValues();
    }


    /**
     * @expectedException \App\Common\AppParseException
     * @expectedExceptionMessage Found 'invalid_key' at (row:6,col:3) but expected [type|status|sex|age|proficiency|
     * @expectedExceptionCode \App\Common\AppExceptionCodes::FOUND_BUT_EXPECTED
     * @throws \Exception
     */
    public function test1210InvalidKey()
    {
        $this->setup->parsePersons(__DIR__.'/../../tests/Common/data-1210-invalid-key.yml');
    }

    /**
     * @expectedException \App\Common\AppParseException
     * @expectedExceptionMessage Missing [age] between lines 1-9 in file: data-1220-missing-key.yml. Reference: 1030
     * @expectedExceptionCode \App\Common\AppExceptionCodes::MISSING_KEYS
     * @throws \App\Common\AppParseException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
 */
    public function test1220MissingKey()
    {
        $this->setup->parsePersons(__DIR__.'/../../tests/Common/data-1220-missing-key.yml');
    }

    /**
     * @expectedException \App\Common\AppParseException
     * @expectedExceptionMessage 'Invalid Value' at (row:2,col:11) is an unrecognized value in file:
     * @expectedExceptionCode  \App\Common\AppExceptionCodes::UNRECOGNIZED_VALUE
     * @throws \App\Common\AppParseException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test1230InvalidValue()
    {
        $this->setup->parsePersons(__DIR__.'/../../tests/Common/data-1230-invalid-value.yml');
    }

    /**
     * @expectedException \App\Common\AppParseException
     * @expectedExceptionMessage 'Invalid Value' at (row:4,col:5) is an unrecognized value in file:
     * @expectedExceptionCode \App\Common\AppExceptionCodes::UNRECOGNIZED_VALUE
     * @throws \App\Common\AppParseException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test1240InvalidValue()
    {
        $this->setup->parsePersons(__DIR__.'/../../tests/Common/data-1240-invalid-value.yml');
    }


    /**
     * @expectedException \App\Common\AppParseException
     * @expectedExceptionMessage '1 16' at (row:6,col:8) is an invalid numeric range in file:
     * @expectedExceptionCode \App\Common\AppExceptionCodes::INVALID_RANGE
     * @throws \App\Common\AppParseException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test1250InvalidNumeric()
    {
        $this->setup->parsePersons(__DIR__.'/../../tests/Common/data-1250-invalid-numeric.yml');
    }

    /**
     * @expectedException \App\Common\AppParseException
     * @expectedExceptionMessage 'X1-16' at (row:6,col:8) is an invalid numeric range in file:
     * @expectedExceptionCode \App\Common\AppExceptionCodes::INVALID_RANGE
     * @throws \App\Common\AppParseException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test1260InvalidNumeric()
    {
        $this->setup->parsePersons(__DIR__.'/../../tests/Common/data-1260-invalid-numeric.yml');
    }

    /**
     * @expectedException \App\Common\AppParseException
     * @expectedExceptionMessage '16-15' at (row:6,col:8) is an invalid numeric range in file
     * @expectedExceptionCode \App\Common\AppExceptionCodes::INVALID_RANGE
     * @throws \App\Common\AppParseException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test1270InvalidNumeric()
    {
        $this->setup->parsePersons(__DIR__.'/../../tests/Common/data-1270-invalid-numeric.yml');
    }

    /**
     * @throws \App\Common\AppParseException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test1280PersonBuildInitial()
    {
        $expected = $this->setup->parsePersons(__DIR__.'/../../tests/Common/setup-05-persons.yml');
        $this->assertTrue(!is_null($expected));
    }


    /**
     * @throws \App\Common\AppParseException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test1300PersonBuild()
    {
        $expected = $this->setup->parsePersons(__DIR__.'/../../tests/Common/setup-05-persons.yml');
        /** @var AgePersonRepository $ageRepository */
        $ageRepository = $this->getEntityManager()->getRepository(AgePerson::class);
        /** @var PrfPersonRepository $prfRepository */
        $prfRepository = $this->getEntityManager()->getRepository(PrfPerson::class);
        $actualAge = $ageRepository->fetchQuickSearch();
        $actualPrf = $prfRepository->fetchQuickSearch();
        foreach($expected as $type=>$statusList) {
            $this->assertArrayHasKey($type, $actualAge);
            $this->assertArrayHasKey($type, $actualPrf);
            foreach($statusList as $status=>$agePrfRecords) {
                    /**
                     * @var string $proficiency
                     * @var PrfPerson $expectObject
                     */
                foreach($agePrfRecords['prf'] as $sex=>$proficiencyList) {
                    $this->assertArrayHasKey($sex,$actualPrf[$type][$status]);
                    foreach($proficiencyList as $proficiency=>$designateList) {
                        $this->assertArrayHasKey($status,$actualAge[$type]);
                        $this->assertArrayHasKey($status,$actualPrf[$type]);
                        /** @var AgePerson $actualObject */
                        $this->assertArrayHasKey($proficiency, $actualPrf[$type][$status][$sex]);
                        foreach($designateList as $designate=>$expectObject){
                            $actualObject = $actualPrf[$type][$status][$sex][$proficiency][$designate];
                            $this->assertEquals($expectObject->getDescribe(),$actualObject->getDescribe());
                        }
                    }
                }
                foreach($agePrfRecords['age'] as $years=>$designateList) {
                    $this->assertArrayHasKey($years,$actualAge[$type][$status]);
                    foreach($designateList as $designate=>$expectObject) {
                        $this->assertArrayHasKey($designate,$actualAge[$type][$status][$years]);
                        $actualObject = $actualAge[$type][$status][$years][$designate];
                        $this->assertEquals($expectObject->getDescribe(),$actualObject->getDescribe());
                    }
                }
            }
        }
    }
}