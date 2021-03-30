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
            $resourceLink = new LtiResourceLink($data['resource_link']['id'], $data['resource_link']);

            return new AcsControl(
                $resourceLink,
                $data['user']['sub'],
                $data['action'],
                new Carbon($data['incident_time']),
                $data['attempt_number'],
                $data['user']['iss'],
                intval($data['extra_time']),
                floatval($data['incident_severity']),
                $data['reason_code'],
                $data['reason_msg']
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
