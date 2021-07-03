# <img src="doc/images/logo/logo.png" width="40" height="40"> [TAO](https://www.taotesting.com/) - LTI 1.3 Proctoring Library

[![Latest Version](https://img.shields.io/github/tag/oat-sa/lib-lti1p3-proctoring.svg?style=flat&label=release)](https://github.com/oat-sa/lib-lti1p3-proctoring/tags)
[![License GPL2](http://img.shields.io/badge/licence-GPL%202.0-blue.svg)](http://www.gnu.org/licenses/gpl-2.0.html)
[![Build Status](https://github.com/oat-sa/lib-lti1p3-proctoring/actions/workflows/build.yaml/badge.svg?branch=main)](https://github.com/oat-sa/lib-lti1p3-proctoring/actions)
[![Test Coverage Status](https://coveralls.io/repos/github/oat-sa/lib-lti1p3-proctoring/badge.svg?branch=main)](https://coveralls.io/github/oat-sa/lib-lti1p3-proctoring?branch=main)
[![Psalm Level Status](https://shepherd.dev/github/oat-sa/lib-lti1p3-proctoring/level.svg)](https://shepherd.dev/github/oat-sa/lib-lti1p3-proctoring)
[![Packagist Downloads](http://img.shields.io/packagist/dt/oat-sa/lib-lti1p3-proctoring.svg)](https://packagist.org/packages/oat-sa/lib-lti1p3-proctoring)
[![IMS Certified](https://img.shields.io/badge/IMS-certified-brightgreen)](https://site.imsglobal.org/certifications/open-assessment-technologies-sa/tao-lti-13-devkit)

> [IMS certified](https://site.imsglobal.org/certifications/open-assessment-technologies-sa/tao-lti-13-devkit) PHP library for [LTI 1.3 Proctoring](https://www.imsglobal.org/spec/proctoring/v1p0) implementations as [platforms and / or as tools](http://www.imsglobal.org/spec/lti/v1p3/#platforms-and-tools), based on [LTI 1.3 Core library](https://github.com/oat-sa/lib-lti1p3-core).

# Table of contents

- [TAO LTI 1.3 PHP framework](#tao-lti-13-php-framework)
- [IMS](#ims)
- [Installation](#installation)
- [Documentation](#documentation)
- [Tests](#tests)

## TAO LTI 1.3 PHP framework

This library is part of the [TAO LTI 1.3 PHP framework](https://oat-sa.github.io/doc-lti1p3/).

## IMS

You can find below [IMS](https://www.imsglobal.org/) related information.

### Related certifications

- [LTI 1.3 proctoring services](https://site.imsglobal.org/certifications/open-assessment-technologies-sa/tao-lti-13-devkit)

### Related specifications

- [IMS LTI 1.3 Proctoring](https://www.imsglobal.org/spec/proctoring/v1p0)
- [IMS LTI 1.3 Core](http://www.imsglobal.org/spec/lti/v1p3)
- [IMS Security](https://www.imsglobal.org/spec/security/v1p0)

## Installation

```console
$ composer require oat-sa/lib-lti1p3-proctoring
```

## Documentation

You can find below the library documentation, presented by topics.

### Configuration

- how to [configure the underlying LTI 1.3 Core library](https://github.com/oat-sa/lib-lti1p3-core#quick-start)

### Messages

- how to [implement the proctoring messages workflow (as platform and / or tool)](doc/message/proctoring-workflow.md)

### Services

- how to [use the library for ACS as a platform](doc/service/platform.md)
- how to [use the library for ACS as a tool](doc/service/tool.md)

## Tests

To run tests:

```console
$ vendor/bin/phpunit
```
**Note**: see [phpunit.xml.dist](phpunit.xml.dist) for available test suites.
