<?php
/**
 * Copyright (c) 2019. Mark Garber.  All rights reserved.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 3/8/19
 * Time: 4:38 PM
 */

namespace App\Tests\Common;


use App\Common\AppParseException;
use App\Common\YamlDbSales;
use App\Entity\Sales\Channel;
use App\Entity\Sales\Pricing;
use App\Kernel;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Dotenv\Dotenv;

class Code3000Sales extends KernelTestCase
{
    const EXPECTED_KEYS=['channel','competition','logo','venue','city','state','date','monitor','inventory','processor'];
    const INVENTORY_TAGS=['participant','extra','discount','penalty'];

    /** @var Kernel */
    protected static $kernel;

    /** @var YamlDbSales */
    private $sales;

    /** @var EntityManagerInterface */
    private $entityManager;


    public static function setUpBeforeClass()
    {
        (new Dotenv()) -> load(__DIR__.'/../../.env');
    }

    protected function setUp()
    {
        self::$kernel = self::bootKernel();
        $this->entityManager = self::$kernel->getContainer()->get('doctrine.orm.sales_entity_manager');
        $purger = new ORMPurger($this->entityManager,[]);
        $purger->setPurgeMode(ORMPurger::PURGE_MODE_TRUNCATE);
        $conn = $purger->getObjectManager()->getConnection();
        $conn->query('SET FOREIGN_KEY_CHECKS=0');
        $purger->purge();
        $conn->query('SET FOREIGN_KEY_CHECKS=1');
        $this->sales=new YamlDbSales($this->entityManager);

    }


    /**
     * @expectedException \App\Common\AppParseException
     * @expectedExceptionMessage Found 'no_channel' at (row:4,col:1) but expected
     * @expectedExceptionCode \App\Common\AppExceptionCodes::FOUND_BUT_EXPECTED
     * @throws AppParseException
     */
    public function test2010TopLevelKeyInvalid()
    {
        $this->sales->parseSales(__DIR__ . '/../../tests/Common/data-3010-sales-bad-key.yml');
    }

    /**
     * @expectedException \App\Common\AppParseException
     * @expectedExceptionMessage Missing [venue|city|state|date] between lines 1-37 in file
     * @throws AppParseException \App\Common\AppExceptionCodes::MISSING_KEYS
     */
    public function test2020TopLevelKeyMissing()
    {
        $this->sales->parseSales(__DIR__ . '/../../tests/Common/data-3020-sales-missing-keys.yml');
    }

    /**
     * @expectedException \App\Common\AppParseException
     * @expectedExceptionMessage File location not found at (row:6,col:7) in yaml file: data-3030-sales-missing-logo.yml.
     * @expectedExceptionCode \App\Common\AppExceptionCodes::FILE_NOT_FOUND
     * @throws AppParseException
     */
    public function test3030LogoMissing()
    {
        $this->sales->parseSales(__DIR__ . '/../../tests/Common/data-3030-sales-missing-logo.yml');
    }

    /**
     * @expectedException \App\Common\AppParseException
     * @expectedExceptionMessage Found 'no_start' at (row:10,col:8) but expected [start|finish] in file:
     * @expectedExceptionCode \App\Common\AppExceptionCodes::FOUND_BUT_EXPECTED
     * @throws AppParseException
     */
    public function test3040SalesStart()
    {
        $this->sales->parseSales(__DIR__ . '/../../tests/Common/data-3040-sales-start.yml');
    }


    /**
     * @expectedException \App\Common\AppParseException
     * @expectedExceptionMessage Found 'no_finish' at (row:10,col:26) but expected [start|finish] in file:
     * @expectedExceptionCode \App\Common\AppExceptionCodes::FOUND_BUT_EXPECTED
     * @throws AppParseException
     */
    public function test3050SalesFinish()
    {
        $this->sales->parseSales(__DIR__ . '/../../tests/Common/data-3050-sales-finish.yml');
    }


    /**
     * @expectedException \App\Common\AppParseException
     * @expectedExceptionMessage '20XX-09-15' at (row:10,col:34) is an invalid parameter in file
     * @expectedExceptionCode \App\Common\AppExceptionCodes::INVALID_PARAMETER
     * @throws AppParseException
     */
    public function test3060SalesDates()
    {
        $this->sales->parseSales(__DIR__ . '/../../tests/Common/data-3060-sales-dates.yml');
    }

    /**
     * @expectedException \App\Common\AppParseException
     * @expectedExceptionMessage '2018-09-14' at (row:10,col:34) in yaml file:
     * @expectedExceptionCode  \App\Common\AppExceptionCodes::BAD_DATE_ORDER
     * @throws AppParseException
     */
    public function test3070SalesDateRange()
    {
        $this->sales->parseSales(__DIR__ . '/../../tests/Common/data-3070-sales-date-range.yml');
    }

    /**
     * @expectedException \App\Common\AppParseException
     * @expectedExceptionMessage 'not_valid_email' at (row:12,col:18) is an invalid email in file
     * @expectedExceptionCode \App\Common\AppExceptionCodes::INVALID_EMAIL
     * @throws AppParseException
     */
    public function test3080SalesMonitor()
    {
        $this->sales->parseSales(__DIR__ . '/../../tests/Common/data-3080-sales-monitor.yml');
    }

    /**
     * @expectedException \App\Common\AppParseException
     * @expectedExceptionMessage '20XX-09-01' at (row:15,col:5) is an invalid parameter in file:
     * @expectedExceptionCode \App\Common\AppExceptionCodes::INVALID_PARAMETER
     * @throws AppParseException
     */
    public function test3090SalesInventoryDate()
    {
        $this->sales->parseSales(__DIR__ . '/../../tests/Common/data-3090-sales-inventory-date.yml');
    }

    /**
     * @expectedException \App\Common\AppParseException
     * @expectedExceptionMessage Found 'not_valid' at (row:21,col:9) but expected [participant|extra|discount|penalty]
     * @expectedExceptionCode \App\Common\AppExceptionCodes::FOUND_BUT_EXPECTED
     * @throws AppParseException
     */
    public function test3100SalesInventoryKeys()
    {
        $this->sales->parseSales(__DIR__ . '/../../tests/Common/data-3100-sales-inventory-keys.yml');
    }


    /**
     * @expectedException \App\Common\AppParseException
     * @expectedExceptionMessage 'XX' at (row:17,col:30) is an invalid parameter in file:
     * @expectedExceptionCode \App\Common\AppExceptionCodes::INVALID_PARAMETER
     * @throws AppParseException
     */
    public function test3110SalesInvalidPrice()
    {
        $this->sales->parseSales(__DIR__ . '/../../tests/Common/data-3110-sales-invalid-price.yml');
    }

    /**
     * @expectedException \App\Common\AppParseException
     * @expectedExceptionMessage Found 'invalid' at (row:43,col:9) but expected [test|prod] in file:
     * @expectedExceptionCode \App\Common\AppExceptionCodes::FOUND_BUT_EXPECTED
     * @throws AppParseException
     */
    public function test3130SalesProcessorsMode()
    {
        $this->sales->parseSales(__DIR__ . '/../../tests/Common/data-3130-sales-processors-mode.yml');
    }


    public function test3140SalesAll()
    {
        $repository = [];
        $this->sales->parseSales(__DIR__ . '/../../tests/Common/data-3140-sales-all.yml');
        $repository['channel'] = $this->entityManager->getRepository(Channel::class);
        $repository['pricing'] = $this->entityManager->getRepository((Pricing::class));
        $channel=$repository['channel']->findOneBy(['name'=>'Georgia DanceSport']);
        $pricing=$repository['pricing']->findBy(['channel'=>$channel]);
        $this->assertInstanceOf(Channel::class,$channel);
        $this->assertCount(2,$pricing);
        /** @var Pricing $pricingOnDate */
        foreach($pricing as $pricingOnDate) {
            $foundChannel=$pricingOnDate->getChannel();
            $this->assertEquals($channel,$foundChannel);
        }
    }



}