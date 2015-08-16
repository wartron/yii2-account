<?php

/*
 * This file is part of the Dektrium project.
 *
 * (c) Dektrium project <http://github.com/dektrium/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace wartron\yii2account\models\query;

use wartron\yii2account\models\Account;
use yii\authclient\ClientInterface;
use yii\db\ActiveQuery;

/**
 * @method Account|null one($db = null)
 * @method Account[]    all($db = null)
 *
 * @author Dmitry Erofeev <dmeroff@gmail.com>
 */
class AccountNetworkQuery extends ActiveQuery
{
    /**
     * Finds an account by code.
     * @param  string       $code
     * @return AccountNetworkQuery
     */
    public function byCode($code)
    {
        return $this->andWhere(['code' => md5($code)]);
    }

    /**
     * Finds an account by id.
     * @param  integer      $id
     * @return AccountNetworkQuery
     */
    public function byId($id)
    {
        return $this->andWhere(['id' => $id]);
    }

    /**
     * Finds an account by user_id.
     * @param  integer      $userId
     * @return AccountNetworkQuery
     */
    public function byUser($userId)
    {
        return $this->andWhere(['user_id' => $userId]);
    }

    /**
     * Finds an account by client.
     * @param  ClientInterface $client
     * @return AccountNetworkQuery
     */
    public function byClient(ClientInterface $client)
    {
        return $this->andWhere([
            'provider'  => $client->getId(),
            'client_id' => $client->getUserAttributes()['id'],
        ]);
    }
}