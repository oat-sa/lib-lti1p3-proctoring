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
use JsonSerializable;

/**
 * @see https://www.imsglobal.org/spec/proctoring/v1p0#h.r9n0nket2gul
 */
interface AcsControlResultInterface extends JsonSerializable
{
    public const STATUS_NONE = 'none';
    public const STATUS_RUNNING = 'running';
    public const STATUS_PAUSED = 'paused';
    public const STATUS_TERMINATED = 'terminated';
    public const STATUS_COMPLETE = 'complete';

    public const SUPPORTED_STATUSES = [
        self::STATUS_NONE,
        self::STATUS_RUNNING,
        self::STATUS_PAUSED,
        self::STATUS_TERMINATED,
        self::STATUS_COMPLETE,
    ];

    public function getStatus(): string;

    /**
     * @throws InvalidArgumentException
     */
    public function setStatus(string $status): AcsControlResultInterface;

    public function getExtraTime(): ?int;

    public function setExtraTime(?int $extraTime): AcsControlResultInterface;
}
