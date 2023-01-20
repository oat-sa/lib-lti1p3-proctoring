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

namespace OAT\Library\Lti1p3Proctoring\Model;

use InvalidArgumentException;

/**
 * @see https://www.imsglobal.org/spec/proctoring/v1p0#h.r9n0nket2gul
 */
class AcsControlResult implements AcsControlResultInterface
{
    /** @var string */
    private $status;

    /** @var int|null */
    private $extraTime;

    public function __construct(string $status, ?int $extraTime = null)
    {
        $this->setStatus($status);

        $this->extraTime = $extraTime;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function setStatus(string $status): AcsControlResultInterface
    {
        if (!in_array($status, self::SUPPORTED_STATUSES)) {
            throw new InvalidArgumentException(sprintf('Control result status %s is not supported', $status));
        }

        $this->status = $status;

        return $this;
    }

    public function getExtraTime(): ?int
    {
        return $this->extraTime;
    }

    public function setExtraTime(?int $extraTime): AcsControlResultInterface
    {
        $this->extraTime = $extraTime;

        return $this;
    }

    public function jsonSerialize(): array
    {
        return array_filter(
            [
                'status' => $this->status,
                'extra_time' => $this->extraTime,
            ],
            static fn($element) => $element !== null
        );
    }
}
