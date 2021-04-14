# LTI 1.3 Proctoring Library

[![Latest Version](https://img.shields.io/github/tag/oat-sa/lib-lti1p3-proctoring.svg?style=flat&label=release)](https://github.com/oat-sa/lib-lti1p3-proctoring/tags)
[![License GPL2](http://img.shields.io/badge/licence-GPL%202.0-blue.svg)](http://www.gnu.org/licenses/gpl-2.0.html)
[![Build Status](https://github.com/oat-sa/lib-lti1p3-proctoring/actions/workflows/build.yaml/badge.svg?branch=main)](https://github.com/oat-sa/lib-lti1p3-proctoring/actions)
[![Test Coverage Status](https://coveralls.io/repos/github/oat-sa/lib-lti1p3-proctoring/badge.svg?branch=main)](https://coveralls.io/github/oat-sa/lib-lti1p3-proctoring?branch=main)
[![Psalm Level Status](https://shepherd.dev/github/oat-sa/lib-lti1p3-proctoring/level.svg)](https://shepherd.dev/github/oat-sa/lib-lti1p3-proctoring)
[![Packagist Downloads](http://img.shields.io/packagist/dt/oat-sa/lib-lti1p3-proctoring.svg)](https://packagist.org/packages/oat-sa/lib-lti1p3-proctoring)

> PHP library for [LTI 1.3 Proctoring](https://www.imsglobal.org/spec/proctoring/v1p0) implementations as platforms and / or as tools, based on [LTI 1.3 Core library](https://github.com/oat-sa/lib-lti1p3-core).

# Table of contents

- [Specifications](#specifications)
- [Installation](#installation)
- [Tutorials](#tutorials)
- [Tests](#tests)

## Specifications

- [IMS LTI 1.3 Proctoring](https://www.imsglobal.org/spec/proctoring/v1p0)
- [IMS LTI 1.3 Core](http://www.imsglobal.org/spec/lti/v1p3)
- [IMS Security](https://www.imsglobal.org/spec/security/v1p0)

## Installation

```console
$ composer require oat-sa/lib-lti1p3-proctoring
```

## Tutorials

You can then find below usage tutorials, presented by topics.

### Configuration

- how to [configure the underlying LTI 1.3 Core library](https://github.com/oat-sa/lib-lti1p3-core#quick-start)

### Messages

- how to [implement the proctoring messages workflow (for platform and / or tool)](doc/message/proctoring-workflow.md)

### Services

- how to [use the library for ACS as a tool](doc/service/tool.md)
- how to [use the library for ACS as a platform](doc/service/platform.md)

## Tests

To run tests:

```console
$ vendor/bin/phpunit
```
**Note**: see [phpunit.xml.dist](phpunit.xml.dist) for available test suites.
