# ACS Tool - Assessment Control Service client

> How to use the [AcsServiceClient](../../src/Service/Client/AcsServiceClient.php) to perform authenticated ACS service calls as a tool.

## Table of contents

- [Features](#features)
- [Usage](#usage)

## Features

This library provides a [AcsServiceClient](../../src/Service/Client/AcsServiceClient.php) (based on the [core LtiServiceClient](https://github.com/oat-sa/lib-lti1p3-core/blob/master/doc/service/service-client.md)) that allow sending ACS controls to a platform.

You can use:
- `sendControlForPayload()` to [send ACS control](https://www.imsglobal.org/spec/proctoring/v1p0#h.awao2i3cnvsy) from a received LTI message payload
- `sendControl()` to [send ACS control](https://www.imsglobal.org/spec/proctoring/v1p0#h.awao2i3cnvsy) for a given ACS service url

## Usage

To send an ACS control:

```php
<?php

use OAT\Library\Lti1p3Core\Message\Payload\LtiMessagePayloadInterface;
use OAT\Library\Lti1p3Core\Registration\RegistrationRepositoryInterface;
use OAT\Library\Lti1p3Proctoring\Model\AcsControl;
use OAT\Library\Lti1p3Proctoring\Model\AcsControlResultInterface;
use OAT\Library\Lti1p3Proctoring\Service\Client\AcsServiceClient;

// Related registration
/** @var RegistrationRepositoryInterface $registrationRepository */
$registration = $registrationRepository->find(...);

// Related LTI 1.3 message payload
/** @var LtiMessagePayloadInterface $payload */
$payload  = ...;

$acsClient = new AcsServiceClient();

$control = new AcsControl(...);

/** @var AcsControlResultInterface $controlResult */
$controlResult = $acsClient->sendControlForPayload(
    $registration,  // [required] as the tool, it will call the platform of this registration
    $control,       // [required] with provided ACS control
    $payload        // [required] from the LTI message payload containing the ACS claim (got at LTI launch)

);

// or you also can call directly for an given URL (avoid claim construction)
/** @var AcsControlResultInterface $controlResult */
$controlResult = $acsClient->sendControl(
    $registration,              // [required] as the tool, it will call the platform of this registration
    $control,                   // [required] with provided ACS control
    'https://platform.com/acs'  // [required] to a given ACS service url
);

// Control result status
echo $controlResult->getStatus();

// Control result extra time (if given)
echo $controlResult->getExtraTime();
```
