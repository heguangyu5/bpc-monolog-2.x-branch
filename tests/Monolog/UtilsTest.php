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

class UtilsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $expected
     * @param object $object
     */
    public function testGetClass($expected, $object)
    {
        $this->assertSame($expected, Utils::getClass($object));
    }

    public function dataProviderTestGetClass()
    {
        return [
            ['stdClass', new \stdClass()],
            //['class@anonymous', new class {}],
            //['stdClass@anonymous', new class extends \stdClass {}],
        ];
    }

    /**
     * @param string $expected
     * @param string $input
     */
    public function testCanonicalizePath($expected, $input)
    {
        $this->assertSame($expected, Utils::canonicalizePath($input));
    }

    public function dataProviderTestCanonicalizePath()
    {
        return array(
            array('/foo/bar', '/foo/bar'),
            array('file://'.getcwd().'/bla', 'file://bla'),
            array(getcwd().'/bla', 'bla'),
            array(getcwd().'/./bla', './bla'),
            array('file:///foo/bar', 'file:///foo/bar'),
            array('any://foo', 'any://foo'),
            array('\\\\network\path', '\\\\network\path'),
        );
    }

    /**
     * @param int    $code
     * @param string $msg
     */
    public function testHandleJsonErrorFailure($code, $msg)
    {
        $this->expectException('RuntimeException', $msg);
        Utils::handleJsonError($code, 'faked');
    }

    public function dataProviderTestHandleJsonErrorFailure()
    {
        return [
            'depth' => [JSON_ERROR_DEPTH, 'Maximum stack depth exceeded'],
            'state' => [JSON_ERROR_STATE_MISMATCH, 'Underflow or the modes mismatch'],
            'ctrl' => [JSON_ERROR_CTRL_CHAR, 'Unexpected control character found'],
            'default' => [-1, 'Unknown error'],
        ];
    }

    /**
     * @param mixed $in     Input
     * @param mixed $expect Expected output
     * @covers Monolog\Formatter\NormalizerFormatter::detectAndCleanUtf8
     */
    public function testDetectAndCleanUtf8($in, $expect)
    {
        //$reflMethod = new \ReflectionMethod(Utils::class, 'detectAndCleanUtf8');
        //$reflMethod->setAccessible(true);
        //$args = [&$in];
        //$reflMethod->invokeArgs(null, $args);
        Utils::detectAndCleanUtf8ForTest($in);
        $this->assertSame($expect, $in);
    }

    public function dataProviderTestDetectAndCleanUtf8()
    {
        $obj = new \stdClass;

        return [
            'null' => [null, null],
            'int' => [123, 123],
            'float' => [123.45, 123.45],
            'bool false' => [false, false],
            'bool true' => [true, true],
            'ascii string' => ['abcdef', 'abcdef'],
            'latin9 string' => ["\xB1\x31\xA4\xA6\xA8\xB4\xB8\xBC\xBD\xBE\xFF", '±1€ŠšŽžŒœŸÿ'],
            'unicode string' => ['¤¦¨´¸¼½¾€ŠšŽžŒœŸ', '¤¦¨´¸¼½¾€ŠšŽžŒœŸ'],
            'empty array' => [[], []],
            'array' => [['abcdef'], ['abcdef']],
            'object' => [$obj, $obj],
        ];
    }

    /**
     * @param int $code
     * @param string $msg
     */
    public function testPcreLastErrorMessage($code, $msg)
    {
        if (PHP_VERSION_ID >= 80000) {
            $this->assertSame('No error', Utils::pcreLastErrorMessage($code));
            return;
        }

        $this->assertEquals($msg, Utils::pcreLastErrorMessage($code));
    }

    /**
     * @return array[]
     */
    public function dataProviderTestPcreLastErrorMessage()
    {
        return [
            [0, 'PREG_NO_ERROR'],
            [1, 'PREG_INTERNAL_ERROR'],
            [2, 'PREG_BACKTRACK_LIMIT_ERROR'],
            [3, 'PREG_RECURSION_LIMIT_ERROR'],
            [4, 'PREG_BAD_UTF8_ERROR'],
            [5, 'PREG_BAD_UTF8_OFFSET_ERROR'],
            [6, 'PREG_JIT_STACKLIMIT_ERROR'],
            [-1, 'UNDEFINED_ERROR'],
        ];
    }

    public function dataProviderTestExpandIniShorthandBytes()
    {
        return [
            ['1', 1],
            ['2', 2],
            ['2.5', 2],
            ['2.9', 2],
            ['1B', false],
            ['1X', false],
            ['1K', 1024],
            ['1 K', 1024],
            ['   5 M  ', 5*1024*1024],
            ['1G', 1073741824],
            ['', false],
            [null, false],
            ['A', false],
            ['AA', false],
            ['B', false],
            ['BB', false],
            ['G', false],
            ['GG', false],
            ['-1', -1],
            ['-123', -123],
            ['-1A', -1],
            ['-1B', -1],
            ['-123G', -123],
            ['-B', false],
            ['-A', false],
            ['-', false],
            [true, false],
            [false, false],
        ];
    }

    /**
     * @param mixed $input
     * @param int|false $expected
     */
    public function testExpandIniShorthandBytes($input, $expected)
    {
        $result = Utils::expandIniShorthandBytes($input);
        $this->assertEquals($expected, $result);
    }
}
