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

namespace OAT\Library\Lti1p3Proctoring\Message\Launch\Builder;

use OAT\Library\Lti1p3Core\Exception\LtiException;
use OAT\Library\Lti1p3Core\Exception\LtiExceptionInterface;
use OAT\Library\Lti1p3Core\Message\Launch\Builder\ToolOriginatingLaunchBuilder;
use OAT\Library\Lti1p3Core\Message\LtiMessageInterface;
use OAT\Library\Lti1p3Core\Message\Payload\Claim\ResourceLinkClaim;
use OAT\Library\Lti1p3Core\Message\Payload\LtiMessagePayloadInterface;
use OAT\Library\Lti1p3Core\Registration\RegistrationInterface;
use Throwable;

/**
 * @see https://www.imsglobal.org/spec/proctoring/v1p0#h.a81u3sn00k84
 */
class StartAssessmentLaunchRequestBuilder extends ToolOriginatingLaunchBuilder
{
    /**
     * @throws LtiExceptionInterface
     */
    public function buildStartAssessmentLaunchRequest(
        ResourceLinkClaim $resourceLinkClaim,
        RegistrationInterface $registration,
        string $startAssessmentUrl,
        string $sessionData,
        int $attemptNumber = 1,
        string $deploymentId = null,
        array $optionalClaims = []
    ): LtiMessageInterface {
        try {
            $this->builder
                ->withClaim($resourceLinkClaim)
                ->withClaim(LtiMessagePayloadInterface::CLAIM_LTI_PROCTORING_SESSION_DATA, $sessionData)
                ->withClaim(LtiMessagePayloadInterface::CLAIM_LTI_PROCTORING_ATTEMPT_NUMBER, $attemptNumber);

            return $this->buildToolOriginatingLaunch(
                $registration,
                LtiMessageInterface::LTI_MESSAGE_TYPE_START_ASSESSMENT,
                $startAssessmentUrl,
                $deploymentId,
                $optionalClaims
            );

        } catch (LtiExceptionInterface $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            throw new LtiException(
                sprintf('Cannot create start assessment launch request: %s', $exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        }
    }

    /**
     * @throws LtiExceptionInterface
     */
    public function buildStartAssessmentLaunchRequestFromPayload(
        LtiMessagePayloadInterface $payload,
        RegistrationInterface $registration,
        string $deploymentId = null,
        array $optionalClaims = []
    ): LtiMessageInterface {
        try {

            return $this->buildStartAssessmentLaunchRequest(
                $payload->getResourceLink(),
                $registration,
                $payload->getProctoringStartAssessmentUrl(),
                $payload->getProctoringSessionData(),
                $payload->getProctoringAttemptNumber(),
                $deploymentId,
                $optionalClaims
            );

        } catch (LtiExceptionInterface $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            throw new LtiException(
                sprintf('Cannot create start assessment launch request from payload: %s', $exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        }
    }
}
