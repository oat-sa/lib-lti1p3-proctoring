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

use Carbon\Carbon;
use Carbon\CarbonInterface;
use OAT\Library\Lti1p3Core\Exception\LtiExceptionInterface;
use OAT\Library\Lti1p3Core\Resource\LtiResourceLink\LtiResourceLink;
use OAT\Library\Lti1p3Proctoring\Model\AcsControl;
use OAT\Library\Lti1p3Proctoring\Model\AcsControlInterface;
use OAT\Library\Lti1p3Proctoring\Serializer\AcsControlSerializer;
use OAT\Library\Lti1p3Proctoring\Serializer\AcsControlSerializerInterface;
use PHPUnit\Framework\TestCase;

class AcsControlSerializerTest extends TestCase
{
    /** @var CarbonInterface */
    private $time;

    /** @var AcsControlInterface */
    private $control;

    /** @var AcsControlSerializerInterface */
    private $subject;

    protected function setUp(): void
    {
        $this->time = Carbon::now();

        $this->control = new AcsControl(
            new LtiResourceLink(
                'resourceLinkIdentifier',
                [
                    'title' => 'resourceLinkTitle',
                    'text' => 'resourceLinkDescription',
                ]
            ),
            'userIdentifier',
            AcsControlInterface::ACTION_UPDATE,
            $this->time,
            1,
            'issuerIdentifier'
        );

        $this->subject = new AcsControlSerializer();
    }

    public function testMinimalControlSerializeSuccess(): void
    {
        $this->assertEquals(
            [
                'user' => [
                    'iss' => 'issuerIdentifier',
                    'sub' => 'userIdentifier',
                ],
                'resource_link' => [
                    'id' => 'resourceLinkIdentifier',
                    'title' => 'resourceLinkTitle',
                    'description' => 'resourceLinkDescription',
                ],
                'attempt_number' => 1,
                'action' => AcsControlInterface::ACTION_UPDATE,
                'incident_time' => $this->time->format(DATE_ATOM),
            ],
            json_decode($this->subject->serialize($this->control), true)
        );
    }

    public function testCompleteControlSerializeSuccess(): void
    {
        $this->control
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
                    'id' => 'resourceLinkIdentifier',
                    'title' => 'resourceLinkTitle',
                    'description' => 'resourceLinkDescription',
                ],
                'attempt_number' => 1,
                'action' => AcsControlInterface::ACTION_UPDATE,
                'incident_time' => $this->time->format(DATE_ATOM),
                'extra_time' => 10,
                'incident_severity' => 0.5,
                'reason_code' => 'code',
                'reason_msg' => 'message',
            ],
            json_decode($this->subject->serialize($this->control), true)
        );
    }

    public function testMinimalControlDeserializeSuccess(): void
    {
        $data = [
            'user' => [
                'iss' => 'issuerIdentifier',
                'sub' => 'userIdentifier',
            ],
            'resource_link' => [
                'id' => 'resourceLinkIdentifier',
                'title' => 'resourceLinkTitle',
                'description' => 'resourceLinkDescription',
            ],
            'attempt_number' => 1,
            'action' => AcsControlInterface::ACTION_UPDATE,
            'incident_time' => $this->time->format(DATE_ATOM),
        ];

        $this->assertEquals(
            $data,
            $this->subject->deserialize(json_encode($data))->jsonSerialize()
        );
    }

    public function testCompleteControlDeserializeSuccess(): void
    {
        $data = [
            'user' => [
                'iss' => 'issuerIdentifier',
                'sub' => 'userIdentifier',
            ],
            'resource_link' => [
                'id' => 'resourceLinkIdentifier',
                'title' => 'resourceLinkTitle',
                'description' => 'resourceLinkDescription',
            ],
            'attempt_number' => 1,
            'action' => AcsControlInterface::ACTION_UPDATE,
            'incident_time' => $this->time->format(DATE_ATOM),
            'extra_time' => 10,
            'incident_severity' => 0.5,
            'reason_code' => 'code',
            'reason_msg' => 'message',
        ];

        $this->assertEquals(
            $data,
            $this->subject->deserialize(json_encode($data))->jsonSerialize()
        );
    }

    public function testDeserializeFailure(): void
    {
        $this->expectException(LtiExceptionInterface::class);
        $this->expectExceptionMessage('Error during ACS control deserialization: Syntax error');

        $this->subject->deserialize('{');
    }
}
