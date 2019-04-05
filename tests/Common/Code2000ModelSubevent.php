<?php
/**
 * Copyright (c) 2019. Mark Garber.  All rights reserved.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 *
 * Date: 1/9/19
 * Time: 12:07 AM
 */

namespace App\Tests\Common;


use App\Common\YamlDbModelSubevent;
use App\Entity\Model\Event;
use App\Entity\Model\Subevent;
use App\Kernel;
use App\Repository\Model\SubeventRepository;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Dotenv\Dotenv;

class Code2000ModelSubevent extends KernelTestCase
{

    const /** @noinspection SqlNoDataSourceInspection */
        /** @noinspection SqlResolve */
        ALTER_TABLE='ALTER TABLE subevent AUTO_INCREMENT=1';

    /** @var Kernel */
   protected static $kernel;

   /** @var YamlDbModelSubevent */
   protected static $yamlDbModelSubevent;

   /** @var EntityManagerInterface */
   protected static $emModel;

    /** @var EntityManagerInterface */
   protected static $emSetup;


    /**
     * @throws \Doctrine\DBAL\DBALException
     */
   public static function setUpBeforeClass()
   {
       (new Dotenv())->load(__DIR__.'/../../.env');

       self::$kernel = self::bootKernel();
       /** @var EntityManagerInterface $emModel */
       self::$emModel=self::$kernel->getContainer()->get('doctrine.orm.model_entity_manager');
       /** @var EntityManagerInterface $emModel */
       self::$emSetup=self::$kernel->getContainer()->get('doctrine.orm.setup_entity_manager');
       self::purgeDatabase(self::$emModel);
       self::purgeDatabase(self::$emSetup);
       self::loadSetupDump(self::$emSetup,'/home/mgarber/GeorgiaDancesport/TestData19/Data19-1900');
       self::$yamlDbModelSubevent = new YamlDbModelSubevent(self::$emModel);
       $conn=self::$emModel->getConnection();
       $conn->exec('CALL pull_from_setup()');
   }

    /**
     * @param EntityManagerInterface $em
     * @param $dumpDirectory
     * @throws \Doctrine\DBAL\DBALException
     */
    private static function loadSetupDump(EntityManagerInterface $em, $dumpDirectory)
    {
        /** @var EntityManagerInterface $em */
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
     * @param EntityManagerInterface $em
     * @throws \Doctrine\DBAL\DBALException
     */
    public static function purgeDatabase(EntityManagerInterface $em)
    {
       $conn = $em->getConnection();
       $conn ->query('SET FOREIGN_KEY_CHECKS=0');
       $purger = new ORMPurger($em);
       $purger->setPurgeMode(ORMPurger::PURGE_MODE_TRUNCATE);
       $purger->purge();
       $conn->query('SET FOREIGN_KEY_CHECKS=1');
       $conn->query('UNLOCK TABLES');
    }



    /**
     * @throws \Doctrine\DBAL\DBALException
     */
   public function setup()
   {
       self::$kernel = self::bootKernel();
       /** @var EntityManagerInterface $em */
       $em = self::$kernel->getContainer()->get('doctrine.orm.model_entity_manager');
       $conn = $em->getConnection();
       $conn->query('TRUNCATE subevent');
       /** @noinspection SqlNoDataSourceInspection */
       $conn->query(self::ALTER_TABLE);
   }


    /**
     * @expectedException \App\Common\AppParseException
     * @expectedExceptionMessage  'Invalid Model' at (row:10,col:3) is an unrecognized value
     * @expectedExceptionCode \App\Common\AppExceptionCodes::UNRECOGNIZED_VALUE
     */
   public function test2010InvalidModel()
   {
        $parser = self::$yamlDbModelSubevent;
        $parser->parseEvents(__DIR__ . '/data-2010-invalid-model.yml');
   }


   /**
    * @expectedException  \App\Common\AppParseException
    * @expectedExceptionMessage 'Invalid Style' at (row:11,col:7) is an unrecognized value
    * @expectedExceptionCode \App\Common\AppExceptionCodes::UNRECOGNIZED_VALUE
    */

   public function test2020InvalidStyle()
   {
       $parser = self::$yamlDbModelSubevent;
       $parser->parseEvents(__DIR__ . '/data-2020-invalid-style.yml');
   }

   /**
    * @expectedException  \App\Common\AppParseException
    * @expectedExceptionMessage 'Invalid Substyle' at (row:12,col:9) is an unrecognized value in file:
    * @expectedExceptionCode \App\Common\AppExceptionCodes::UNRECOGNIZED_VALUE
    */
   public function test2030InvalidSubstyle()
   {
       $parser = self::$yamlDbModelSubevent;
       $parser->parseEvents(__DIR__ . '/data-2030-invalid-substyle.yml');
   }

   /**
    * @expectedException  \App\Common\AppParseException
    * @expectedExceptionMessage 'Invalid Proficiency' at (row:7,col:9) is an unrecognized value in file:
    * @expectedExceptionCode \App\Common\AppExceptionCodes::UNRECOGNIZED_VALUE
    */
   public function test2040InvalidProficiency()
   {
       $parser = self::$yamlDbModelSubevent;
       $parser->parseEvents(__DIR__ . '/data-2040-invalid-proficiency.yml');
   }

    /**
     * @expectedException  \App\Common\AppParseException
     * @expectedExceptionMessage 'Invalid Age' at (row:13,col:71) is an unrecognized value in file:
     * @expectedExceptionCode \App\Common\AppExceptionCodes::UNRECOGNIZED_VALUE
     */
   public function test2050InvalidAge()
   {
       $parser = self::$yamlDbModelSubevent;
       $parser->parseEvents(__DIR__ . '/data-2050-invalid-age.yml');
   }

    /**
     * @throws \App\Common\AppParseException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
   public function test2060ValidSequencing()
   {
       $parser = self::$yamlDbModelSubevent;
       $parser->parseEvents(__DIR__.'/model-09-subevent-sequence.yml');
       $em = self::$emModel;
       $repositoryEvent = $em->getRepository(Event::class);
       $repositorySubevent = $em->getRepository(Subevent::class);
       /** @var SubeventRepository $repository */
       $qbSubevent=$repositorySubevent->createQueryBuilder('subevent');
       $qbEvent = $repositoryEvent->createQueryBuilder('event');
       $subeventCount=
       $qbSubevent->select('count(subevent.id)')
                ->getQuery()->getSingleScalarResult();

       $eventCount =
       $qbEvent->select('count(event.id)')
                ->getQuery()->getSingleScalarResult();
       $this->assertGreaterThan($eventCount,$subeventCount);
   }

}