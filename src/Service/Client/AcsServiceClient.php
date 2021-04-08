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

namespace OAT\Library\Lti1p3Proctoring\Service\Client;

use InvalidArgumentException;
use OAT\Library\Lti1p3Core\Exception\LtiException;
use OAT\Library\Lti1p3Core\Exception\LtiExceptionInterface;
use OAT\Library\Lti1p3Core\Message\Payload\LtiMessagePayloadInterface;
use OAT\Library\Lti1p3Core\Registration\RegistrationInterface;
use OAT\Library\Lti1p3Core\Service\Client\LtiServiceClient;
use OAT\Library\Lti1p3Core\Service\Client\LtiServiceClientInterface;
use OAT\Library\Lti1p3Proctoring\Model\AcsControlInterface;
use OAT\Library\Lti1p3Proctoring\Model\AcsControlResultInterface;
use OAT\Library\Lti1p3Proctoring\Serializer\AcsControlResultSerializer;
use OAT\Library\Lti1p3Proctoring\Serializer\AcsControlResultSerializerInterface;
use OAT\Library\Lti1p3Proctoring\Serializer\AcsControlSerializer;
use OAT\Library\Lti1p3Proctoring\Serializer\AcsControlSerializerInterface;
use OAT\Library\Lti1p3Proctoring\Service\AcsServiceInterface;
use Throwable;

/**
 * @see https://www.imsglobal.org/spec/proctoring/v1p0#h.awao2i3cnvsy
 */
class AcsServiceClient implements AcsServiceInterface
{
    /** @var LtiServiceClientInterface */
    private $client;

    /** @var AcsControlSerializerInterface */
    private $controlSerializer;

    /** @var AcsControlResultSerializerInterface */
    private $controlResultSerializer;

    public function __construct(
        ?LtiServiceClientInterface $client = null,
        ?AcsControlSerializerInterface $controlSerializer = null,
        ?AcsControlResultSerializerInterface $controlResultSerializer = null
    ) {
        $this->client = $client ?? new LtiServiceClient();
        $this->controlSerializer = $controlSerializer ?? new AcsControlSerializer();
        $this->controlResultSerializer = $controlResultSerializer ?? new AcsControlResultSerializer();
    }

    /**
     * @throws LtiExceptionInterface
     */
    public function sendControlForPayload(
        RegistrationInterface $registration,
        AcsControlInterface $control,
        LtiMessagePayloadInterface $payload
    ): AcsControlResultInterface {
        try {
            $acsClaim = $payload->getAcs();

            if (null === $acsClaim) {
                throw new InvalidArgumentException('Provided payload does not contain ACS claim');
            }

            if (!in_array($control->getAction(), $acsClaim->getActions())) {
                throw new InvalidArgumentException('Provided control action not allowed from ACS claim');
            }

            return $this->sendControl(
                $registration,
                $control,
                $acsClaim->getAssessmentControlUrl()
            );
        } catch (Throwable $exception) {
            throw new LtiException(
                sprintf('Cannot send ACS control for payload: %s', $exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        }
    }

    /**
     * @throws LtiExceptionInterface
     */
    public function sendControl(
        RegistrationInterface $registration,
        AcsControlInterface $control,
        string $acsUrl
    ): AcsControlResultInterface {
        try {
            if (null === $control->getIssuerIdentifier()) {
                $control->setIssuerIdentifier($registration->getPlatform()->getAudience());
            }

            $response = $this->client->request(
                $registration,
                'POST',
                $acsUrl,
                [
                    'headers' => ['Accept' => static::CONTENT_TYPE_CONTROL],
                    'body' => $this->controlSerializer->serialize($control),
                ],
                [
                    static::AUTHORIZATION_SCOPE_CONTROL,
                ]
            );

            return $this->controlResultSerializer->deserialize($response->getBody()->__toString());
        } catch (Throwable $exception) {
            throw new LtiException(
                sprintf('Cannot send ACS control: %s', $exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        }
    }
}
