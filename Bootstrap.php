<?php

/*
 * This file is part of the Dektrium project.
 *
 * (c) Dektrium project <http://github.com/dektrium/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace wartron\yii2account;

use Yii;
use yii\authclient\Collection;
use yii\base\BootstrapInterface;
use yii\console\Application as ConsoleApplication;
use yii\i18n\PhpMessageSource;
use yii\web\GroupUrlRule;

/**
 * Bootstrap class registers module and account application component. It also creates some url rules which will be applied
 * when UrlManager.enablePrettyUrl is enabled.
 *
 * @author Dmitry Erofeev <dmeroff@gmail.com>
 */
class Bootstrap implements BootstrapInterface
{
    /** @var array Model's map */
    private $_modelMap = [
        'Account'           =>  'wartron\yii2account\models\Account',
        'AccountSearch'     =>  'wartron\yii2account\models\AccountSearch',
        'AccountNetwork'    =>  'wartron\yii2account\models\AccountNetwork',
        'Profile'           =>  'wartron\yii2account\models\Profile',
        'Token'             =>  'wartron\yii2account\models\Token',
        'RegistrationForm'  =>  'wartron\yii2account\models\RegistrationForm',
        'ResendForm'        =>  'wartron\yii2account\models\ResendForm',
        'LoginForm'         =>  'wartron\yii2account\models\LoginForm',
        'SettingsForm'      =>  'wartron\yii2account\models\SettingsForm',
        'RecoveryForm'      =>  'wartron\yii2account\models\RecoveryForm',
    ];

    /** @inheritdoc */
    public function bootstrap($app)
    {
        /** @var Module $module */
        /** @var \yii\db\ActiveRecord $modelName */
        if ($app->hasModule('account') && ($module = $app->getModule('account')) instanceof Module) {
            $this->_modelMap = array_merge($this->_modelMap, $module->modelMap);
            foreach ($this->_modelMap as $name => $definition) {
                $class = "wartron\\yii2account\\models\\" . $name;
                Yii::$container->set($class, $definition);
                $modelName = is_array($definition) ? $definition['class'] : $definition;
                $module->modelMap[$name] = $modelName;
                if (in_array($name, ['Account', 'AccountNetwork', 'Profile', 'Token'])) {
                    Yii::$container->set($name . 'Query', function () use ($modelName) {
                        return $modelName::find();
                    });
                }
            }
            Yii::$container->setSingleton(Finder::className(), [
                'accountQuery' => Yii::$container->get('AccountQuery'),
                'accountNetworkQuery' => Yii::$container->get('AccountNetworkQuery'),
                'profileQuery' => Yii::$container->get('ProfileQuery'),
                'tokenQuery'   => Yii::$container->get('TokenQuery'),
            ]);

            if ($app instanceof ConsoleApplication) {
                $module->controllerNamespace = 'wartron\yii2account\commands';
            } else {
                Yii::$container->set('yii\web\User', [
                    'enableAutoLogin' => true,
                    'loginUrl'        => ['/account/security/login'],
                    'identityClass'   => $module->modelMap['Account'],
                ]);

                $configUrlRule = [
                    'prefix' => $module->urlPrefix,
                    'rules'  => $module->urlRules,
                ];

                if ($module->urlPrefix != 'account') {
                    $configUrlRule['routePrefix'] = 'account';
                }

                $app->urlManager->addRules([new GroupUrlRule($configUrlRule)], false);

                if (!$app->has('authClientCollection')) {
                    $app->set('authClientCollection', [
                        'class' => Collection::className(),
                    ]);
                }
            }

            if (!isset($app->get('i18n')->translations['account*'])) {
                $app->get('i18n')->translations['account*'] = [
                    'class'    => PhpMessageSource::className(),
                    'basePath' => __DIR__ . '/messages',
                ];
            }

            $defaults = [
                'welcomeSubject'        => Yii::t('account', 'Welcome to {0}', Yii::$app->name),
                'confirmationSubject'   => Yii::t('account', 'Confirm account on {0}', Yii::$app->name),
                'reconfirmationSubject' => Yii::t('account', 'Confirm email change on {0}', Yii::$app->name),
                'recoverySubject'       => Yii::t('account', 'Complete password reset on {0}', Yii::$app->name),
            ];

            Yii::$container->set('wartron\yii2account\Mailer', array_merge($defaults, $module->mailer));
        }
    }
}
