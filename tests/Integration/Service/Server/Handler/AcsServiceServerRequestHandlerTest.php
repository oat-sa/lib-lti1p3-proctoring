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

namespace OAT\Library\Lti1p3Proctoring\Tests\Integration\Service\Server\Handler;

use Carbon\Carbon;
use Exception;
use Nyholm\Psr7\ServerRequest;
use OAT\Library\Lti1p3Core\Registration\RegistrationInterface;
use OAT\Library\Lti1p3Core\Resource\LtiResourceLink\LtiResourceLink;
use OAT\Library\Lti1p3Core\Security\OAuth2\Validator\RequestAccessTokenValidator;
use OAT\Library\Lti1p3Core\Security\OAuth2\Validator\Result\RequestAccessTokenValidationResult;
use OAT\Library\Lti1p3Core\Service\Server\LtiServiceServer;
use OAT\Library\Lti1p3Core\Tests\Resource\Logger\TestLogger;
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
use OAT\Library\Lti1p3Proctoring\Service\Server\Handler\AcsServiceServerRequestHandler;
use OAT\Library\Lti1p3Proctoring\Service\Server\Processor\AcsServiceServerControlProcessorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LogLevel;

class AcsServiceServerRequestHandlerTest extends TestCase
{
    use DomainTestingTrait;
    use NetworkTestingTrait;

    /** @var RequestAccessTokenValidator|MockObject */
    private $validatorMock;

    /** @var AcsServiceServerControlProcessorInterface */
    private $processor;

    /** @var TestLogger */
    private $logger;

    /** @var AcsControlSerializerInterface */
    private $controlSerializer;

    /** @var AcsControlResultSerializerInterface */
    private $controlResultSerializer;

    /** @var AcsServiceServerRequestHandler */
    private $subject;

    /** @var LtiServiceServer */
    private $server;

    protected function setUp(): void
    {
        $this->validatorMock = $this->createMock(RequestAccessTokenValidator::class);
        $this->processor = $this->createTestProcessor();

        $this->logger = new TestLogger();
        $this->controlSerializer = new AcsControlSerializer();
        $this->controlResultSerializer = new AcsControlResultSerializer();

        $this->subject = new AcsServiceServerRequestHandler($this->processor);

        $this->server = new LtiServiceServer(
            $this->validatorMock,
            $this->subject,
            null,
            $this->logger
        );
    }

    public function testAcsControlRequestHandlingSuccess(): void
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

        $request = new ServerRequest(
            'POST',
            'http://platform.com/acs',
            [
                'Accept' => AcsServiceInterface::CONTENT_TYPE_CONTROL,
            ],
            $this->controlSerializer->serialize($control)
        );

        $validationResult = new RequestAccessTokenValidationResult($registration);

        $this->validatorMock
            ->expects($this->once())
            ->method('validate')
            ->with($request)
            ->willReturn($validationResult);

        $this->processor->setFunc(
            static function () use ($controlResult): AcsControlResultInterface {
                return $controlResult;
            }
        );

        $response = $this->server->handle($request);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $result = $this->controlResultSerializer->deserialize($response->getBody()->__toString());

        $this->assertInstanceOf(AcsControlResultInterface::class, $result);
        $this->assertEquals(AcsControlResultInterface::STATUS_RUNNING, $result->getStatus());

        $this->assertTrue($this->logger->hasLog(LogLevel::INFO, 'ACS service success'));
    }

    public function testAcsControlRequestHandlingFailureOnInvalidRequestMethod(): void
    {
        $request = new ServerRequest(
            'GET',
            'http://platform.com/acs',
            [
                'Accept' => AcsServiceInterface::CONTENT_TYPE_CONTROL,
            ]
        );

        $response = $this->server->handle($request);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(405, $response->getStatusCode());

        $error = 'Not acceptable request method, accepts: [post]';

        $this->assertEquals($error, $response->getBody()->__toString());
        $this->assertTrue($this->logger->hasLog(LogLevel::ERROR, $error));
    }

    public function testAcsControlRequestHandlingFailureOnInvalidRequestContentType(): void
    {
        $request = new ServerRequest(
            'POST',
            'http://platform.com/acs',
            [
                'Accept' => 'invalid'
            ]
        );

        $response = $this->server->handle($request);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(406, $response->getStatusCode());

        $error = 'Not acceptable request content type, accepts: application/vnd.ims.lti-ap.v1.control+json';

        $this->assertEquals($error, $response->getBody()->__toString());
        $this->assertTrue($this->logger->hasLog(LogLevel::ERROR, $error));
    }

    public function testAcsControlRequestHandlingFailureOnInvalidRequestAccessToken(): void
    {
        $registration = $this->createTestRegistration();

        $error = 'validation error';

        $validationResult = new RequestAccessTokenValidationResult($registration, null, [], $error);

        $request = new ServerRequest(
            'POST',
            'http://platform.com/acs',
            [
                'Accept' => AcsServiceInterface::CONTENT_TYPE_CONTROL,
            ]
        );

        $this->validatorMock
            ->expects($this->once())
            ->method('validate')
            ->with($request)
            ->willReturn($validationResult);

        $response = $this->server->handle($request);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(401, $response->getStatusCode());

        $this->assertEquals($error, $response->getBody()->__toString());

        $this->assertTrue($this->logger->hasLog(LogLevel::ERROR, $error));
    }

    public function testAcsControlRequestHandlingFailureOnGenericError(): void
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

        $request = new ServerRequest(
            'POST',
            'http://platform.com/acs',
            [
                'Accept' => AcsServiceInterface::CONTENT_TYPE_CONTROL,
            ],
            $this->controlSerializer->serialize($control)
        );

        $validationResult = new RequestAccessTokenValidationResult($registration);

        $this->validatorMock
            ->expects($this->once())
            ->method('validate')
            ->with($request)
            ->willReturn($validationResult);

        $this->processor->setFunc(
            static function (): AcsControlResultInterface {
                throw new Exception('generic error');
            }
        );

        $response = $this->server->handle($request);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals('Internal ACS service error', $response->getBody()->__toString());

        $this->assertTrue($this->logger->hasLog(LogLevel::ERROR, 'generic error'));
    }

    private function createTestProcessor(callable $func = null): AcsServiceServerControlProcessorInterface
    {
        return new class ($func) implements AcsServiceServerControlProcessorInterface
        {
            /** @var callable|null */
            private $func;

            public function __construct(?callable $func)
            {
                $this->func = $func;
            }

            public function setFunc(callable $func): self
            {
                $this->func = $func;

                return $this;
            }

            public function process(RegistrationInterface $registration, AcsControlInterface $control): AcsControlResultInterface
            {
                if (null !== $this->func) {
                    return call_user_func($this->func, $registration, $control);
                }

                throw new Exception('Undefined func');
            }
        };
    }
}
