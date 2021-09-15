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
use OAT\Library\Lti1p3Core\Message\Payload\LtiMessagePayload;
use OAT\Library\Lti1p3Core\Registration\RegistrationInterface;
use OAT\Library\Lti1p3Core\Tests\Traits\DomainTestingTrait;
use OAT\Library\Lti1p3Core\Tool\Tool;
use OAT\Library\Lti1p3Proctoring\Message\Launch\Builder\EndAssessmentLaunchRequestBuilder;
use PHPUnit\Framework\TestCase;

class EndAssessmentLaunchRequestBuilderTest extends TestCase
{
    use DomainTestingTrait;

    /** @var RegistrationInterface */
    private $registration;

    /** @var EndAssessmentLaunchRequestBuilder */
    private $subject;

    protected function setUp(): void
    {
        $this->registration = $this->createTestRegistration();

        $this->subject = new EndAssessmentLaunchRequestBuilder();
    }

    public function testBuildEndAssessmentLaunchRequestSuccess(): void
    {
        $result = $this->subject->buildEndAssessmentLaunchRequest(
            $this->registration,
            'loginHint',
            'http://tool.com/end-assessment-url',
            1,
            null,
            [
                'Learner'
            ],
            [
                'a' => 'b'
            ]
        );

        $this->assertInstanceOf(LtiMessageInterface::class, $result);

        $this->assertEquals(
            'http://tool.com/end-assessment-url',
            $result->getParameters()->getMandatory('target_link_uri')
        );

        $ltiMessageHintToken = $this->parseJwt($result->getParameters()->getMandatory('lti_message_hint'));

        $this->assertTrue(
            $this->verifyJwt($ltiMessageHintToken, $this->registration->getPlatformKeyChain()->getPublicKey())
        );

        $payload = new LtiMessagePayload($ltiMessageHintToken);

        $this->assertEquals(['Learner'], $payload->getRoles());
        $this->assertEquals('b', $payload->getClaim('a'));
    }

    public function testBuildEndAssessmentLaunchErrorRequestSuccess(): void
    {
        $result = $this->subject->buildEndAssessmentLaunchErrorRequest(
            $this->registration,
            'loginHint',
            'errorMessage',
            'errorLog',
            'http://tool.com/end-assessment-url',
            1,
            null,
            [
                'Learner'
            ],
            [
                'a' => 'b'
            ]
        );

        $this->assertInstanceOf(LtiMessageInterface::class, $result);

        $this->assertEquals(
            'http://tool.com/end-assessment-url',
            $result->getParameters()->getMandatory('target_link_uri')
        );

        $ltiMessageHintToken = $this->parseJwt($result->getParameters()->getMandatory('lti_message_hint'));

        $this->assertTrue(
            $this->verifyJwt($ltiMessageHintToken, $this->registration->getPlatformKeyChain()->getPublicKey())
        );

        $payload = new LtiMessagePayload($ltiMessageHintToken);

        $this->assertEquals(['Learner'], $payload->getRoles());
        $this->assertEquals('b', $payload->getClaim('a'));
        $this->assertEquals('errorMessage', $payload->getProctoringErrorMessage());
        $this->assertEquals('errorLog', $payload->getProctoringErrorLog());
    }

    public function testBuildEndAssessmentLaunchRequestFailureOnMissingLaunchUrl(): void
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

        $this->expectException(LtiExceptionInterface::class);
        $this->expectExceptionMessage('Neither end assessment url nor tool default url were presented');

        $this->subject->buildEndAssessmentLaunchRequest($registration, 'loginHint');
    }

    public function testBuildEndAssessmentLaunchRequestFailureOnGenericError(): void
    {
        $builderMock = $this->createMock(MessagePayloadBuilderInterface::class);
        $builderMock
            ->expects($this->once())
            ->method('reset')
            ->willThrowException(new Exception('generic error'));

        $subject = new EndAssessmentLaunchRequestBuilder($builderMock);

        $this->expectException(LtiExceptionInterface::class);
        $this->expectExceptionMessage('Cannot create end assessment launch request: generic error');

        $subject->buildEndAssessmentLaunchRequest($this->registration, 'loginHint');
    }
}
