# ACS Platform - Assessment Control Service server

> How to use the [AcsServiceServerRequestHandler](../../src/Service/Server/Handler/AcsServiceServerRequestHandler.php) (with the core [LtiServiceServer](https://github.com/oat-sa/lib-lti1p3-core/blob/master/src/Service/Server/LtiServiceServer.php)) to serve authenticated ACS service calls as a platform.

## Table of contents

- [Features](#features)
- [Usage](#usage)

## Features

This library provides a [AcsServiceServerRequestHandler](../../src/Service/Server/Handler/AcsServiceServerRequestHandler.php) ready to be use with the core [LtiServiceServer](https://github.com/oat-sa/lib-lti1p3-core/blob/master/src/Service/Server/LtiServiceServer.php) to handle assessment control requests.

- it accepts a [PSR7 ServerRequestInterface](https://www.php-fig.org/psr/psr-7/#321-psrhttpmessageserverrequestinterface),
- leverages the [required IMS LTI 1.3 service authentication](https://www.imsglobal.org/spec/security/v1p0/#securing_web_services),
- and returns a [PSR7 ResponseInterface](https://www.php-fig.org/psr/psr-7/#33-psrhttpmessageresponseinterface) containing the `membership` representation

## Usage

First, you need to provide a [AcsServiceServerControlProcessorInterface](../../src/Service/Server/Processor/AcsServiceServerControlProcessorInterface.php) implementation, in charge to process the ACS control requests.

```php
<?php

use OAT\Library\Lti1p3Core\Registration\RegistrationInterface;
use OAT\Library\Lti1p3Proctoring\Model\AcsControlInterface;
use OAT\Library\Lti1p3Proctoring\Model\AcsControlResultInterface;
use OAT\Library\Lti1p3Proctoring\Service\Server\Handler\AcsServiceServerControlProcessorInterface;

/** @var AcsServiceServerControlProcessorInterface $processor */
$processor = new class() implements AcsServiceServerControlProcessorInterface 
{
    public function process(
        RegistrationInterface $registration,
        AcsControlInterface $control
    ) : AcsControlResultInterface {
        // TODO: Implement process() method.
    }
};
```

Then:
- you can construct the [AcsServiceServerRequestHandler](../../src/Service/Server/Handler/AcsServiceServerRequestHandler.php) (constructed with your [AcsServiceServerControlProcessorInterface](../../src/Service/Server/Processor/AcsServiceServerControlProcessorInterface.php) implementation)
- to finally expose it to requests using the core [LtiServiceServer](https://github.com/oat-sa/lib-lti1p3-core/blob/master/src/Service/Server/LtiServiceServer.php) (constructed with the [RequestAccessTokenValidator](https://github.com/oat-sa/lib-lti1p3-core/blob/master/src/Security/OAuth2/Validator/RequestAccessTokenValidator.php), from core library)

```php
<?php

use OAT\Library\Lti1p3Core\Registration\RegistrationRepositoryInterface;
use OAT\Library\Lti1p3Core\Security\OAuth2\Validator\RequestAccessTokenValidator;
use OAT\Library\Lti1p3Core\Service\Server\LtiServiceServer;
use OAT\Library\Lti1p3Proctoring\Service\Server\Handler\AcsServiceServerControlProcessorInterface;
use OAT\Library\Lti1p3Proctoring\Service\Server\Handler\AcsServiceServerRequestHandler;
use Psr\Http\Message\ServerRequestInterface;

/** @var ServerRequestInterface $request */
$request = ...

/** @var RegistrationRepositoryInterface $repository */
$repository = ...

/** @var AcsServiceServerControlProcessorInterface $processor */
$processor = ...

$validator = new RequestAccessTokenValidator($repository);

$handler = new AcsServiceServerRequestHandler($processor);

$server = new LtiServiceServer($validator, $handler);

// Generates an authenticated response containing the control result representation
$response = $server->handle($request);
```
