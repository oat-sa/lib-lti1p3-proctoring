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

namespace OAT\Library\Lti1p3Proctoring\Service;

/**
 * @see https://www.imsglobal.org/spec/proctoring/v1p0#h.awao2i3cnvsy
 */
interface AcsServiceInterface
{
    public const NAME = 'ACS';
    public const AUTHORIZATION_SCOPE_CONTROL = 'https://purl.imsglobal.org/spec/lti-ap/scope/control.all';
    public const CONTENT_TYPE_CONTROL = 'application/vnd.ims.lti-ap.v1.control+json';
}
