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

namespace OAT\Library\Lti1p3Proctoring\Tests\Unit\Message\Launch\Builder;

use Exception;
use OAT\Library\Lti1p3Core\Exception\LtiExceptionInterface;
use OAT\Library\Lti1p3Core\Message\LtiMessageInterface;
use OAT\Library\Lti1p3Core\Message\Payload\Claim\AcsClaim;
use OAT\Library\Lti1p3Core\Message\Payload\LtiMessagePayload;
use OAT\Library\Lti1p3Core\Registration\RegistrationInterface;
use OAT\Library\Lti1p3Core\Resource\LtiResourceLink\LtiResourceLink;
use OAT\Library\Lti1p3Core\Resource\LtiResourceLink\LtiResourceLinkInterface;
use OAT\Library\Lti1p3Core\Tests\Traits\DomainTestingTrait;
use OAT\Library\Lti1p3Core\Tool\Tool;
use OAT\Library\Lti1p3Proctoring\Message\Launch\Builder\StartProctoringLaunchRequestBuilder;
use OAT\Library\Lti1p3Proctoring\Model\AcsControlInterface;
use PHPUnit\Framework\TestCase;

class StartProctoringLaunchRequestBuilderTest extends TestCase
{
    use DomainTestingTrait;

    /** @var RegistrationInterface */
    private $registration;

    /** @var StartProctoringLaunchRequestBuilder */
    private $subject;

    protected function setUp(): void
    {
        $this->registration = $this->createTestRegistration();

        $this->subject = new StartProctoringLaunchRequestBuilder();
    }

    public function testBuildStartProctoringLaunchRequestSuccess(): void
    {
        $resourceLink = new LtiResourceLink('resourceLinkIdentifier');

        $result = $this->subject->buildStartProctoringLaunchRequest(
            $resourceLink,
            $this->registration,
            'http://tool.com/start-proctoring-url',
            'loginHint',
            1,
            null,
            [
                'Instructor'
            ],
            [
                new AcsClaim(
                    [
                        AcsControlInterface::ACTION_UPDATE
                    ],
                    'http://platform.com/acs'
                ),
                'a' => 'b'
            ]
        );

        $this->assertInstanceOf(LtiMessageInterface::class, $result);

        $this->assertEquals(
            'http://tool.com/launch',
            $result->getParameters()->getMandatory('target_link_uri')
        );

        $ltiMessageHintToken = $this->parseJwt($result->getParameters()->getMandatory('lti_message_hint'));

        $this->assertTrue(
            $this->verifyJwt($ltiMessageHintToken, $this->registration->getPlatformKeyChain()->getPublicKey())
        );

        $payload = new LtiMessagePayload($ltiMessageHintToken);

        $this->assertEquals('http://tool.com/start-proctoring-url', $payload->getProctoringStartAssessmentUrl());
        $this->assertEquals(['Instructor'], $payload->getRoles());
        $this->assertEquals([AcsControlInterface::ACTION_UPDATE], $payload->getAcs()->getActions());
        $this->assertEquals('http://platform.com/acs', $payload->getAcs()->getAssessmentControlUrl());
        $this->assertEquals('b', $payload->getClaim('a'));
    }

    public function testBuildStartProctoringLaunchRequestFailureOnMissingLaunchUrl(): void
    {
        $tool = new Tool(
            'toolIdentifier',
            'toolName',
            'toolAudience',
            'http://tool.com/oidc-init'
        );

        $registration  = $this->createTestRegistration(
            'registrationIdentifier',
            'registrationClientId',
            $this->createTestPlatform(),
            $tool,
            ['deploymentIdentifier']
        );

        $resourceLink = new LtiResourceLink('resourceLinkIdentifier');

        $this->expectException(LtiExceptionInterface::class);
        $this->expectExceptionMessage('Neither resource link url nor tool default url were presented');

        $this->subject->buildStartProctoringLaunchRequest(
            $resourceLink,
            $registration,
            'http://tool.com/start-proctoring-url',
            'loginHint'
        );
    }

    public function testBuildStartProctoringLaunchRequestFailureOnGenericError(): void
    {
        $resourceLinkMock = $this->createMock(LtiResourceLinkInterface::class);
        $resourceLinkMock
            ->expects($this->once())
            ->method('getIdentifier')
            ->willThrowException(new Exception('generic error'));

        $this->expectException(LtiExceptionInterface::class);
        $this->expectExceptionMessage('Cannot create start proctoring launch request: generic error');

        $this->subject->buildStartProctoringLaunchRequest(
            $resourceLinkMock,
            $this->registration,
            'http://tool.com/start-proctoring-url',
            'loginHint'
        );
    }
}
