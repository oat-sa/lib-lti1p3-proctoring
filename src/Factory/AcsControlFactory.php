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

namespace OAT\Library\Lti1p3Proctoring\Factory;

use Carbon\Carbon;
use InvalidArgumentException;
use OAT\Library\Lti1p3Core\Exception\LtiException;
use OAT\Library\Lti1p3Core\Exception\LtiExceptionInterface;
use OAT\Library\Lti1p3Core\Resource\LtiResourceLink\LtiResourceLink;
use OAT\Library\Lti1p3Proctoring\Model\AcsControl;
use OAT\Library\Lti1p3Proctoring\Model\AcsControlInterface;
use Throwable;

class AcsControlFactory implements AcsControlFactoryInterface
{
    /**
     * @throws LtiExceptionInterface
     */
    public function create(array $data): AcsControlInterface
    {
        try {
            $resourceLinkData = $data['resource_link'] ?? null;

            if (null === $resourceLinkData) {
                throw new InvalidArgumentException('Missing mandatory resource_link');
            }

            $resourceLinkIdentifier = $resourceLinkData['id'] ?? null;

            if (null === $resourceLinkIdentifier) {
                throw new InvalidArgumentException('Missing mandatory resource_link.id');
            }

            $resourceLink = new LtiResourceLink(
                $resourceLinkIdentifier,
                [
                    'title' => $resourceLinkData['title'] ?? null,
                    'text' => $resourceLinkData['description'] ?? null,
                ]);

            $userData = $data['user'] ?? null;

            if (null === $userData) {
                throw new InvalidArgumentException('Missing mandatory user');
            }

            $userIdentifier = $userData['sub'] ?? null;

            if (null === $userIdentifier) {
                throw new InvalidArgumentException('Missing mandatory user.sub');
            }

            $issuerIdentifier = $userData['iss'] ?? null;

            if (null === $issuerIdentifier) {
                throw new InvalidArgumentException('Missing mandatory user.iss');
            }

            $action = $data['action'] ?? null;

            if (!in_array($action, AcsControlInterface::SUPPORTED_ACTIONS)) {
                throw new InvalidArgumentException('Invalid action');
            }

            $attemptNumber = $data['attempt_number'] ?? null;

            if (null === $attemptNumber) {
                throw new InvalidArgumentException('Missing mandatory attempt_number');
            }

            $incidentTime = $data['incident_time'] ?? null;

            if (null === $incidentTime) {
                throw new InvalidArgumentException('Missing mandatory incident_time');
            }

            $extraTime = $data['extra_time'] ?? null;
            $incidentSeverity = $data['incident_severity'] ?? null;

            return new AcsControl(
                $resourceLink,
                $userIdentifier,
                $action,
                new Carbon($incidentTime),
                $attemptNumber,
                $issuerIdentifier,
                $extraTime ? intval($extraTime) : null,
                $incidentSeverity ? floatval($incidentSeverity) : null,
                $data['reason_code'] ?? null,
                $data['reason_msg'] ?? null
            );
        } catch (Throwable $exception) {
            throw new LtiException(
                sprintf('Cannot create ACS control: %s', $exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        }
    }
}
