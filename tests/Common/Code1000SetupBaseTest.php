<?php
/**
 * Copyright (c) 2018. Mark Garber.  All rights reserved.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 11/30/18
 * Time: 3:32 PM
 */

namespace App\Tests\Common;


use App\Common\YamlDbSetupBase;
use App\Entity\Setup\Domain;
use App\Entity\Setup\Model;
use App\Entity\Setup\Value;
use App\Kernel;
use App\Repository\Setup\DomainRepository;
use App\Repository\Setup\ModelRepository;
use App\Repository\Setup\ValueRepository;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Dotenv\Dotenv;

class Code1000SetupBaseTest extends KernelTestCase
{
    /** @var  Kernel*/
    protected static $kernel;

    public static function setUpBeforeClass()
    {
       (new Dotenv()) -> load(__DIR__.'/../../.env');
    }

    protected function getEntityManager()
    {
        return self::$kernel->getContainer()->get('doctrine.orm.setup_entity_manager');
    }


    protected function getRepository($className)
    {
        $em = $this->getEntityManager();
        return $em->getRepository($className);
    }


    protected function getYamlDbBaseSetup()
    {
        /** @var EntityManagerInterface $em */
        $em = $this->getEntityManager();
        return new YamlDbSetupBase($em);
    }

    /**
     * @param array|null $excluded
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function purge(array $excluded=[])
    {
        $em = $this->getEntityManager();
        $purger = new ORMPurger($em,$excluded);
        $purger->setPurgeMode(ORMPurger::PURGE_MODE_TRUNCATE);
        $conn = $purger->getObjectManager()->getConnection();
        $conn->query('SET FOREIGN_KEY_CHECKS=0');
        $purger->purge();
        $conn->query('SET FOREIGN_KEY_CHECKS=1');
    }


    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function setUp()
    {
        self::$kernel = self::bootKernel();
        $this->purge();
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test1010Model()
    {
        /** @var ModelRepository $repository */
        $repository = $this->getRepository(Model::class);
        $setup = $this->getYamlDbBaseSetup();
        $parseResult = $setup->parseModels(__DIR__ . '/../../tests/Common/setup-01-models.yml');
        $readResult = $repository->read();
        $this->assertEquals($readResult,$parseResult);
        $this->assertEquals(3, count($readResult));
        $this->assertEquals(3, count($parseResult));
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test1020Domain()
    {
        /** @var DomainRepository $repository */
        $repository = $this->getRepository(Domain::class);
        $setup = $this->getYamlDbBaseSetup();
        $parseResult = $setup->parseDomains(__DIR__ . '/../../tests/Common/setup-02-domains.yml');
        $readResult = $repository->read();
        $this->assertEquals($readResult,$parseResult);
        $this->assertEquals(11, count($readResult));
        $this->assertEquals(11, count($parseResult));
    }

    /**
     * @return YamlDbSetupBase
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function parseModelsAndDomains()
    {
        /** @var YamlDbSetupBase $setup */
        $setup = $this->getYamlDbBaseSetup();
        $setup->parseModels(__DIR__ . '/../../tests/Common/setup-01-models.yml');
        $setup->parseDomains(__DIR__ . '/../../tests/Common/setup-02-domains.yml');
        return $setup;
    }

    /**
     * @return YamlDbSetupBase
     * @throws \App\Common\AppParseException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function parseModelsDomainsValues()
    {
        $setup = $this->parseModelsAndDomains();
        $setup->parseValues(__DIR__ . '/../../tests/Common/setup-03-values.yml');
        return $setup;
    }

    /**
     * @expectedException \App\Common\AppParseException
     * @expectedExceptionMessage  Found 'invalid-key' at (row:4,col:1) but expected [style|substyle|
     * @expectedExceptionCodes App\Common\AppExceptionCode::FOUND_BUT_EXPECTED
     *
     * @throws \App\Common\AppParseException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test1030ValuesInvalidKey()
    {
        $setup = $this->parseModelsAndDomains();
        $setup->parseValues(__DIR__.'/data-1030-values-invalid-key.yml');
    }

    /**
     * @expectedException \App\Common\AppParseException
     * @expectedExceptionMessage   Found 'invalid-key' at (row:5,col:12) but expected [abbr|note|
     * @expectedExceptionCodes App\Common\AppExceptionCode::FOUND_BUT_EXPECTED
     *
     * @throws \App\Common\AppParseException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test1040ValuesInvalidKey()
    {
        $setup = $this->parseModelsAndDomains();
        $setup->parseValues(__DIR__.'/data-1040-values-invalid-key.yml');
    }

    /**
     * @throws \App\Common\AppParseException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test1050Value()
    {
        /** @var ValueRepository $repository */
        $repository = $this->getRepository(Value::class);
        /** @var YamlDbSetupBase $setup */
        $setup = $this->parseModelsAndDomains();
        $parseResult=$setup->parseValues(__DIR__ . '/../../tests/Common/setup-03-values.yml');
        $readResult =$repository->read();
        $this->assertEquals($readResult,$parseResult);
        $this->assertEquals(11, count($parseResult));
        $this->assertEquals(11, count($readResult));
        $this->assertEquals(count($parseResult['age']), count($readResult['age']));
        $this->assertEquals(count($parseResult),count($readResult));
    }


    /**
     * @expectedException \App\Common\AppParseException
     * @expectedExceptionMessage 'Invalid Model' at (row:1,col:1) is an unrecognized value in file:
     * @expectedExceptionCode \App\Common\AppExceptionCodes::UNRECOGNIZED_VALUE
     *
     * @throws \App\Common\AppParseException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test1060InvalidModel()
    {
        $setup = $this->parseModelsDomainsValues();
        $setup->parseModelValues(__DIR__ . '/../../tests/Common/data-1060-invalid-model.yml');
    }


    /**
     * @expectedException \App\Common\AppParseException
     * @expectedExceptionMessage  Found 'invalid_key' at (row:3,col:5) but expected [type|status|
     * @expectedExceptionCode \App\Common\AppExceptionCodes::FOUND_BUT_EXPECTED
     *
     * @throws \App\Common\AppParseException
     * @throws \App\Common\AppParseException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test1070InvalidKey()
    {
        $setup = $this->parseModelsDomainsValues();
        $setup->parseModelValues(__DIR__ . '/../../tests/Common/data-1070-invalid-key.yml');
    }



    /**
     * @expectedException \App\Common\AppParseException
     * @expectedExceptionMessage  Missing [status|sex] between lines 2-9 in file: data-1080-missing-keys.yml.
     * @expectedExceptionCode \App\Common\AppExceptionCodes::MISSING_KEYS
     *
     * @throws \App\Common\AppParseException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test1080MissingKeys()
    {
        /** @var YamlDbSetupBase $setup */
        $setup = $this->parseModelsDomainsValues();
        $setup->parseModelValues(__DIR__ . '/../../tests/Common/data-1080-missing-keys.yml');
    }

    /**
     * @expectedException \App\Common\AppParseException
     * @expectedExceptionMessage 'Invalid-Value' at (row:6,col:30) is an unrecognized value in file
     * @expectedExceptionCode \App\Common\AppExceptionCodes::UNRECOGNIZED_VALUE
     *
     * @throws \App\Common\AppParseException
     * @throws \App\Common\AppParseException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test1090InvalidValue()
    {
        $setup = $this->parseModelsDomainsValues();
        $setup->parseModelValues(__DIR__ . '/../../tests/Common/data-1090-invalid-value.yml');
    }

    /**
     * @throws \App\Common\AppParseException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function test1100ModelValue()
    {
        $setup = $this->parseModelsDomainsValues();
        $expected=$setup->parseModelValues(__DIR__ . '/../../tests/Common/setup-04-model-values.yml');
        /** @var ModelRepository $repository */
        $repository = $this->getRepository(Model::class);
        $actual = $repository->fetchQuickSearch();
        foreach($expected as $modelName=>$domainValueList) {
            $this->assertArrayHasKey($modelName,$actual);
            foreach($domainValueList as $domName=>$valueList) {
                $this->assertArrayHasKey($domName,$actual[$modelName]);
                foreach($valueList as $valName=>$expectedValue) {
                    $this->assertArrayHasKey($valName,$actual[$modelName][$domName]);
                    /** @var Value $actualValue */
                    $actualValue = $actual[$modelName][$domName][$valName];
                    /** @var Value $expectValue */
                    $expectValue = $expected[$modelName][$domName][$valName];
                    $this->assertEquals($expectValue->getName(),$actualValue->getName());
                    $this->assertEquals($expectValue->getId(), $actualValue->getId());
                }
            }
        }
    }


}