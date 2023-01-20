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

use Carbon\Carbon;
use OAT\Library\Lti1p3Core\Exception\LtiException;
use OAT\Library\Lti1p3Proctoring\Factory\AcsControlFactory;
use OAT\Library\Lti1p3Proctoring\Factory\AcsControlFactoryInterface;
use OAT\Library\Lti1p3Proctoring\Model\AcsControlInterface;
use PHPUnit\Framework\TestCase;

class AcsControlFactoryTest extends TestCase
{
    /** @var AcsControlFactoryInterface */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new AcsControlFactory();
    }

    public function testCreateSuccess(): void
    {
        $date = Carbon::now();

        $result = $this->subject->create(
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
                'extra_time' => 10,
                'incident_time' => $date->format(DATE_ATOM),
                'incident_severity' => 0.5,
                'reason_code' => 'code',
                'reason_msg' => 'message',
            ]
        );

        $this->assertInstanceOf(AcsControlInterface::class, $result);

        $this->assertEquals('resourceLinkIdentifier', $result->getResourceLink()->getIdentifier());
        $this->assertEquals('resourceLinkTitle', $result->getResourceLink()->getTitle());
        $this->assertEquals('resourceLinkDescription', $result->getResourceLink()->getText());
        $this->assertEquals('issuerIdentifier', $result->getIssuerIdentifier());
        $this->assertEquals('userIdentifier', $result->getUserIdentifier());
        $this->assertEquals(1, $result->getAttemptNumber());
        $this->assertEquals(AcsControlInterface::ACTION_UPDATE, $result->getAction());
        $this->assertEquals(10, $result->getExtraTime());
        $this->assertEquals($date->format(DATE_ATOM), $result->getIncidentTime()->format(DATE_ATOM));
        $this->assertEquals(0.5, $result->getIncidentSeverity());
        $this->assertEquals('code', $result->getReasonCode());
        $this->assertEquals('message', $result->getReasonMessage());
    }

    public function testCreateSuccessWithZeroExtraTimeAndIncidentSeverity(): void
    {
        $date = Carbon::now();

        $result = $this->subject->create(
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
                'extra_time' => 0,
                'incident_time' => $date->format(DATE_ATOM),
                'incident_severity' => 0,
                'reason_code' => 'code',
                'reason_msg' => 'message',
            ]
        );

        $this->assertInstanceOf(AcsControlInterface::class, $result);

        $this->assertEquals('resourceLinkIdentifier', $result->getResourceLink()->getIdentifier());
        $this->assertEquals('resourceLinkTitle', $result->getResourceLink()->getTitle());
        $this->assertEquals('resourceLinkDescription', $result->getResourceLink()->getText());
        $this->assertEquals('issuerIdentifier', $result->getIssuerIdentifier());
        $this->assertEquals('userIdentifier', $result->getUserIdentifier());
        $this->assertEquals(1, $result->getAttemptNumber());
        $this->assertEquals(AcsControlInterface::ACTION_UPDATE, $result->getAction());
        $this->assertEquals(0, $result->getExtraTime());
        $this->assertEquals($date->format(DATE_ATOM), $result->getIncidentTime()->format(DATE_ATOM));
        $this->assertEquals(0, $result->getIncidentSeverity());
        $this->assertEquals('code', $result->getReasonCode());
        $this->assertEquals('message', $result->getReasonMessage());
    }

    public function testCreateSuccessWithoutExtraTimeAndIncidentSeverity(): void
    {
        $date = Carbon::now();

        $result = $this->subject->create(
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
                'incident_time' => $date->format(DATE_ATOM),
                'reason_code' => 'code',
                'reason_msg' => 'message',
            ]
        );

        $this->assertInstanceOf(AcsControlInterface::class, $result);

        $this->assertEquals('resourceLinkIdentifier', $result->getResourceLink()->getIdentifier());
        $this->assertEquals('resourceLinkTitle', $result->getResourceLink()->getTitle());
        $this->assertEquals('resourceLinkDescription', $result->getResourceLink()->getText());
        $this->assertEquals('issuerIdentifier', $result->getIssuerIdentifier());
        $this->assertEquals('userIdentifier', $result->getUserIdentifier());
        $this->assertEquals(1, $result->getAttemptNumber());
        $this->assertEquals(AcsControlInterface::ACTION_UPDATE, $result->getAction());
        $this->assertEquals(null, $result->getExtraTime());
        $this->assertEquals($date->format(DATE_ATOM), $result->getIncidentTime()->format(DATE_ATOM));
        $this->assertEquals(null, $result->getIncidentSeverity());
        $this->assertEquals('code', $result->getReasonCode());
        $this->assertEquals('message', $result->getReasonMessage());
    }

    /**
     * @dataProvider provideFailingData
     */
    public function testCreateFailures(array $data, string $errorMessage): void
    {
        $this->expectException(LtiException::class);
        $this->expectExceptionMessage(sprintf('Cannot create ACS control: %s', $errorMessage));

        $this->subject->create($data);
    }

    public function provideFailingData(): array
    {
        return [
            'Missing resource_link' => [
                [],
                'Missing mandatory resource_link',
            ],
            'Missing resource_link.id' => [
                [
                    'resource_link' => [],
                ],
                'Missing mandatory resource_link.id',
            ],
            'Missing user' => [
                [
                    'resource_link' => [
                        'id' => 'resourceLinkIdentifier',
                    ],
                ],
                'Missing mandatory user',
            ],
            'Missing user.sub' => [
                [
                    'resource_link' => [
                        'id' => 'resourceLinkIdentifier',
                    ],
                    'user' => [],
                ],
                'Missing mandatory user.sub',
            ],
            'Missing user.iss' => [
                [
                    'resource_link' => [
                        'id' => 'resourceLinkIdentifier',
                    ],
                    'user' => [
                        'sub' => 'userIdentifier',
                    ],
                ],
                'Missing mandatory user.iss',
            ],
            'Missing action' => [
                [
                    'resource_link' => [
                        'id' => 'resourceLinkIdentifier',
                    ],
                    'user' => [
                        'sub' => 'userIdentifier',
                        'iss' => 'issuerIdentifier',
                    ],
                ],
                'Invalid action',
            ],
            'Invalid action' => [
                [
                    'resource_link' => [
                        'id' => 'resourceLinkIdentifier',
                    ],
                    'user' => [
                        'sub' => 'userIdentifier',
                        'iss' => 'issuerIdentifier',
                    ],
                    'action' => 'invalid',
                ],
                'Invalid action',
            ],
            'Missing attempt_number' => [
                [
                    'resource_link' => [
                        'id' => 'resourceLinkIdentifier',
                    ],
                    'user' => [
                        'sub' => 'userIdentifier',
                        'iss' => 'issuerIdentifier',
                    ],
                    'action' => AcsControlInterface::ACTION_UPDATE,
                ],
                'Missing mandatory attempt_number',
            ],
            'Missing incident_time' => [
                [
                    'resource_link' => [
                        'id' => 'resourceLinkIdentifier',
                    ],
                    'user' => [
                        'sub' => 'userIdentifier',
                        'iss' => 'issuerIdentifier',
                    ],
                    'action' => AcsControlInterface::ACTION_UPDATE,
                    'attempt_number' => 1,
                ],
                'Missing mandatory incident_time',
            ],
        ];
    }
}
