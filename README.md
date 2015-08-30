# Yii2-Account

This is a hard fork of [dektrium/yii2-user](https://github.com/dektrium/yii2-user). Most noteable changes are the module, models, and tables now referrer to Account instead of User. And all primary keys are binary(16) uuids.

## From yii2-user

Most of web applications provide a way for users to register, log in or reset
their forgotten passwords. Rather than re-implementing this on each application,
you can use Yii2-user which is a flexible user management module for Yii2 that
handles common tasks such as registration, authentication and password retrieval.
The latest version includes following features:

* Registration with an optional confirmation per mail
* Registration via social networks
* Password recovery
* Account and profile management
* Console commands
* User management interface

> **NOTE:** Module is in initial development. Anything may change at any time.

## Documentation

[Installation instructions](docs/getting-started.md)

## License

Yii2-user is released under the MIT License. See the bundled [LICENSE.md](LICENSE.md)
for details.
