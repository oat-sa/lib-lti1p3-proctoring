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

namespace OAT\Library\Lti1p3Proctoring\Service\Server\Handler;

use Http\Message\ResponseFactory;
use Nyholm\Psr7\Factory\HttplugFactory;
use OAT\Library\Lti1p3Core\Security\OAuth2\Validator\Result\RequestAccessTokenValidationResultInterface;
use OAT\Library\Lti1p3Core\Service\Server\Handler\LtiServiceServerRequestHandlerInterface;
use OAT\Library\Lti1p3Proctoring\Serializer\AcsControlResultSerializer;
use OAT\Library\Lti1p3Proctoring\Serializer\AcsControlResultSerializerInterface;
use OAT\Library\Lti1p3Proctoring\Serializer\AcsControlSerializer;
use OAT\Library\Lti1p3Proctoring\Serializer\AcsControlSerializerInterface;
use OAT\Library\Lti1p3Proctoring\Service\AcsServiceInterface;
use OAT\Library\Lti1p3Proctoring\Service\Server\Processor\AcsServiceServerControlProcessorInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @see https://www.imsglobal.org/spec/proctoring/v1p0#h.awao2i3cnvsy
 */
class AcsServiceServerRequestHandler implements LtiServiceServerRequestHandlerInterface, AcsServiceInterface
{
    /** @var AcsServiceServerControlProcessorInterface */
    private $processor;

    /** @var AcsControlSerializerInterface */
    private $controlSerializer;

    /** @var AcsControlResultSerializerInterface */
    private $controlResultSerializer;

    /** @var ResponseFactory */
    private $factory;

    public function __construct(
        AcsServiceServerControlProcessorInterface $processor,
        ?AcsControlSerializerInterface $controlSerializer = null,
        ?AcsControlResultSerializerInterface $controlResultSerializer = null,
        ?ResponseFactory $factory = null
    ) {
        $this->processor = $processor;
        $this->controlSerializer = $controlSerializer ?? new AcsControlSerializer();
        $this->controlResultSerializer = $controlResultSerializer ?? new AcsControlResultSerializer();
        $this->factory = $factory ?? new HttplugFactory();
    }

    public function getServiceName(): string
    {
        return static::NAME;
    }

    public function getAllowedContentType(): ?string
    {
        return static::CONTENT_TYPE_CONTROL;
    }

    public function getAllowedMethods(): array
    {
        return [
            'POST',
        ];
    }

    public function getAllowedScopes(): array
    {
        return [
            static::AUTHORIZATION_SCOPE_CONTROL,
        ];
    }

    public function handleValidatedServiceRequest(
        RequestAccessTokenValidationResultInterface $validationResult,
        ServerRequestInterface $request,
        array $options = []
    ): ResponseInterface {
        $registration = $validationResult->getRegistration();

        $controlResult = $this->processor->process(
            $registration,
            $this->controlSerializer->deserialize($request->getBody()->__toString())
        );

        $responseBody = $this->controlResultSerializer->serialize($controlResult);
        $responseHeaders = [
            'Content-Type' => static::CONTENT_TYPE_CONTROL,
            'Content-Length' => strlen($responseBody),
        ];

        return $this->factory->createResponse(200, null, $responseHeaders, $responseBody);
    }
}
