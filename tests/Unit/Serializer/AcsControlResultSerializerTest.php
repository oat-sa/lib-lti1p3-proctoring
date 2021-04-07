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

namespace OAT\Library\Lti1p3Proctoring\Tests\Unit\Serializer;

use OAT\Library\Lti1p3Core\Exception\LtiExceptionInterface;
use OAT\Library\Lti1p3Proctoring\Model\AcsControlResult;
use OAT\Library\Lti1p3Proctoring\Model\AcsControlResultInterface;
use OAT\Library\Lti1p3Proctoring\Serializer\AcsControlResultSerializer;
use OAT\Library\Lti1p3Proctoring\Serializer\AcsControlResultSerializerInterface;
use PHPUnit\Framework\TestCase;

class AcsControlResultSerializerTest extends TestCase
{
    /** @var AcsControlResultInterface */
    private $controlResult;

    /** @var AcsControlResultSerializerInterface */
    private $subject;

    protected function setUp(): void
    {
        $this->controlResult = new AcsControlResult(AcsControlResultInterface::STATUS_RUNNING);

        $this->subject = new AcsControlResultSerializer();
    }

    public function testMinimalControlResultSerializeSuccess(): void
    {
        $this->assertEquals(
            [
                'status' => AcsControlResultInterface::STATUS_RUNNING,
            ],
            json_decode($this->subject->serialize($this->controlResult), true)
        );
    }

    public function testCompleteControlSerializeSuccess(): void
    {
        $this->controlResult->setExtraTime(10);

        $this->assertEquals(
            [
                'status' => AcsControlResultInterface::STATUS_RUNNING,
                'extra_time' => 10,
            ],
            json_decode($this->subject->serialize($this->controlResult), true)
        );
    }

    public function testMinimalControlDeserializeSuccess(): void
    {
        $data = [
            'status' => AcsControlResultInterface::STATUS_RUNNING,
        ];

        $this->assertEquals(
            AcsControlResultInterface::STATUS_RUNNING,
            $this->subject->deserialize(json_encode($data))->getStatus()
        );
    }

    public function testCompleteControlDeserializeSuccess(): void
    {
        $data = [
            'status' => AcsControlResultInterface::STATUS_RUNNING,
            'extra_time' => 10,
        ];

        $result = $this->subject->deserialize(json_encode($data));

        $this->assertEquals(AcsControlResultInterface::STATUS_RUNNING, $result->getStatus());
        $this->assertEquals(10, $result->getExtraTime());
    }

    public function testDeserializeFailure(): void
    {
        $this->expectException(LtiExceptionInterface::class);
        $this->expectExceptionMessage('Error during ACS control result deserialization: Syntax error');

        $this->subject->deserialize('{');
    }
}
