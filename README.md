# zend-mvc-i18n

[![Build Status](https://secure.travis-ci.org/zendframework/zend-mvc-i18n.svg?branch=master)](https://secure.travis-ci.org/zendframework/zend-mvc-i18n)
[![Coverage Status](https://coveralls.io/repos/zendframework/zend-mvc-i18n/badge.svg?branch=master)](https://coveralls.io/r/zendframework/zend-mvc-i18n?branch=master)

zend-mvc-i18n provides integration between:

- zend-i18n
- zend-mvc
- zend-router

and replaces the i18n functionality found in the v2 releases of the latter
two components.

- File issues at https://github.com/zendframework/zend-mvc-i18n/issues
- Documentation is at https://zendframework.github.io/zend-mvc-i18n/

## Installation

```console
$ composer require zendframework/zend-mvc-i18n
```

Assuming you are using the [component installer](https://zendframework.github.io/zend-component-installer],
doing so will enable the component in your application, allowing you to
immediately start developing console applications via your MVC. If you are not,
please read the [introduction](https://zendframework.github.io/zend-mvc-i18n/intro/)
for details on how to register the functionality with your application.

## For use with zend-mvc v3 and up

While this component has an initial stable release, please do not use it with
zend-mvc releases prior to v3, as it is not compatible.

## Migrating from zend-mvc v2 i18n features to zend-mvc-i18n

Please see the [migration guide](http://zendframework.github.io/zend-mvc-i18n/migration/v2-to-v3/)
for details on how to migrate your existing zend-mvc console functionality to 
the features exposed by this component.
