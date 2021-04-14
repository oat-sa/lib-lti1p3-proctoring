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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace OAT\Library\Lti1p3Proctoring\Tests\Integration\Flow;

use OAT\Library\Lti1p3Core\Message\Launch\Validator\Platform\PlatformLaunchValidator;
use OAT\Library\Lti1p3Core\Message\Launch\Validator\Tool\ToolLaunchValidator;
use OAT\Library\Lti1p3Core\Message\LtiMessageInterface;
use OAT\Library\Lti1p3Core\Resource\LtiResourceLink\LtiResourceLink;
use OAT\Library\Lti1p3Core\Security\Oidc\OidcAuthenticator;
use OAT\Library\Lti1p3Core\Security\Oidc\OidcInitiator;
use OAT\Library\Lti1p3Core\Tests\Traits\DomainTestingTrait;
use OAT\Library\Lti1p3Core\Tests\Traits\NetworkTestingTrait;
use OAT\Library\Lti1p3Proctoring\Message\Launch\Builder\StartAssessmentLaunchRequestBuilder;
use OAT\Library\Lti1p3Proctoring\Message\Launch\Builder\StartProctoringLaunchRequestBuilder;
use PHPUnit\Framework\TestCase;

/**
 * @see https://www.imsglobal.org/spec/proctoring/v1p0#h.s9djysqqig55
 */
class ProctoringMessageFlowTest extends TestCase
{
    use DomainTestingTrait;
    use NetworkTestingTrait;

    public function testProctoringFlow(): void
    {
        // Step 0 - Dependencies preparation

        $registrationRepository = $this->createTestRegistrationRepository();
        $userAuthenticator = $this->createTestUserAuthenticator();
        $nonceRepository = $this->createTestNonceRepository();

        $registration = $registrationRepository->find('registrationIdentifier');

        // Step 1 - Platform start proctoring launch request creation

        $resourceLink = new LtiResourceLink('resourceLinkIdentifier');

        $platformRequestMessage = (new StartProctoringLaunchRequestBuilder())->buildStartProctoringLaunchRequest(
            $resourceLink,
            $registration,
            'http://platform.com/start-assessment-url',
            'loginHint'
        );

        // Step 2 - OIDC handling

        $oidcInitPlatformRequest = $this->createServerRequest('GET', $platformRequestMessage->toUrl());

        $oidcInit = new OidcInitiator($registrationRepository);

        $oidcInitToolMessage = $oidcInit->initiate($oidcInitPlatformRequest);

        $oidcAuthToolRequest = $this->createServerRequest('GET', $oidcInitToolMessage->toUrl());

        $oidcAuth = new OidcAuthenticator($registrationRepository, $userAuthenticator);

        $oidcAuthPlatformMessage = $oidcAuth->authenticate($oidcAuthToolRequest);

        $oidcLaunchRequest = $this->createServerRequest(
            'POST',
            $oidcAuthPlatformMessage->getUrl(),
            $oidcAuthPlatformMessage->getParameters()->all()
        );

        $oidcLaunchValidator = new ToolLaunchValidator($registrationRepository, $nonceRepository);

        $launchValidationResult = $oidcLaunchValidator->validatePlatformOriginatingLaunch($oidcLaunchRequest);

        $this->assertFalse($launchValidationResult->hasError());
        $this->assertEquals(
            LtiMessageInterface::LTI_MESSAGE_TYPE_START_PROCTORING,
            $launchValidationResult->getPayload()->getMessageType()
        );
        $this->assertEquals(
            'http://platform.com/start-assessment-url',
            $launchValidationResult->getPayload()->getProctoringStartAssessmentUrl()
        );
        $this->assertEquals(1, $launchValidationResult->getPayload()->getProctoringAttemptNumber());

        // Step 3 - Platform start assessment launch request creation

        $startAssessmentMessage = (new StartAssessmentLaunchRequestBuilder())->buildStartAssessmentLaunchRequestFromPayload(
            $launchValidationResult->getPayload(),
            $registration
        );

        // Step 4 - Platform reception and validation

        $startAssessmentRequest = $this->createServerRequest(
            'POST',
            $startAssessmentMessage->getUrl(),
            $startAssessmentMessage->getParameters()->all()
        );

        $startAssessmentValidator = new PlatformLaunchValidator($registrationRepository, $nonceRepository);

        $startAssessmentValidationResult = $startAssessmentValidator->validateToolOriginatingLaunch($startAssessmentRequest);

        $this->assertFalse($startAssessmentValidationResult->hasError());
        $this->assertEquals(
            LtiMessageInterface::LTI_MESSAGE_TYPE_START_ASSESSMENT,
            $startAssessmentValidationResult->getPayload()->getMessageType()
        );
        $this->assertEquals(
            'resourceLinkIdentifier',
            $startAssessmentValidationResult->getPayload()->getResourceLink()->getIdentifier()
        );
        $this->assertEquals(1, $startAssessmentValidationResult->getPayload()->getProctoringAttemptNumber());
    }
}
