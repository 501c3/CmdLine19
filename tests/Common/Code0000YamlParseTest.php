<?php
/**
 * Copyright (c) 2018. Mark Garber.  All rights reserved.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 2/26/18
 * Time: 7:06 PM
 */

namespace Tests\Utils;

use App\Common\YamlPosition;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Yaml\Yaml;

class Code0000YamlParseTest extends KernelTestCase
{
    //    BY_TEN      = '0        10        20        30        40        50        60        70'
    //    RULER       = '1234567890123456789012345678901234567890123456789012345678901234567890123456789'
    const TEST_LINE_A = 'test-line-a: {substyle: [[dance], [dance], [dance], [dance], [dance]]}';
    const POSITION_A = 'R1C1: {R1C15: [[R1C27], [R1C36], [R1C45], [R1C54], [R1C63]]}';
    const TEST_LINE_B = '<test-line-b+: {abbr: tb+, order: 3}';
    const POSITION_B  = 'R1C1: {R1C17: R1C23, R1C28: R1C35}';


    /**
     * @expectedException  \App\Common\AppParseException
     * @expectedExceptionMessage Missing file DummyFile.txt.
     * @expectedExceptionCode \App\Common\AppExceptionCodes::MISSING_FILE
     * @throws \Exception
     */
    public function test0010EmptyString()
    {
        YamlPosition::yamlAddPosition( "DummyFile.txt" );
    }

    /**
     * @throws \Exception
     */
    public function test0010Parse()
    {
        $actual = YamlPosition::strToPos('R10C25');
        $this->assertEquals(10, $actual['row']);
        $this->assertEquals(25, $actual['col']);
    }

    /**
     *
     */
     public function test0020LineA()
    {
        $result = YamlPosition::position( 1, self::TEST_LINE_A );
        $this->assertSame( self::POSITION_A, $result );
    }


    public function test0030LineB()
    {
        $result = YamlPosition::position( 1, self::TEST_LINE_B );
        $this->assertSame(self::POSITION_B, $result);
    }

    /**
     * @throws \Exception
     */
    public function test0040PositionCorrect()
    {

        $file= __DIR__ . '/data-0040-position-isolate.yml';
        $actual = YamlPosition::yamlAddPosition($file);
        $expectedIsolatedData = Yaml::parseFile($file);
        $actualIsolatedData = YamlPosition::isolate($actual);
        $this->assertEquals($expectedIsolatedData, $actualIsolatedData);
    }

    /**
     * @throws AppParseException
     */
    public function test0050PositionKey()
    {
        $testArray = ['key1', 'key2', 'key3'];
        $actualKey1TrueInCollection = YamlPosition::inCollection('key1', $testArray);
        $actualKey3TrueInCollection = YamlPosition::inCollection('key3', $testArray);
        $actualKey99FalseInCollection = YamlPosition::inCollection('key99', $testArray);
        $this->assertTrue($actualKey1TrueInCollection);
        $this->assertTrue($actualKey3TrueInCollection);
        $this->assertFalse($actualKey99FalseInCollection);
    }

    /**
     * @expectedException \App\Common\AppParseException
     * @expectedExceptionMessage Found 'key99' at (row:10,col:10) but expected [key1|key2|key3] in file:
     * @expectedExceptionCode \App\Common\AppExceptionCodes::FOUND_BUT_EXPECTED
     * @throws AppParseException
     */
    public function test0060PositionInCollection()
    {
        $testArray = ['key1', 'key2', 'key3'];
        $actualKey1TrueInCollection = YamlPosition::inCollection('key1|R2C1', $testArray);
        $actualKey3TrueInCollection = YamlPosition::inCollection('key3|R4C1', $testArray);
        YamlPosition::inCollection('key99|R10C10', $testArray); #Throws exception
        $this->assertTrue($actualKey1TrueInCollection);
        $this->assertTrue($actualKey3TrueInCollection);
    }

    /**
     * @throws \Exception
     */
    public function test0070PositionIsolate()
    {
        $file= __DIR__ . '/data-0040-position-isolate.yml';
        $string = file_get_contents($file);
        $expected = Yaml::parse($string);
        $dataPosition = YamlPosition::yamlAddPosition($file);
        $actual = YamlPosition::isolate($dataPosition);
        $this->assertEquals($expected,$actual);
    }
 }