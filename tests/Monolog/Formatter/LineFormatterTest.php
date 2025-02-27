<?php declare(strict_types=1);

/*
 * This file is part of the Monolog package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Monolog\Formatter;

/**
 * @covers Monolog\Formatter\LineFormatter
 */
class LineFormatterTest extends \PHPUnit_Framework_TestCase
{
    public function testDefFormatWithString()
    {
        $formatter = new LineFormatter(null, 'Y-m-d');
        $message = $formatter->format([
            'level_name' => 'WARNING',
            'channel' => 'log',
            'context' => [],
            'message' => 'foo',
            'datetime' => new \DateTimeImmutable,
            'extra' => [],
        ]);
        $this->assertEquals('['.date('Y-m-d').'] log.WARNING: foo [] []'."\n", $message);
    }

    public function testDefFormatWithArrayContext()
    {
        $formatter = new LineFormatter(null, 'Y-m-d');
        $message = $formatter->format([
            'level_name' => 'ERROR',
            'channel' => 'meh',
            'message' => 'foo',
            'datetime' => new \DateTimeImmutable,
            'extra' => [],
            'context' => [
                'foo' => 'bar',
                'baz' => 'qux',
                'bool' => false,
                'null' => null,
            ],
        ]);
        $this->assertEquals('['.date('Y-m-d').'] meh.ERROR: foo {"foo":"bar","baz":"qux","bool":false,"null":null} []'."\n", $message);
    }

    public function testDefFormatExtras()
    {
        $formatter = new LineFormatter(null, 'Y-m-d');
        $message = $formatter->format([
            'level_name' => 'ERROR',
            'channel' => 'meh',
            'context' => [],
            'datetime' => new \DateTimeImmutable,
            'extra' => ['ip' => '127.0.0.1'],
            'message' => 'log',
        ]);
        $this->assertEquals('['.date('Y-m-d').'] meh.ERROR: log [] {"ip":"127.0.0.1"}'."\n", $message);
    }

    public function testFormatExtras()
    {
        $formatter = new LineFormatter("[%datetime%] %channel%.%level_name%: %message% %context% %extra.file% %extra%\n", 'Y-m-d');
        $message = $formatter->format([
            'level_name' => 'ERROR',
            'channel' => 'meh',
            'context' => [],
            'datetime' => new \DateTimeImmutable,
            'extra' => ['ip' => '127.0.0.1', 'file' => 'test'],
            'message' => 'log',
        ]);
        $this->assertEquals('['.date('Y-m-d').'] meh.ERROR: log [] test {"ip":"127.0.0.1"}'."\n", $message);
    }

    public function testContextAndExtraOptionallyNotShownIfEmpty()
    {
        $formatter = new LineFormatter(null, 'Y-m-d', false, true);
        $message = $formatter->format([
            'level_name' => 'ERROR',
            'channel' => 'meh',
            'context' => [],
            'datetime' => new \DateTimeImmutable,
            'extra' => [],
            'message' => 'log',
        ]);
        $this->assertEquals('['.date('Y-m-d').'] meh.ERROR: log  '."\n", $message);
    }

    public function testContextAndExtraReplacement()
    {
        $formatter = new LineFormatter('%context.foo% => %extra.foo%');
        $message = $formatter->format([
            'level_name' => 'ERROR',
            'channel' => 'meh',
            'context' => ['foo' => 'bar'],
            'datetime' => new \DateTimeImmutable,
            'extra' => ['foo' => 'xbar'],
            'message' => 'log',
        ]);
        $this->assertEquals('bar => xbar', $message);
    }

    public function testDefFormatWithObject()
    {
        $formatter = new LineFormatter(null, 'Y-m-d');
        $message = $formatter->format([
            'level_name' => 'ERROR',
            'channel' => 'meh',
            'context' => [],
            'datetime' => new \DateTimeImmutable,
            'extra' => ['foo' => new TestFoo, 'bar' => new TestBar, 'baz' => [], 'res' => fopen('php://memory', 'rb')],
            'message' => 'foobar',
        ]);

        $this->assertEquals('['.date('Y-m-d').'] meh.ERROR: foobar [] {"foo":{"Monolog\\\\Formatter\\\\TestFoo":{"foo":"fooValue"}},"bar":{"Monolog\\\\Formatter\\\\TestBar":"bar"},"baz":[],"res":"[resource(stream)]"}'."\n", $message);
    }

    public function testDefFormatWithException()
    {
        $formatter = new LineFormatter(null, 'Y-m-d');
        $message = $formatter->format([
            'level_name' => 'CRITICAL',
            'channel' => 'core',
            'context' => ['exception' => new \RuntimeException('Foo')],
            'datetime' => new \DateTimeImmutable,
            'extra' => [],
            'message' => 'foobar',
        ]);

        $path = str_replace('\\/', '/', json_encode(__FILE__));
if (defined('__BPC__')) { $lineDiff = 3; } else { $lineDiff = 8; }
        $this->assertEquals('['.date('Y-m-d').'] core.CRITICAL: foobar {"exception":"[object] (RuntimeException(code: 0): Foo at '.substr($path, 1, -1).':'.(__LINE__ - $lineDiff).')"} []'."\n", $message);
    }

    public function testDefFormatWithExceptionAndStacktrace()
    {
        $formatter = new LineFormatter(null, 'Y-m-d');
        $formatter->includeStacktraces();
        $message = $formatter->format([
            'level_name' => 'CRITICAL',
            'channel' => 'core',
            'context' => ['exception' => new \RuntimeException('Foo')],
            'datetime' => new \DateTimeImmutable,
            'extra' => [],
            'message' => 'foobar',
        ]);

        $path = str_replace('\\/', '/', json_encode(__FILE__));
if (defined('__BPC__')) { $lineDiff = 3; } else { $lineDiff = 8; }
        $this->assertRegexp('{^\['.date('Y-m-d').'] core\.CRITICAL: foobar \{"exception":"\[object] \(RuntimeException\(code: 0\): Foo at '.preg_quote(substr($path, 1, -1)).':'.(__LINE__ - $lineDiff).'\)\n\[stacktrace]\n#0}', $message);
    }

    public function testInlineLineBreaksRespectsEscapedBackslashes()
    {
        $formatter = new LineFormatter(null, 'Y-m-d');
        $formatter->allowInlineLineBreaks();

        self::assertSame('{"test":"foo'."\n".'bar\\\\name-with-n"}', $formatter->stringify(["test" => "foo\nbar\\name-with-n"]));
    }

    public function testDefFormatWithExceptionAndStacktraceParserFull()
    {
        $formatter = new LineFormatter(null, 'Y-m-d');
        $formatter->includeStacktraces(true, function ($line) {
            return $line;
        });

        $message = $formatter->format([
            'level_name' => 'CRITICAL',
            'channel' => 'core',
            'context' => ['exception' => new \RuntimeException('Foo')],
            'datetime' => new \DateTimeImmutable,
            'extra' => [],
            'message' => 'foobar',
        ]);

        $trace = explode('[stacktrace]', $message, 2)[1];

        if (defined('__BPC__')) {
            $this->assertStringContainsString('%3aecc091c03dd219d7b9bce63f76da6f', $trace); // TestCase.php
            $this->assertStringContainsString('%e535e702d53d4ea048131d2dfb724a6b', $trace); // TestResult.php
        } else {
            $this->assertStringContainsString('TestCase.php', $trace);
            $this->assertStringContainsString('TestResult.php', $trace);
        }
    }

    public function testDefFormatWithExceptionAndStacktraceParserCustom()
    {
        $formatter = new LineFormatter(null, 'Y-m-d');
        $formatter->includeStacktraces(true, function ($line) {
            if (defined('__BPC__')) {
                if (strpos($line, '%3aecc091c03dd219d7b9bce63f76da6f') === false) { // TestCase.php
                    return $line;
                }
            } else {
                if (strpos($line, 'TestCase.php') === false) {
                    return $line;
                }
            }
        });

        $message = $formatter->format([
            'level_name' => 'CRITICAL',
            'channel' => 'core',
            'context' => ['exception' => new \RuntimeException('Foo')],
            'datetime' => new \DateTimeImmutable,
            'extra' => [],
            'message' => 'foobar',
        ]);

        $trace = explode('[stacktrace]', $message, 2)[1];

        if (defined('__BPC__')) {
            $this->assertStringNotContainsString('%3aecc091c03dd219d7b9bce63f76da6f', $trace); // TestCase.php
            $this->assertStringContainsString('%e535e702d53d4ea048131d2dfb724a6b', $trace); // TestResult.php
        } else {
            $this->assertStringNotContainsString('TestCase.php', $trace);
            $this->assertStringContainsString('TestResult.php', $trace);
        }
    }

    public function testDefFormatWithExceptionAndStacktraceParserEmpty()
    {
        $formatter = new LineFormatter(null, 'Y-m-d');
        $formatter->includeStacktraces(true, function ($line) {
            return null;
        });

        $message = $formatter->format([
            'level_name' => 'CRITICAL',
            'channel' => 'core',
            'context' => ['exception' => new \RuntimeException('Foo')],
            'datetime' => new \DateTimeImmutable,
            'extra' => [],
            'message' => 'foobar',
        ]);

        $trace = explode('[stacktrace]', $message, 2)[1];

        $this->assertStringNotContainsString('#', $trace);
    }

    public function testDefFormatWithPreviousException()
    {
        $formatter = new LineFormatter(null, 'Y-m-d');
        $previous = new \LogicException('Wut?');
        $message = $formatter->format([
            'level_name' => 'CRITICAL',
            'channel' => 'core',
            'context' => ['exception' => new \RuntimeException('Foo', 0, $previous)],
            'datetime' => new \DateTimeImmutable,
            'extra' => [],
            'message' => 'foobar',
        ]);

        $path = str_replace('\\/', '/', json_encode(__FILE__));
if (defined('__BPC__')) { $lineDiff1 = 4; $lineDiff2 = 5; } else { $lineDiff1 = 8; $lineDiff2 = 12;}
        $this->assertEquals('['.date('Y-m-d').'] core.CRITICAL: foobar {"exception":"[object] (RuntimeException(code: 0): Foo at '.substr($path, 1, -1).':'.(__LINE__ - $lineDiff1).')\n[previous exception] [object] (LogicException(code: 0): Wut? at '.substr($path, 1, -1).':'.(__LINE__ - $lineDiff2).')"} []'."\n", $message);
    }
/*
    public function testDefFormatWithSoapFaultException()
    {
        if (!class_exists('SoapFault')) {
            $this->markTestSkipped('Requires the soap extension');
        }

        $formatter = new LineFormatter(null, 'Y-m-d');
        $message = $formatter->format([
            'level_name' => 'CRITICAL',
            'channel' => 'core',
            'context' => ['exception' => new \SoapFault('foo', 'bar', 'hello', 'world')],
            'datetime' => new \DateTimeImmutable,
            'extra' => [],
            'message' => 'foobar',
        ]);

        $path = str_replace('\\/', '/', json_encode(__FILE__));

        $this->assertEquals('['.date('Y-m-d').'] core.CRITICAL: foobar {"exception":"[object] (SoapFault(code: 0 faultcode: foo faultactor: hello detail: world): bar at '.substr($path, 1, -1).':'.(__LINE__ - 8).')"} []'."\n", $message);

        $message = $formatter->format([
            'level_name' => 'CRITICAL',
            'channel' => 'core',
            'context' => ['exception' => new \SoapFault('foo', 'bar', 'hello', (object) ['bar' => (object) ['biz' => 'baz'], 'foo' => 'world'])],
            'datetime' => new \DateTimeImmutable,
            'extra' => [],
            'message' => 'foobar',
        ]);

        $path = str_replace('\\/', '/', json_encode(__FILE__));

        $this->assertEquals('['.date('Y-m-d').'] core.CRITICAL: foobar {"exception":"[object] (SoapFault(code: 0 faultcode: foo faultactor: hello detail: {\"bar\":{\"biz\":\"baz\"},\"foo\":\"world\"}): bar at '.substr($path, 1, -1).':'.(__LINE__ - 8).')"} []'."\n", $message);
    }
*/
    public function testBatchFormat()
    {
        $formatter = new LineFormatter(null, 'Y-m-d');
        $message = $formatter->formatBatch([
            [
                'level_name' => 'CRITICAL',
                'channel' => 'test',
                'message' => 'bar',
                'context' => [],
                'datetime' => new \DateTimeImmutable,
                'extra' => [],
            ],
            [
                'level_name' => 'WARNING',
                'channel' => 'log',
                'message' => 'foo',
                'context' => [],
                'datetime' => new \DateTimeImmutable,
                'extra' => [],
            ],
        ]);
        $this->assertEquals('['.date('Y-m-d').'] test.CRITICAL: bar [] []'."\n".'['.date('Y-m-d').'] log.WARNING: foo [] []'."\n", $message);
    }

    public function testFormatShouldStripInlineLineBreaks()
    {
        $formatter = new LineFormatter(null, 'Y-m-d');
        $message = $formatter->format(
            [
                'message' => "foo\nbar",
                'context' => [],
                'extra' => [],
            ]
        );

        $this->assertRegExp('/foo bar/', $message);
    }

    public function testFormatShouldNotStripInlineLineBreaksWhenFlagIsSet()
    {
        $formatter = new LineFormatter(null, 'Y-m-d', true);
        $message = $formatter->format(
            [
                'message' => "foo\nbar",
                'context' => [],
                'extra' => [],
            ]
        );

        $this->assertRegExp('/foo\nbar/', $message);
    }
}

class TestFoo
{
    public $foo = 'fooValue';
}

class TestBar
{
    public function __toString()
    {
        return 'bar';
    }
}
