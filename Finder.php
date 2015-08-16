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

use wartron\yii2account\models\query\AccountQuery;
use wartron\yii2account\models\Token;
use yii\authclient\ClientInterface;
use yii\base\Object;
use yii\db\ActiveQuery;

/**
 * Finder provides some useful methods for finding active record models.
 *
 * @author Dmitry Erofeev <dmeroff@gmail.com>
 */
class Finder extends Object
{
    /** @var ActiveQuery */
    protected $accountQuery;

    /** @var AccountQuery */
    protected $accountNetworkQuery;

    /** @var ActiveQuery */
    protected $tokenQuery;

    /** @var ActiveQuery */
    protected $profileQuery;

    /**
     * @return ActiveQuery
     */
    public function getAccountQuery()
    {
        return $this->accountQuery;
    }

    /**
     * @return ActiveQuery
     */
    public function getAccountNetworkQuery()
    {
        return $this->accountNetworkQuery;
    }

    /**
     * @return ActiveQuery
     */
    public function getTokenQuery()
    {
        return $this->tokenQuery;
    }

    /**
     * @return ActiveQuery
     */
    public function getProfileQuery()
    {
        return $this->profileQuery;
    }

    /** @param ActiveQuery $accountQuery */
    public function setAccountQuery(ActiveQuery $accountQuery)
    {
        $this->accountQuery = $accountQuery;
    }

    /** @param ActiveQuery $accountNetworkQuery */
    public function setAccountNetworkQuery(ActiveQuery $accountNetworkQuery)
    {
        $this->accountNetworkQuery = $accountNetworkQuery;
    }

    /** @param ActiveQuery $tokenQuery */
    public function setTokenQuery(ActiveQuery $tokenQuery)
    {
        $this->tokenQuery = $tokenQuery;
    }

    /** @param ActiveQuery $profileQuery */
    public function setProfileQuery(ActiveQuery $profileQuery)
    {
        $this->profileQuery = $profileQuery;
    }

    /**
     * Finds a user by the given id.
     *
     * @param int $id User id to be used on search.
     *
     * @return models\User
     */
    public function findAccountById($id)
    {
        return $this->findAccount(['id' => $id])->one();
    }

    /**
     * Finds a user by the given username.
     *
     * @param string $username Username to be used on search.
     *
     * @return models\User
     */
    public function findAccountByUsername($username)
    {
        return $this->findAccount(['username' => $username])->one();
    }

    /**
     * Finds a user by the given email.
     *
     * @param string $email Email to be used on search.
     *
     * @return models\User
     */
    public function findAccountByEmail($email)
    {
        return $this->findAccount(['email' => $email])->one();
    }

    /**
     * Finds a user by the given username or email.
     *
     * @param string $usernameOrEmail Username or email to be used on search.
     *
     * @return models\User
     */
    public function findAccountByUsernameOrEmail($usernameOrEmail)
    {
        if (filter_var($usernameOrEmail, FILTER_VALIDATE_EMAIL)) {
            return $this->findAccountByEmail($usernameOrEmail);
        }

        return $this->findAccountByUsername($usernameOrEmail);
    }

    /**
     * Finds a user by the given condition.
     *
     * @param mixed $condition Condition to be used on search.
     *
     * @return \yii\db\ActiveQuery
     */
    public function findAccount($condition)
    {
        return $this->accountQuery->where($condition);
    }

    /**
     * @return AccountQuery
     */
    public function findAccountNetwork()
    {
        return $this->accountNetworkQuery;
    }

    /**
     * Finds an account by id.
     *
     * @param int $id
     *
     * @return models\Account|null
     */
    public function findAccountNetworkById($id)
    {
        return $this->accountNetworkQuery->where(['id' => $id])->one();
    }

    /**
     * Finds a token by user id and code.
     *
     * @param mixed $condition
     *
     * @return ActiveQuery
     */
    public function findToken($condition)
    {
        return $this->tokenQuery->where($condition);
    }

    /**
     * Finds a token by params.
     *
     * @param integer $accountId
     * @param string  $code
     * @param integer $type
     *
     * @return Token
     */
    public function findTokenByParams($accountId, $code, $type)
    {
        return $this->findToken([
            'account_id' => $accountId,
            'code'    => $code,
            'type'    => $type,
        ])->one();
    }

    /**
     * Finds a profile by user id.
     *
     * @param int $id
     *
     * @return null|models\Profile
     */
    public function findProfileById($id)
    {
        return $this->findProfile(['account_id' => $id])->one();
    }

    /**
     * Finds a profile.
     *
     * @param mixed $condition
     *
     * @return \yii\db\ActiveQuery
     */
    public function findProfile($condition)
    {
        return $this->profileQuery->where($condition);
    }
}
