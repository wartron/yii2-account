<?php

/*
 * This file is part of the Dektrium project
 *
 * (c) Dektrium project <http://github.com/dektrium>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace wartron\yii2account\clients;

use yii\authclient\clients\Twitter as BaseTwitter;
use yii\helpers\ArrayHelper;

/**
 * @author Dmitry Erofeev <dmeroff@gmail.com>
 */
class Twitter extends BaseTwitter implements ClientInterface
{
    /**
     * @return string
     */
    public function getUsername()
    {
        return ArrayHelper::getValue($this->getUserAttributes(), 'screen_name');
    }

    /**
     * @return null Twitter does not provide user's email address
     */
    public function getEmail()
    {
        return null;
    }
}
