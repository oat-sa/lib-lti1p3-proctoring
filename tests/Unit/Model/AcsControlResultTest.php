<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace OAT\Library\Lti1p3Proctoring\Tests\Unit\Model;

use InvalidArgumentException;
use OAT\Library\Lti1p3Proctoring\Model\AcsControlResult;
use OAT\Library\Lti1p3Proctoring\Model\AcsControlResultInterface;
use PHPUnit\Framework\TestCase;

class AcsControlResultTest extends TestCase
{
    /** @var AcsControlResultInterface */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new AcsControlResult(AcsControlResultInterface::STATUS_RUNNING);
    }

    public function testConstructorDefaults(): void
    {
        $this->assertEquals(AcsControlResultInterface::STATUS_RUNNING, $this->subject->getStatus());
        $this->assertNull($this->subject->getExtraTime());
    }

    public function testSetStatusSuccess(): void
    {
        $this->subject->setStatus(AcsControlResultInterface::STATUS_PAUSED);

        $this->assertEquals(AcsControlResultInterface::STATUS_PAUSED, $this->subject->getStatus());
    }

    public function testSetStatusFailure(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Control result status invalid is not supported');

        $this->subject->setStatus('invalid');
    }

    public function testSetExtraTime(): void
    {
        $this->subject->setExtraTime(10);

        $this->assertEquals(10, $this->subject->getExtraTime());
    }

    public function testJsonSerialize(): void
    {
        $this->subject->setExtraTime(10);

        $this->assertEquals(
            [
                'status' => AcsControlResultInterface::STATUS_RUNNING,
                'extra_time' => 10,
            ],
            $this->subject->jsonSerialize()
        );
    }
}
