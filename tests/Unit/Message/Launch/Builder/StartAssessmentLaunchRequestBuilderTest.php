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
use OAT\Library\Lti1p3Core\Message\Payload\Builder\MessagePayloadBuilderInterface;
use OAT\Library\Lti1p3Core\Message\Payload\Claim\ResourceLinkClaim;
use OAT\Library\Lti1p3Core\Message\Payload\LtiMessagePayload;
use OAT\Library\Lti1p3Core\Message\Payload\LtiMessagePayloadInterface;
use OAT\Library\Lti1p3Core\Registration\RegistrationInterface;
use OAT\Library\Lti1p3Core\Tests\Traits\DomainTestingTrait;
use OAT\Library\Lti1p3Proctoring\Message\Launch\Builder\StartAssessmentLaunchRequestBuilder;
use PHPUnit\Framework\TestCase;

class StartAssessmentLaunchRequestBuilderTest extends TestCase
{
    use DomainTestingTrait;

    /** @var RegistrationInterface */
    private $registration;

    /** @var StartAssessmentLaunchRequestBuilder */
    private $subject;

    protected function setUp(): void
    {
        $this->registration = $this->createTestRegistration();

        $this->subject = new StartAssessmentLaunchRequestBuilder();
    }

    public function testBuildStartAssessmentLaunchRequestSuccess(): void
    {
        $resourceLinkClaim = new ResourceLinkClaim('resourceLinkIdentifier');

        $result = $this->subject->buildStartAssessmentLaunchRequest(
            $resourceLinkClaim,
            $this->registration,
            'http://platform.com/start-assessment-url',
            'data'
        );

        $this->assertInstanceOf(LtiMessageInterface::class, $result);
        $this->assertEquals('http://platform.com/start-assessment-url', $result->getUrl());

        $token = $this->parseJwt($result->getParameters()->getMandatory('JWT'));

        $this->assertTrue(
            $this->verifyJwt($token, $this->registration->getToolKeyChain()->getPublicKey())
        );

        $payload = new LtiMessagePayload($token);

        $this->assertEquals('resourceLinkIdentifier', $payload->getResourceLink()->getIdentifier());
        $this->assertEquals('data', $payload->getProctoringSessionData());
        $this->assertEquals(1, $payload->getProctoringAttemptNumber());
    }

    public function testBuildStartAssessmentLaunchRequestFailureOnInvalidDeploymentId(): void
    {
        $this->expectException(LtiExceptionInterface::class);
        $this->expectExceptionMessage('Invalid deployment id invalid for registration registrationIdentifier');

        $resourceLinkClaim = new ResourceLinkClaim('resourceLinkIdentifier');

        $this->subject->buildStartAssessmentLaunchRequest(
            $resourceLinkClaim,
            $this->registration,
            'http://platform.com/start-assessment-url',
            'data',
            1,
            'invalid'
        );
    }

    public function testBuildStartAssessmentLaunchRequestFailureOnGenericError(): void
    {
        $this->expectException(LtiExceptionInterface::class);
        $this->expectExceptionMessage('Cannot create start assessment launch request: generic error');

        $builderMock = $this->createMock(MessagePayloadBuilderInterface::class);
        $builderMock
            ->expects($this->once())
            ->method('withClaim')
            ->willThrowException(new Exception('generic error'));

        $subject = new StartAssessmentLaunchRequestBuilder($builderMock);

        $resourceLinkClaimMock = $this->createMock(ResourceLinkClaim::class);

        $subject->buildStartAssessmentLaunchRequest(
            $resourceLinkClaimMock,
            $this->registration,
            'http://platform.com/start-assessment-url',
            'data'
        );
    }

    public function testBuildStartAssessmentLaunchRequestFromPayloadSuccess(): void
    {
        $resourceLinkClaim = new ResourceLinkClaim('resourceLinkIdentifier');

        $token = $this->buildJwt(
            [],
            [
                $resourceLinkClaim::getClaimName() => $resourceLinkClaim->normalize(),
                LtiMessagePayloadInterface::CLAIM_LTI_PROCTORING_START_ASSESSMENT_URL => 'http://platform.com/start-assessment-url',
                LtiMessagePayloadInterface::CLAIM_LTI_PROCTORING_SESSION_DATA => 'data',
                LtiMessagePayloadInterface::CLAIM_LTI_PROCTORING_ATTEMPT_NUMBER => 1,
            ],
            $this->registration->getPlatformKeyChain()->getPrivateKey()
        );

        $payload = new LtiMessagePayload($token);

        $result = $this->subject->buildStartAssessmentLaunchRequestFromPayload(
            $payload,
            $this->registration
        );

        $this->assertInstanceOf(LtiMessageInterface::class, $result);
        $this->assertEquals('http://platform.com/start-assessment-url', $result->getUrl());

        $token = $this->parseJwt($result->getParameters()->getMandatory('JWT'));

        $this->assertTrue(
            $this->verifyJwt($token, $this->registration->getToolKeyChain()->getPublicKey())
        );

        $payload = new LtiMessagePayload($token);

        $this->assertEquals('resourceLinkIdentifier', $payload->getResourceLink()->getIdentifier());
        $this->assertEquals('data', $payload->getProctoringSessionData());
        $this->assertEquals(1, $payload->getProctoringAttemptNumber());
    }

    public function testBuildStartAssessmentLaunchRequestFromPayloadFailureOnMissingResourceLink(): void
    {
        $this->expectException(LtiExceptionInterface::class);
        $this->expectExceptionMessage('Missing resource link claim from payload');

        $token = $this->buildJwt(
            [],
            [],
            $this->registration->getPlatformKeyChain()->getPrivateKey()
        );

        $payload = new LtiMessagePayload($token);

        $this->subject->buildStartAssessmentLaunchRequestFromPayload(
            $payload,
            $this->registration
        );
    }

    public function testBuildStartAssessmentLaunchRequestFromPayloadFailureOnMissingStartAssessmentUrl(): void
    {
        $this->expectException(LtiExceptionInterface::class);
        $this->expectExceptionMessage('Missing start assessment url claim from payload');

        $resourceLinkClaim = new ResourceLinkClaim('resourceLinkIdentifier');

        $token = $this->buildJwt(
            [],
            [
                $resourceLinkClaim::getClaimName() => $resourceLinkClaim->normalize(),
            ],
            $this->registration->getPlatformKeyChain()->getPrivateKey()
        );

        $payload = new LtiMessagePayload($token);

        $this->subject->buildStartAssessmentLaunchRequestFromPayload(
            $payload,
            $this->registration
        );
    }

    public function testBuildStartAssessmentLaunchRequestFromPayloadFailureOnMissingSessionData(): void
    {
        $this->expectException(LtiExceptionInterface::class);
        $this->expectExceptionMessage('Missing session data claim from payload');

        $resourceLinkClaim = new ResourceLinkClaim('resourceLinkIdentifier');

        $token = $this->buildJwt(
            [],
            [
                $resourceLinkClaim::getClaimName() => $resourceLinkClaim->normalize(),
                LtiMessagePayloadInterface::CLAIM_LTI_PROCTORING_START_ASSESSMENT_URL => 'http://platform.com/start-assessment-url',
            ],
            $this->registration->getPlatformKeyChain()->getPrivateKey()
        );

        $payload = new LtiMessagePayload($token);

        $this->subject->buildStartAssessmentLaunchRequestFromPayload(
            $payload,
            $this->registration
        );
    }

    public function testBuildStartAssessmentLaunchRequestFromPayloadFailureOnMissingAttemptNumber(): void
    {
        $this->expectException(LtiExceptionInterface::class);
        $this->expectExceptionMessage('Missing attempt number claim from payload');

        $resourceLinkClaim = new ResourceLinkClaim('resourceLinkIdentifier');

        $token = $this->buildJwt(
            [],
            [
                $resourceLinkClaim::getClaimName() => $resourceLinkClaim->normalize(),
                LtiMessagePayloadInterface::CLAIM_LTI_PROCTORING_START_ASSESSMENT_URL => 'http://platform.com/start-assessment-url',
                LtiMessagePayloadInterface::CLAIM_LTI_PROCTORING_SESSION_DATA => 'data',
            ],
            $this->registration->getPlatformKeyChain()->getPrivateKey()
        );

        $payload = new LtiMessagePayload($token);

        $this->subject->buildStartAssessmentLaunchRequestFromPayload(
            $payload,
            $this->registration
        );
    }

    public function testBuildStartAssessmentLaunchRequestFromPayloadFailureOnInvalidDeploymentId(): void
    {
        $this->expectException(LtiExceptionInterface::class);
        $this->expectExceptionMessage('Invalid deployment id invalid for registration registrationIdentifier');

        $resourceLinkClaim = new ResourceLinkClaim('resourceLinkIdentifier');

        $token = $this->buildJwt(
            [],
            [
                $resourceLinkClaim::getClaimName() => $resourceLinkClaim->normalize(),
                LtiMessagePayloadInterface::CLAIM_LTI_PROCTORING_START_ASSESSMENT_URL => 'http://platform.com/start-assessment-url',
                LtiMessagePayloadInterface::CLAIM_LTI_PROCTORING_SESSION_DATA => 'data',
                LtiMessagePayloadInterface::CLAIM_LTI_PROCTORING_ATTEMPT_NUMBER => 1,
            ],
            $this->registration->getPlatformKeyChain()->getPrivateKey()
        );

        $payload = new LtiMessagePayload($token);

        $this->subject->buildStartAssessmentLaunchRequestFromPayload(
            $payload,
            $this->registration,
            'invalid'
        );
    }

    public function testBuildStartAssessmentLaunchRequestFromPayloadFailureOnGenericError(): void
    {
        $this->expectException(LtiExceptionInterface::class);
        $this->expectExceptionMessage('Cannot create start assessment launch request from payload: generic error');

        $payloadMock = $this->createMock(LtiMessagePayloadInterface::class);
        $payloadMock
            ->expects($this->once())
            ->method('getResourceLink')
            ->willThrowException(new Exception('generic error'));

        $this->subject->buildStartAssessmentLaunchRequestFromPayload(
            $payloadMock,
            $this->registration
        );
    }
}
