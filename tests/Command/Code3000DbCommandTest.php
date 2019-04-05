<?php
/**
 * Copyright (c) 2018. Mark Garber.  All rights reserved.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 12/19/18
 * Time: 2:47 PM
 */

namespace App\Tests\Command;


use App\Command\DbBuildSetupCommand;
use App\Command\DbLoadCommand;
use App\Entity\Setup\Model;
use App\Kernel;
use App\Repository\Model\ModelRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Dotenv\Dotenv;
use /** @noinspection PhpUnusedAliasInspection */
    App\Command\DbTruncateCommand;

class Code3000DbCommandTest extends KernelTestCase
{
    const DUMP_SETUP = '/home/mgarber/GeorgiaDancesport/TestData19/Data19-setup';

    const TEST_BUILDS = false;

    /** @var Kernel */
    protected static $kernel;

    /** @var Application application */
    private static $application;

    /** @var EntityManagerInterface */
    private static $emSetup;
    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    public static function setUpBeforeClass()
    {
        (new Dotenv())->load(__DIR__.'/../../.env');
    }

    public function setUp()
    {
        self::$kernel = self::bootKernel();
        self::$application = new Application(self::$kernel);
        self::$application->add(new DbTruncateCommand(self::$kernel));
        self::$application->add(new DbLoadCommand(self::$kernel));
        self::$application->add(new DbBuildSetupCommand(self::$kernel));
        self::$emSetup = self::$kernel->getContainer()->get("doctrine.orm.setup_entity_manager");
        self::purgeDatabase();
    }

    /**
     * @param string $dbname
     * @throws \Doctrine\DBAL\DBALException
     */
    private static function purgeDatabase()
    {
        /** @var Connection $conn */
        $conn = self::$emSetup->getConnection();
        $sm = $conn->getSchemaManager();
        $tables = $sm->listTables();
        $conn->query('SET foreign_key_checks = 0');
        /** @var Doctrine/DBAL/Schema/Table $table */
        foreach($tables as $table) {
            $statement = 'TRUNCATE '.$table->getName();
            $conn->exec($statement);
        }
        $conn->query('SET foreign_key_checks = 1');
    }

    public function test3110SetupMissingMaster()
    {
        $command = self::$application->find('db:build:setup');
        $parameters=['command'=>$command->getName(),'masterFile'=>__DIR__.'/data-3110-master-non-existent.yml'];
        $commandTester = new CommandTester($command);
        $commandTester->execute($parameters);
        $output = $commandTester->getDisplay();
        $this->assertContains(' Missing master file:',$output);
    }


    public function test3120SetupMasterFileNotFound()
    {
        $command = self::$application->find('db:build:setup');
        $parameters=['command'=>$command->getName(),'masterFile'=> __DIR__ . '/data-3120-file-not-found.yml'];
        $commandTester = new CommandTester($command);
        $commandTester->execute($parameters);
        $output = $commandTester->getDisplay();
        $this->assertContains(' Found event-values but expected [models,domains,values,model-values,',$output);
    }

    public function test3130SetupMasterMissingComponent()
    {

        $command = self::$application->find('db:build:setup');
        $parameters=['command'=>$command->getName(),'masterFile'=> __DIR__ . '/data-3130-component-not-found.yml'];
        $commandTester = new CommandTester($command);
        $commandTester->execute($parameters);
        $output = $commandTester->getDisplay();
        $this->assertContains('Found event-values but expected [models,domains,values,model-values,',$output);
    }


    /**
     * @return string
     */
    private function run3100DbSetupLoad()
    {
        $command = self::$application->find('db:load');
        $commandTester = new CommandTester($command);
        $parameters=['command'=>$command->getName(),'dbname'=>'setup','dump'=>self::DUMP_SETUP];
        $commandTester->execute($parameters);
        $output = $commandTester->getDisplay();
        return $output;
    }


    /**
     * @return string
     */
    private function run3100DbSetupTruncate()
    {
        /** @var EntityManagerInterface $em */
        $command = self::$application->find('db:truncate');
        $commandTester = new CommandTester($command);
        $parameters  = ['command'=>$command->getName(),'dbname'=>'setup'];
        $commandTester->execute($parameters);
        $output = $commandTester->getDisplay();
        return $output;
    }


    private function run3100DbSetupBuild()
    {

        $command = self::$application->find('db:build:setup');
        $parameters=['command'=>$command->getName(),'masterFile'=> __DIR__ . '/setup-09-master.yml'];
        $commandTester = new CommandTester($command);
        $commandTester->execute($parameters);
        $output=$commandTester->getDisplay();
        return $output;
    }

    /**
     *
     */
    public function test3150SetupValid()
    {

        /** @var ModelRepository $repositoryModel */
        $repositoryModel=self::$emSetup->getRepository(Model::class);

        $modelsBeforeLoad = $repositoryModel->findAll();
        $this->assertEquals(0,count($modelsBeforeLoad));

        $this->run3100DbSetupLoad();
        $modelsAfterLoad = $repositoryModel->findAll();
        $this->assertEquals(3,count($modelsAfterLoad));

        $this->run3100DbSetupTruncate();
        $modelsAfterTruncate = $repositoryModel->findAll();
        $this->assertEquals(0,count($modelsAfterTruncate));

        if(self::TEST_BUILDS) {
            $this->run3100DbSetupBuild();
        }
    }
}