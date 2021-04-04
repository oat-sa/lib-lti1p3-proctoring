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

use OAT\Library\Lti1p3Core\Exception\LtiException;
use OAT\Library\Lti1p3Core\Exception\LtiExceptionInterface;
use OAT\Library\Lti1p3Proctoring\Model\AcsControlResult;
use OAT\Library\Lti1p3Proctoring\Model\AcsControlResultInterface;

class AcsControlResultFactory implements AcsControlResultFactoryInterface
{
    /**
     * @throws LtiExceptionInterface
     */
    public function create(array $data): AcsControlResultInterface
    {
        $status = $data['status'] ?? null;

        if (null === $status || !in_array($status, AcsControlResultInterface::SUPPORTED_STATUSES)) {
            throw new LtiException('Cannot create ACS control result: Invalid status');
        }

        $extraTime = $data['extra_time'] ?? null;

        return new AcsControlResult($status, $extraTime ? intval($extraTime) : null);
    }
}
