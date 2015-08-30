# Getting started with Yii2-account

Yii2-account is designed to work out of the box. It means that installation requires
minimal steps. Only one configuration step should be taken and you are ready to
have account management on your Yii2 website.

> If you're using Yii2 advanced template, you should read [this article](usage-with-advanced-template.md) firstly.

### 1. Download

Yii2-account can be installed using composer. Run following command to download and
install Yii2-account:

```bash
composer require "wartron/yii2-account"
```

### 2. Configure

> **NOTE:** Make sure that you don't have `account` component configuration in your config files.

Add following lines to your main configuration file:

```php
'components' => [
    'formatter' => [
        'class' => 'wartron\yii2helpers\formatter\Formatter'
    ],
],
'modules' => [
    'account' => [
        'class' => 'wartron\yii2account\Module',
    ],
],
```

### 3. Update database schema

The last thing you need to do is updating your database schema by applying the
migrations. Make sure that you have properly configured `db` application component
and run the following command:

```bash
$ php yii migrate/up --migrationPath=@vendor/wartron/yii2-account/migrations
```

## Where do I go now?

You have Yii2-account installed. Now you can check out the [list of articles](README.md)
for more information.

## Troubleshooting

If you're having troubles with Yii2-account, make sure to check out the
[troubleshooting guide](troubleshooting.md).
