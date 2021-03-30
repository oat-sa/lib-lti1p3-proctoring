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

namespace OAT\Library\Lti1p3Proctoring\Serializer;

use OAT\Library\Lti1p3Core\Exception\LtiException;
use OAT\Library\Lti1p3Core\Exception\LtiExceptionInterface;
use OAT\Library\Lti1p3Proctoring\Factory\AcsControlFactory;
use OAT\Library\Lti1p3Proctoring\Factory\AcsControlFactoryInterface;
use OAT\Library\Lti1p3Proctoring\Model\AcsControlInterface;

class AcsControlSerializer implements AcsControlSerializerInterface
{
    /** @var AcsControlFactoryInterface */
    private $factory;

    public function __construct(?AcsControlFactoryInterface $factory = null)
    {
        $this->factory = $factory ?? new AcsControlFactory();
    }

    public function serialize(AcsControlInterface $control): string
    {
        return json_encode($control);
    }

    /**
     * @throws LtiExceptionInterface
     */
    public function deserialize(string $data): AcsControlInterface
    {
        $data = json_decode($data, true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new LtiException(
                sprintf('Error during ACS control deserialization: %s', json_last_error_msg())
            );
        }

        return $this->factory->create($data);
    }
}
