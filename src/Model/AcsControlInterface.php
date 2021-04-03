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

use DateTimeInterface;
use JsonSerializable;
use OAT\Library\Lti1p3Core\Resource\LtiResourceLink\LtiResourceLinkInterface;

/**
 * @see https://www.imsglobal.org/spec/proctoring/v1p0#h.7p0gg8s6cj7h
 */
interface AcsControlInterface extends JsonSerializable
{
    public const ACTION_PAUSE = 'pause';
    public const ACTION_RESUME = 'resume';
    public const ACTION_TERMINATE = 'terminate';
    public const ACTION_UPDATE = 'update';
    public const ACTION_FLAG = 'flag';

    public const SUPPORTED_ACTIONS = [
        self::ACTION_PAUSE,
        self::ACTION_RESUME,
        self::ACTION_TERMINATE,
        self::ACTION_UPDATE,
        self::ACTION_FLAG,
    ];

    public function getResourceLink(): LtiResourceLinkInterface;

    public function setResourceLink(LtiResourceLinkInterface $resourceLink): AcsControlInterface;

    public function getUserIdentifier(): string;

    public function setUserIdentifier(string $userIdentifier): AcsControlInterface;

    public function getAction(): string;

    public function setAction(string $action): AcsControlInterface;

    public function getIncidentTime(): DateTimeInterface;

    public function setIncidentTime(DateTimeInterface $incidentTime): AcsControlInterface;

    public function getAttemptNumber(): int;

    public function setAttemptNumber(int $attemptNumber): AcsControlInterface;

    public function getIssuerIdentifier(): ?string;

    public function setIssuerIdentifier(?string $issuerIdentifier): AcsControlInterface;

    public function getExtraTime(): ?int;

    public function setExtraTime(?int $extraTime): AcsControlInterface;

    public function getIncidentSeverity(): ?float;

    public function setIncidentSeverity(?float $incidentSeverity): AcsControlInterface;

    public function getReasonCode(): ?string;

    public function setReasonCode(?string $reasonCode): AcsControlInterface;

    public function getReasonMessage(): ?string;

    public function setReasonMessage(?string $reasonMessage): AcsControlInterface;
}
