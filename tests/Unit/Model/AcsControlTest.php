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

use Carbon\Carbon;
use Carbon\CarbonInterface;
use InvalidArgumentException;
use OAT\Library\Lti1p3Core\Resource\LtiResourceLink\LtiResourceLink;
use OAT\Library\Lti1p3Core\Resource\LtiResourceLink\LtiResourceLinkInterface;
use OAT\Library\Lti1p3Proctoring\Model\AcsControl;
use OAT\Library\Lti1p3Proctoring\Model\AcsControlInterface;
use PHPUnit\Framework\TestCase;

class AcsControlTest extends TestCase
{
    /** @var CarbonInterface */
    private $time;

    /** @var LtiResourceLinkInterface */
    private $resourceLink;

    /** @var AcsControlInterface */
    private $subject;

    protected function setUp(): void
    {
        $this->time = Carbon::now();
        $this->resourceLink = new LtiResourceLink('resourceLinkIdentifier');

        $this->subject = new AcsControl(
            $this->resourceLink,
            'userIdentifier',
            AcsControlInterface::ACTION_UPDATE,
            $this->time
        );
    }

    public function testConstructorDefaults(): void
    {
        $this->assertEquals($this->resourceLink, $this->subject->getResourceLink());
        $this->assertEquals('userIdentifier', $this->subject->getUserIdentifier());
        $this->assertEquals(AcsControlInterface::ACTION_UPDATE, $this->subject->getAction());
        $this->assertEquals($this->time, $this->subject->getIncidentTime());
        $this->assertEquals(1, $this->subject->getAttemptNumber());
        $this->assertNull($this->subject->getIssuerIdentifier());
        $this->assertNull($this->subject->getExtraTime());
        $this->assertNull($this->subject->getIncidentSeverity());
        $this->assertNull($this->subject->getReasonCode());
        $this->assertNull($this->subject->getReasonMessage());
    }

    public function testSetResourceLink(): void
    {
        $resourceLink = new LtiResourceLink('otherResourceLinkIdentifier');

        $this->subject->setResourceLink($resourceLink);

        $this->assertEquals('otherResourceLinkIdentifier', $this->subject->getResourceLink()->getIdentifier());
    }

    public function testSetUserIdentifier(): void
    {
        $this->subject->setUserIdentifier('otherUserIdentifier');

        $this->assertEquals('otherUserIdentifier', $this->subject->getUserIdentifier());
    }

    public function testSetActionSuccess(): void
    {
        $this->subject->setAction(AcsControlInterface::ACTION_PAUSE);

        $this->assertEquals(AcsControlInterface::ACTION_PAUSE, $this->subject->getAction());
    }

    public function testSetActionFailure(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Control action invalid is not supported');

        $this->subject->setAction('invalid');
    }

    public function testSetIncidentTime(): void
    {
        $otherTime = $this->time->addSeconds(100);

        $this->subject->setIncidentTime($otherTime);

        $this->assertEquals($otherTime, $this->subject->getIncidentTime());
    }

    public function testSetAttemptNumber(): void
    {
        $this->subject->setAttemptNumber(2);

        $this->assertEquals(2, $this->subject->getAttemptNumber());
    }

    public function testSetIssuerIdentifier(): void
    {
        $this->subject->setIssuerIdentifier('issuerIdentifier');

        $this->assertEquals('issuerIdentifier', $this->subject->getIssuerIdentifier());
    }

    public function testSetExtraTime(): void
    {
        $this->subject->setExtraTime(10);

        $this->assertEquals(10, $this->subject->getExtraTime());
    }

    public function testSetIncidentSeverity(): void
    {
        $this->subject->setIncidentSeverity(0.5);

        $this->assertEquals(0.5, $this->subject->getIncidentSeverity());
    }

    public function testSetReasonCode(): void
    {
        $this->subject->setReasonCode('code');

        $this->assertEquals('code', $this->subject->getReasonCode());
    }

    public function testSetReasonMessage(): void
    {
        $this->subject->setReasonMessage('message');

        $this->assertEquals('message', $this->subject->getReasonMessage());
    }

    public function testJsonSerialize(): void
    {
        $this->subject
            ->setIssuerIdentifier('issuerIdentifier')
            ->setExtraTime(10)
            ->setIncidentSeverity(0.5)
            ->setReasonCode('code')
            ->setReasonMessage('message');

        $this->assertEquals(
            [
                'user' => [
                    'iss' => 'issuerIdentifier',
                    'sub' => 'userIdentifier',
                ],
                'resource_link' => [
                    'id' => $this->resourceLink->getIdentifier(),
                    'title' => $this->resourceLink->getTitle(),
                    'description' => $this->resourceLink->getText(),
                ],
                'attempt_number' => 1,
                'action' => AcsControlInterface::ACTION_UPDATE,
                'extra_time' => 10,
                'incident_time' => $this->time->format(DATE_ATOM),
                'incident_severity' => 0.5,
                'reason_code' => 'code',
                'reason_msg' => 'message',
            ],
            $this->subject->jsonSerialize()
        );
    }
}
