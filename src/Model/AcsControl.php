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
use OAT\Library\Lti1p3Core\Resource\LtiResourceLink\LtiResourceLinkInterface;

/**
 * @see https://www.imsglobal.org/spec/proctoring/v1p0#h.7p0gg8s6cj7h
 */
class AcsControl implements AcsControlInterface
{
    /** @var LtiResourceLinkInterface */
    private $resourceLink;

    /** @var string */
    private $userIdentifier;

    /** @var string */
    private $action;

    /** @var DateTimeInterface */
    private $incidentTime;

    /** @var int */
    private $attemptNumber;

    /** @var string|null */
    private $issuerIdentifier;

    /** @var int|null */
    private $extraTime;

    /** @var float|null */
    private $incidentSeverity;

    /** @var string|null */
    private $reasonCode;

    /** @var string|null */
    private $reasonMessage;

    public function __construct(
        LtiResourceLinkInterface $resourceLink,
        string $userIdentifier,
        string $action,
        DateTimeInterface $incidentTime,
        int $attemptNumber,
        ?string $issuerIdentifier = null,
        ?int $extraTime = null,
        ?float $incidentSeverity = null,
        ?string $reasonCode = null,
        ?string $reasonMessage = null
    ) {
        $this->resourceLink = $resourceLink;
        $this->userIdentifier = $userIdentifier;
        $this->action = $action;
        $this->incidentTime = $incidentTime;
        $this->attemptNumber = $attemptNumber;
        $this->issuerIdentifier = $issuerIdentifier;
        $this->extraTime = $extraTime;
        $this->incidentSeverity = $incidentSeverity;
        $this->reasonCode = $reasonCode;
        $this->reasonMessage = $reasonMessage;
    }

    public function getResourceLink(): LtiResourceLinkInterface
    {
        return $this->resourceLink;
    }

    public function setResourceLink(LtiResourceLinkInterface $resourceLink): AcsControlInterface
    {
        $this->resourceLink = $resourceLink;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->userIdentifier;
    }

    public function setUserIdentifier(string $userIdentifier): AcsControlInterface
    {
        $this->userIdentifier = $userIdentifier;

        return $this;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function setAction(string $action): AcsControlInterface
    {
        $this->action = $action;

        return $this;
    }

    public function getIncidentTime(): DateTimeInterface
    {
        return $this->incidentTime;
    }

    public function setIncidentTime(DateTimeInterface $incidentTime): AcsControlInterface
    {
        $this->incidentTime = $incidentTime;

        return $this;
    }

    public function getAttemptNumber(): int
    {
        return $this->attemptNumber;
    }

    public function setAttemptNumber(int $attemptNumber): AcsControlInterface
    {
        $this->attemptNumber = $attemptNumber;

        return $this;
    }

    public function getIssuerIdentifier(): ?string
    {
        return $this->issuerIdentifier;
    }

    public function setIssuerIdentifier(?string $issuerIdentifier): AcsControlInterface
    {
        $this->issuerIdentifier = $issuerIdentifier;

        return $this;
    }

    public function getExtraTime(): ?int
    {
        return $this->extraTime;
    }

    public function setExtraTime(?int $extraTime): AcsControlInterface
    {
        $this->extraTime = $extraTime;

        return $this;
    }

    public function getIncidentSeverity(): ?float
    {
        return $this->incidentSeverity;
    }

    public function setIncidentSeverity(?float $incidentSeverity): AcsControlInterface
    {
        $this->incidentSeverity = $incidentSeverity;

        return $this;
    }

    public function getReasonCode(): ?string
    {
        return $this->reasonCode;
    }

    public function setReasonCode(?string $reasonCode): AcsControlInterface
    {
        $this->reasonCode = $reasonCode;

        return $this;
    }

    public function getReasonMessage(): ?string
    {
        return $this->reasonMessage;
    }

    public function setReasonMessage(?string $reasonMessage): AcsControlInterface
    {
        $this->reasonMessage = $reasonMessage;

        return $this;
    }

    public function jsonSerialize(): array
    {
        $incidentTime = $this->incidentTime
            ? $this->incidentTime->format(DateTimeInterface::ATOM)
            : null;

        return array_filter(
            [
                'user' => [
                    'iss' => $this->issuerIdentifier,
                    'sub' => $this->userIdentifier,
                ],
                'resource_link' => [
                    'id' => $this->resourceLink->getIdentifier()
                ],
                'attempt_number' => $this->attemptNumber,
                'action' => $this->action,
                'extra_time' => $this->extraTime,
                'incident_time' => $incidentTime,
                'incident_severity' => $this->incidentSeverity,
                'reason_code' => $this->reasonCode,
                'reason_message' => $this->reasonMessage
            ]
        );
    }
}
