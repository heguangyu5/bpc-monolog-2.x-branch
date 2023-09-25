<?php declare(strict_types=1);

/*
 * This file is part of the Monolog package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Monolog;

use Monolog\Handler\TestHandler;
use Psr\Log\LogLevel;

class ErrorHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testRegister()
    {
        $logger = new Logger('test', [$handler = new TestHandler]);

        $this->assertInstanceOf(ErrorHandler::class, ErrorHandler::register($logger, false, false, false));
    }

    public function testHandleError()
    {
        $logger = new Logger('test', [$handler = new TestHandler]);
        $errHandler = new ErrorHandler($logger);

        $phpunitHandler = set_error_handler($prevHandler = function () {
        });

        //try {
            $errHandler->registerErrorHandler([], true);
            $prop = $errHandler->getPrivatePropertyValue('previousErrorHandler');
            $this->assertTrue(is_callable($prop));
            $this->assertSame($prevHandler, $prop);

            $resHandler = $errHandler->registerErrorHandler([E_USER_NOTICE => Logger::EMERGENCY], false);
            $this->assertSame($errHandler, $resHandler);
            trigger_error('Foo', E_USER_ERROR);
            $this->assertCount(1, $handler->getRecords());
            $this->assertTrue($handler->hasErrorRecords());
            trigger_error('Foo', E_USER_NOTICE);
            $this->assertCount(2, $handler->getRecords());
            $this->assertTrue($handler->hasEmergencyRecords());
        //} finally {
            // restore previous handler
            set_error_handler($phpunitHandler);
        //}
    }

    public function dataProviderTestFatalHandler()
    {
        return [
            [null, 10, str_repeat(' ', 1024 * 10), LogLevel::ALERT],
            [LogLevel::DEBUG, 15, str_repeat(' ', 1024 * 15), LogLevel::DEBUG],
        ];
    }
/*
    protected function getPrivatePropertyValue($instance, $property)
    {
        $ref = new \ReflectionClass(get_class($instance));
        $prop = $ref->getProperty($property);
        $prop->setAccessible(true);

        return $prop->getValue($instance);
    }
*/
    public function testFatalHandler(
        $level,
        $reservedMemorySize,
        $expectedReservedMemory,
        $expectedFatalLevel
    ) {
        $logger = new Logger('test', [$handler = new TestHandler]);
        $errHandler = new ErrorHandler($logger);
        $res = $errHandler->registerFatalHandler($level, $reservedMemorySize);

        $this->assertSame($res, $errHandler);
        $this->assertTrue($errHandler->getPrivatePropertyValue('hasFatalErrorHandler'));
        $this->assertEquals($expectedReservedMemory, $errHandler->getPrivatePropertyValue('reservedMemory'));
        $this->assertEquals($expectedFatalLevel, $errHandler->getPrivatePropertyValue('fatalLevel'));
    }

    public function testHandleException()
    {
        $logger = new Logger('test', [$handler = new TestHandler]);
        $errHandler = new ErrorHandler($logger);

        $resHandler = $errHandler->registerExceptionHandler($map = ['Monolog\CustomTestException' => LogLevel::DEBUG, 'TypeError' => LogLevel::NOTICE, 'Throwable' => LogLevel::WARNING], false);
        $this->assertSame($errHandler, $resHandler);

        $map['ParseError'] = LogLevel::CRITICAL;
        $prop = $errHandler->getPrivatePropertyValue('uncaughtExceptionLevelMap');
        $this->assertSame($map, $prop);

        $errHandler->registerExceptionHandler([], true);
        $prop = $errHandler->getPrivatePropertyValue('previousExceptionHandler');
        $this->assertTrue(is_callable($prop));
    }

    public function testCodeToString()
    {
        //$method = new \ReflectionMethod(ErrorHandler::class, 'codeToString');
        //$method->setAccessible(true);

        $this->assertEquals('E_ERROR', ErrorHandler::codeToStringForTest(E_ERROR));
        $this->assertEquals('E_WARNING', ErrorHandler::codeToStringForTest(E_WARNING));
        $this->assertEquals('E_PARSE', ErrorHandler::codeToStringForTest(E_PARSE));
        $this->assertEquals('E_NOTICE', ErrorHandler::codeToStringForTest(E_NOTICE));
        $this->assertEquals('E_CORE_ERROR', ErrorHandler::codeToStringForTest(E_CORE_ERROR));
        $this->assertEquals('E_CORE_WARNING', ErrorHandler::codeToStringForTest(E_CORE_WARNING));
        $this->assertEquals('E_COMPILE_ERROR', ErrorHandler::codeToStringForTest(E_COMPILE_ERROR));
        $this->assertEquals('E_COMPILE_WARNING', ErrorHandler::codeToStringForTest(E_COMPILE_WARNING));
        $this->assertEquals('E_USER_ERROR', ErrorHandler::codeToStringForTest(E_USER_ERROR));
        $this->assertEquals('E_USER_WARNING', ErrorHandler::codeToStringForTest(E_USER_WARNING));
        $this->assertEquals('E_USER_NOTICE', ErrorHandler::codeToStringForTest(E_USER_NOTICE));
        $this->assertEquals('E_STRICT', ErrorHandler::codeToStringForTest(E_STRICT));
        $this->assertEquals('E_RECOVERABLE_ERROR', ErrorHandler::codeToStringForTest(E_RECOVERABLE_ERROR));
        $this->assertEquals('E_DEPRECATED', ErrorHandler::codeToStringForTest(E_DEPRECATED));
        $this->assertEquals('E_USER_DEPRECATED', ErrorHandler::codeToStringForTest(E_USER_DEPRECATED));

        $this->assertEquals('Unknown PHP error', ErrorHandler::codeToStringForTest('RANDOM_TEXT'));
        $this->assertEquals('Unknown PHP error', ErrorHandler::codeToStringForTest(E_ALL));
    }
}

class CustomTestException extends \Exception
{
}
class CustomCustomException extends CustomTestException
{
}
