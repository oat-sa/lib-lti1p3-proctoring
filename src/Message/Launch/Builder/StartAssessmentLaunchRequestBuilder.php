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

use InvalidArgumentException;
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
        int $attemptNumber,
        string $deploymentId = null,
        array $optionalClaims = [],
        bool $endAssessmentReturn = false
    ): LtiMessageInterface {
        try {
            $this->builder
                ->withClaim($resourceLinkClaim)
                ->withClaim(LtiMessagePayloadInterface::CLAIM_LTI_PROCTORING_SESSION_DATA, $sessionData)
                ->withClaim(LtiMessagePayloadInterface::CLAIM_LTI_PROCTORING_ATTEMPT_NUMBER, $attemptNumber)
                ->withClaim(LtiMessagePayloadInterface::CLAIM_LTI_PROCTORING_END_ASSESSMENT_RETURN, $endAssessmentReturn);

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
        array $optionalClaims = [],
        bool $endAssessmentReturn = false
    ): LtiMessageInterface {
        try {

            $resourceLink = $payload->getResourceLink();

            if (null === $resourceLink) {
                throw new InvalidArgumentException('Missing resource link claim from payload');
            }

            $startAssessmentUrl = $payload->getProctoringStartAssessmentUrl();

            if (null === $startAssessmentUrl) {
                throw new InvalidArgumentException('Missing start assessment url claim from payload');
            }

            $sessionData = $payload->getProctoringSessionData();

            if (null === $sessionData) {
                throw new InvalidArgumentException('Missing session data claim from payload');
            }

            $attemptNumber = $payload->getProctoringAttemptNumber();

            if (null === $attemptNumber) {
                throw new InvalidArgumentException('Missing attempt number claim from payload');
            }

            return $this->buildStartAssessmentLaunchRequest(
                $resourceLink,
                $registration,
                $startAssessmentUrl,
                $sessionData,
                $attemptNumber,
                $deploymentId,
                $optionalClaims,
                $endAssessmentReturn
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
