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

namespace OAT\Library\Lti1p3Proctoring\Tests\Integration\Service\Client;

use Carbon\Carbon;
use Exception;
use OAT\Library\Lti1p3Core\Exception\LtiExceptionInterface;
use OAT\Library\Lti1p3Core\Message\Payload\Claim\AcsClaim;
use OAT\Library\Lti1p3Core\Message\Payload\LtiMessagePayloadInterface;
use OAT\Library\Lti1p3Core\Resource\LtiResourceLink\LtiResourceLink;
use OAT\Library\Lti1p3Core\Service\Client\LtiServiceClientInterface;
use OAT\Library\Lti1p3Core\Tests\Traits\DomainTestingTrait;
use OAT\Library\Lti1p3Core\Tests\Traits\NetworkTestingTrait;
use OAT\Library\Lti1p3Proctoring\Model\AcsControl;
use OAT\Library\Lti1p3Proctoring\Model\AcsControlInterface;
use OAT\Library\Lti1p3Proctoring\Model\AcsControlResult;
use OAT\Library\Lti1p3Proctoring\Model\AcsControlResultInterface;
use OAT\Library\Lti1p3Proctoring\Serializer\AcsControlResultSerializer;
use OAT\Library\Lti1p3Proctoring\Serializer\AcsControlResultSerializerInterface;
use OAT\Library\Lti1p3Proctoring\Serializer\AcsControlSerializer;
use OAT\Library\Lti1p3Proctoring\Serializer\AcsControlSerializerInterface;
use OAT\Library\Lti1p3Proctoring\Service\AcsServiceInterface;
use OAT\Library\Lti1p3Proctoring\Service\Client\AcsServiceClient;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AcsServiceClientTest extends TestCase
{
    use DomainTestingTrait;
    use NetworkTestingTrait;

    /** @var LtiServiceClientInterface|MockObject */
    private $clientMock;

    /** @var AcsControlSerializerInterface */
    private $controlSerializer;

    /** @var AcsControlResultSerializerInterface */
    private $controlResultSerializer;

    /** @var AcsServiceClient */
    private $subject;

    protected function setUp(): void
    {
        $this->clientMock = $this->createMock(LtiServiceClientInterface::class);

        $this->controlSerializer = new AcsControlSerializer();
        $this->controlResultSerializer = new AcsControlResultSerializer();

        $this->subject = new AcsServiceClient($this->clientMock);
    }

    public function testSendControlSuccess(): void
    {
        $registration = $this->createTestRegistration();

        $control = new AcsControl(
            new LtiResourceLink('resourceLinkIdentifier'),
            'userIdentifier',
            AcsControlInterface::ACTION_UPDATE,
            Carbon::now(),
            1,
            'http://platform.com'
        );

        $controlResult = new AcsControlResult(
            AcsControlResultInterface::STATUS_RUNNING
        );

        $this->clientMock
            ->expects($this->once())
            ->method('request')
            ->with(
                $registration,
                'POST',
                'http://platform.com/acs',
                [
                    'headers' => [
                        'Accept' => AcsServiceInterface::CONTENT_TYPE_CONTROL,
                    ],
                    'body' => $this->controlSerializer->serialize($control),
                ],
                [
                    AcsServiceInterface::AUTHORIZATION_SCOPE_CONTROL,
                ]
            )
            ->willReturn(
                $this->createResponse($this->controlResultSerializer->serialize($controlResult))
            );

        $result = $this->subject->sendControl($registration, $control, 'http://platform.com/acs');

        $this->assertInstanceOf(AcsControlResultInterface::class, $result);
        $this->assertEquals($controlResult, $result);
    }

    public function testSendControlSuccessWithAutomaticIssuerSet(): void
    {
        $registration = $this->createTestRegistration();

        $control = new AcsControl(
            new LtiResourceLink('resourceLinkIdentifier'),
            'userIdentifier',
            AcsControlInterface::ACTION_UPDATE,
            Carbon::now()
        );

        $controlCloneWithIssuer = clone $control;
        $controlCloneWithIssuer->setIssuerIdentifier($registration->getPlatform()->getAudience());

        $controlResult = new AcsControlResult(
            AcsControlResultInterface::STATUS_RUNNING
        );

        $this->clientMock
            ->expects($this->once())
            ->method('request')
            ->with(
                $registration,
                'POST',
                'http://platform.com/acs',
                [
                    'headers' => [
                        'Accept' => AcsServiceInterface::CONTENT_TYPE_CONTROL,
                    ],
                    'body' => $this->controlSerializer->serialize($controlCloneWithIssuer),
                ],
                [
                    AcsServiceInterface::AUTHORIZATION_SCOPE_CONTROL,
                ]
            )
            ->willReturn(
                $this->createResponse($this->controlResultSerializer->serialize($controlResult))
            );

        $result = $this->subject->sendControl($registration, $control, 'http://platform.com/acs');

        $this->assertInstanceOf(AcsControlResultInterface::class, $result);
        $this->assertEquals($controlResult, $result);
    }

    public function testSendControlFailure(): void
    {
        $this->expectException(LtiExceptionInterface::class);
        $this->expectExceptionMessage('Cannot send ACS control: generic error');

        $registration = $this->createTestRegistration();

        $control = new AcsControl(
            new LtiResourceLink('resourceLinkIdentifier'),
            'userIdentifier',
            AcsControlInterface::ACTION_UPDATE,
            Carbon::now(),
            1,
            'http://platform.com'
        );

        $this->clientMock
            ->expects($this->once())
            ->method('request')
            ->with(
                $registration,
                'POST',
                'http://platform.com/acs',
                [
                    'headers' => [
                        'Accept' => AcsServiceInterface::CONTENT_TYPE_CONTROL,
                    ],
                    'body' => $this->controlSerializer->serialize($control),
                ],
                [
                    AcsServiceInterface::AUTHORIZATION_SCOPE_CONTROL,
                ]
            )
            ->willThrowException(
                new Exception('generic error')
            );

        $this->subject->sendControl($registration, $control, 'http://platform.com/acs');
    }

    public function testSendControlForPayloadSuccess(): void
    {
        $registration = $this->createTestRegistration();

        $control = new AcsControl(
            new LtiResourceLink('resourceLinkIdentifier'),
            'userIdentifier',
            AcsControlInterface::ACTION_UPDATE,
            Carbon::now(),
            1,
            'http://platform.com'
        );

        $controlResult = new AcsControlResult(
            AcsControlResultInterface::STATUS_RUNNING
        );

        $this->clientMock
            ->expects($this->once())
            ->method('request')
            ->with(
                $registration,
                'POST',
                'http://platform.com/acs',
                [
                    'headers' => [
                        'Accept' => AcsServiceInterface::CONTENT_TYPE_CONTROL,
                    ],
                    'body' => $this->controlSerializer->serialize($control),
                ],
                [
                    AcsServiceInterface::AUTHORIZATION_SCOPE_CONTROL,
                ]
            )
            ->willReturn(
                $this->createResponse($this->controlResultSerializer->serialize($controlResult))
            );

        $acsClaim = new AcsClaim([AcsControlInterface::ACTION_UPDATE], 'http://platform.com/acs');

        $payloadMock = $this->createMock(LtiMessagePayloadInterface::class);
        $payloadMock
            ->expects($this->once())
            ->method('getAcs')
            ->willReturn($acsClaim);

        $result = $this->subject->sendControlForPayload($registration, $control, $payloadMock);

        $this->assertInstanceOf(AcsControlResultInterface::class, $result);
        $this->assertEquals($controlResult, $result);
    }

    public function testSendControlForPayloadFailureOnMissingAcsClaim(): void
    {
        $this->expectException(LtiExceptionInterface::class);
        $this->expectExceptionMessage('Cannot send ACS control for payload: Provided payload does not contain ACS claim');

        $registration = $this->createTestRegistration();

        $this->clientMock
            ->expects($this->never())
            ->method('request');

        $payloadMock = $this->createMock(LtiMessagePayloadInterface::class);
        $payloadMock
            ->expects($this->once())
            ->method('getAcs')
            ->willReturn(null);

        $this->subject->sendControlForPayload(
            $registration,
            $this->createMock(AcsControlInterface::class),
            $payloadMock
        );
    }

    public function testSendControlForPayloadFailureOnInvalidAcsAction(): void
    {
        $this->expectException(LtiExceptionInterface::class);
        $this->expectExceptionMessage('Cannot send ACS control for payload: Provided control action not allowed from ACS claim');

        $registration = $this->createTestRegistration();

        $control = new AcsControl(
            new LtiResourceLink('resourceLinkIdentifier'),
            'userIdentifier',
            AcsControlInterface::ACTION_UPDATE,
            Carbon::now(),
            1,
            'http://platform.com'
        );

        $this->clientMock
            ->expects($this->never())
            ->method('request');

        $acsClaim = new AcsClaim([AcsControlInterface::ACTION_PAUSE], 'http://platform.com/acs');

        $payloadMock = $this->createMock(LtiMessagePayloadInterface::class);
        $payloadMock
            ->expects($this->once())
            ->method('getAcs')
            ->willReturn($acsClaim);

        $this->subject->sendControlForPayload(
            $registration,
            $control,
            $payloadMock
        );
    }
}
