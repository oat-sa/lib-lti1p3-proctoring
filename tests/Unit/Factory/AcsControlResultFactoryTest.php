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

namespace OAT\Library\Lti1p3Proctoring\Tests\Unit\Factory;

use OAT\Library\Lti1p3Core\Exception\LtiException;
use OAT\Library\Lti1p3Proctoring\Factory\AcsControlFactoryInterface;
use OAT\Library\Lti1p3Proctoring\Factory\AcsControlResultFactory;
use OAT\Library\Lti1p3Proctoring\Model\AcsControlResultInterface;
use PHPUnit\Framework\TestCase;

class AcsControlResultFactoryTest extends TestCase
{
    /** @var AcsControlFactoryInterface */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new AcsControlResultFactory();
    }

    public function testCreateSuccess(): void
    {
        $result = $this->subject->create(
            [
                'status' => AcsControlResultInterface::STATUS_RUNNING,
                'extra_time' => 10,
            ]
        );

        $this->assertInstanceOf(AcsControlResultInterface::class, $result);

        $this->assertEquals(AcsControlResultInterface::STATUS_RUNNING, $result->getStatus());
        $this->assertEquals(10, $result->getExtraTime());
    }

    public function testCreateFailureOnMissingStatus(): void
    {
        $this->expectException(LtiException::class);
        $this->expectExceptionMessage(sprintf('Cannot create ACS control result: Invalid status'));

        $this->subject->create([]);
    }

    public function testCreateFailureOnInvalidStatus(): void
    {
        $this->expectException(LtiException::class);
        $this->expectExceptionMessage(sprintf('Cannot create ACS control result: Invalid status'));

        $this->subject->create(
            [
                'status' => 'invalid',
            ]
        );
    }
}
