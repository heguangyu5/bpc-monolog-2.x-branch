<?php declare(strict_types=1);

/*
 * This file is part of the Monolog package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Monolog\Handler;

use Monolog\Test\TestCase;

/**
 * @author Alexey Karapetov <alexey@karapetov.com>
 */
class HandlerWrapperTest extends TestCase
{
    /**
     * @var HandlerWrapper
     */
    private $wrapper;

    private $handler;

    public function setUp(): void
    {
        parent::setUp();
        $this->handler = $this->createMock('Monolog\\Handler\\HandlerInterface');
        $this->wrapper = new HandlerWrapper($this->handler);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        unset($this->wrapper);
    }

    /**
     * @return array
     */
    public function dataProviderTestIsHandling()
    {
        return [
            [true],
            [false],
        ];
    }

    /**
     * @param $result
     */
    public function testIsHandling($result)
    {
        $record = $this->getRecord();
        $this->handler->expects($this->once())
            ->method('isHandling')
            ->with($record)
            ->willReturn($result);

        $this->assertEquals($result, $this->wrapper->isHandling($record));
    }

    public function dataProviderTestHandle()
    {
        return [
            [true],
            [false],
        ];
    }

    /**
     * @param $result
     */
    public function testHandle($result)
    {
        $record = $this->getRecord();
        $this->handler->expects($this->once())
            ->method('handle')
            ->with($record)
            ->willReturn($result);

        $this->assertEquals($result, $this->wrapper->handle($record));
    }

    public function dataProviderTestHandleBatch()
    {
        return [
            [true],
            [false],
        ];
    }

    /**
     * @param $result
     */
    public function testHandleBatch($result)
    {
        $records = $this->getMultipleRecords();
        $this->handler->expects($this->once())
            ->method('handleBatch')
            ->with($records);

        $this->wrapper->handleBatch($records);
    }
}
