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


use App\Common\YamlDbSetupPerson;
use App\Common\YamlDbSetupTeamClass;
use App\Entity\Setup\AgeTeam;
use App\Entity\Setup\PrfTeam;
use App\Kernel;
use App\Repository\Setup\AgeTeamRepository;
use App\Repository\Setup\PrfTeamRepository;
use Doctrine\Common\Collections\ArrayCollection;
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

    public static function setUpBeforeClass()
    {
        (new Dotenv())->load(__DIR__ . '/../../.env');
    }

    /**
     * @return mixed
     */
    private function getEntityManager(): EntityManagerInterface
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
        $purger = new ORMPurger($em, $excluded);
        $purger->setPurgeMode(ORMPurger::PURGE_MODE_TRUNCATE);
        $conn = $purger->getObjectManager()->getConnection();
        $conn->query('SET FOREIGN_KEY_CHECKS=0');
        $purger->purge();
        $conn->query('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * @return YamlDbSetupTeamClass
     */
    protected function getYamlDbTeamClassSetup()
    {
        /** @var EntityManagerInterface $em */
        $em = $this->getEntityManager();
        return new YamlDbSetupTeamClass($em);
    }

    /**
     * @throws \App\Common\AppParseException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function parseModelsDomainsValues()
    {
        /** @var YamlDbSetupPerson $setup */
        $setup = $this->getYamlDbTeamClassSetup();
        $setup->parseModels(__DIR__ . '/../../tests/Common/setup-01-models.yml');
        $setup->parseDomains(__DIR__ . '/../../tests/Common/setup-02-domains.yml');
        $setup->parseValues(__DIR__ . '/../../tests/Common/setup-03-values.yml');
        $setup->parseModelValues(__DIR__ . '/../../tests/Common/setup-04-model-values.yml');
        $setup->parsePersons(__DIR__ . '/../../tests/Common/setup-05-persons.yml');
        $this->setup = $setup;
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
        /** @var PrfTeamRepository $prfRepository */
        $prfRepository = $this->getEntityManager()->getRepository(PrfTeam::class);
        /** @var AgeTeamRepository $ageRepository */
        $ageRepository = $this->getEntityManager()->getRepository(AgeTeam::class);
        $actualPrf = $prfRepository->fetchQuickSearch();
        $actualAge = $ageRepository->fetchQuickSearch();
        $count=0;
        foreach ($expected as $type => $statusList) {
            $this->assertArrayHasKey($type, $actualPrf);
            $this->assertArrayHasKey($type, $actualAge);
            foreach ($statusList as $status => $agePrfExpected) {
                $this->assertArrayHasKey($status, $actualPrf[$type]);
                $this->assertArrayHasKey($status, $actualAge[$type]);
                foreach ($agePrfExpected['prf'] as $sex => $proficienciesExpected) {
                    $this->assertArrayHasKey($sex, $actualPrf[$type][$status]);
                    /**
                     * @var string $proficiency
                     * @var ArrayCollection $expectedCollection
                     */
                    foreach ($proficienciesExpected as $proficiency=>$expectedCollection) {
                        $this->assertArrayHasKey($proficiency, $actualPrf[$type][$status][$sex]);
                        /** @var ArrayCollection  $actualCollection */
                        $actualCollection = $actualPrf[$type][$status][$sex][$proficiency];
                        /** @var PrfTeam $expectedTeam */
                        $expectedTeam = $expectedCollection->first();

                        while($expectedTeam) {
                            $actualTeam = $actualCollection->get($expectedTeam->getId());
                            $expectedArray  = $expectedTeam->getPrfTeamClass()->getDescribe();
                            /** @noinspection PhpComposerExtensionStubsInspection */
                            $expectedJSON = json_encode($expectedArray);
                            $this->assertEquals($expectedTeam,$actualTeam,
                                "Expected $expectedJSON after checking $count records");
                            $expectedTeam = $actualCollection->next();
                            $count++;
                        }
                        $this->assertEquals($expectedCollection->count(),$actualCollection->count());

                    }
                }
                foreach ($agePrfExpected['age'] as $age => $expectedCollection) {
                    $this->assertArrayHasKey($age, $actualAge[$type][$status]);
                    $actualCollection = $actualAge[$type][$status][$age];
                    $expectedTeam = $expectedCollection->first();
                    while($expectedTeam) {
                        $actualTeam = $actualCollection->get($expectedTeam->getId());

                        /** @noinspection PhpComposerExtensionStubsInspection */
                        $expectedJSON = json_encode($expectedTeam->getAgeTeamClass()->getDescribe());
                        $this->assertEquals($expectedTeam,$actualTeam,
                            "Expected $expectedJSON after checking $count records");
                        $expectedTeam = $actualCollection->next();
                        $count++;
                    }
                    $this->assertEquals($expectedCollection->count(),$actualCollection->count());
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