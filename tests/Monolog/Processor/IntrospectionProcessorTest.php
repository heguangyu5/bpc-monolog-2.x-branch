<?php declare(strict_types=1);

/*
 * This file is part of the Monolog package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Acme;

class Tester
{
    public function test($handler, $record)
    {
        $handler->handle($record);
    }
}

function tester($handler, $record)
{
    $handler->handle($record);
}

namespace Monolog\Processor;

use Monolog\Logger;
use Monolog\Test\TestCase;
use Monolog\Handler\TestHandler;

class IntrospectionProcessorTest extends TestCase
{
    public function getHandler()
    {
        $processor = new IntrospectionProcessor();
        $handler = new TestHandler();
        $handler->pushProcessor($processor);

        return $handler;
    }

    public function testProcessorFromClass()
    {
        $handler = $this->getHandler();
        $tester = new \Acme\Tester;
        $tester->test($handler, $this->getRecord());
        list($record) = $handler->getRecords();
        $this->assertEquals(__FILE__, $record['extra']['file']);
        if (defined('__BPC__')) {
            $line = 7;
        } else {
            $line = 18;
        }
        $this->assertEquals($line, $record['extra']['line']);
        $this->assertEquals('Acme\Tester', $record['extra']['class']);
        $this->assertEquals('test', $record['extra']['function']);
    }

    public function testProcessorFromFunc()
    {
        $handler = $this->getHandler();
        \Acme\tester($handler, $this->getRecord());
        list($record) = $handler->getRecords();
        $this->assertEquals(__FILE__, $record['extra']['file']);
        if (defined('__BPC__')) {
            $line = 12;
            $function = '%d7493a13e131897673ef6634806d5c2a';
        } else {
            $line = 24;
            $function = 'Acme\tester';
        }
        $this->assertEquals($line, $record['extra']['line']);
        $this->assertEquals(null, $record['extra']['class']);
        $this->assertEquals($function, $record['extra']['function']);
    }

    public function testLevelTooLow()
    {
        $input = [
            'level' => Logger::DEBUG,
            'extra' => [],
        ];

        $expected = $input;

        $processor = new IntrospectionProcessor(Logger::CRITICAL);
        $actual = $processor($input);

        $this->assertEquals($expected, $actual);
    }

    public function testLevelEqual()
    {
        $input = [
            'level' => Logger::CRITICAL,
            'extra' => [],
        ];

        $expected = $input;
        $expected['extra'] = [
            'file' => null,
            'line' => null,
            'class' => 'PHPUnit_Framework_TestCase',
            'function' => 'runTest',
            'callType' => '->',
        ];

        $processor = new IntrospectionProcessor(Logger::CRITICAL);
        $actual = $processor($input);

        $this->assertEquals($expected, $actual);
    }

    public function testLevelHigher()
    {
        $input = [
            'level' => Logger::EMERGENCY,
            'extra' => [],
        ];

        $expected = $input;
        $expected['extra'] = [
            'file' => null,
            'line' => null,
            'class' => 'PHPUnit_Framework_TestCase',
            'function' => 'runTest',
            'callType' => '->',
        ];

        $processor = new IntrospectionProcessor(Logger::CRITICAL);
        $actual = $processor($input);

        $this->assertEquals($expected, $actual);
    }
}
