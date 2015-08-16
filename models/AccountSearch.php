<?php

/*
 * This file is part of the Dektrium project.
 *
 * (c) Dektrium project <http://github.com/dektrium/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace wartron\yii2account\models;

use wartron\yii2account\Finder;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * AccountSearch represents the model behind the search form about User.
 */
class AccountSearch extends Model
{
    /** @var int */
    public $type;

    /** @var string */
    public $username;

    /** @var string */
    public $email;

    /** @var int */
    public $created_at;

    /** @var string */
    public $registration_ip;

    /** @var Finder */
    protected $finder;

    /**
     * @param Finder $finder
     * @param array  $config
     */
    public function __construct(Finder $finder, $config = [])
    {
        $this->finder = $finder;
        parent::__construct($config);
    }

    /** @inheritdoc */
    public function rules()
    {
        return [
            'fieldsSafe' => [['type','username', 'email', 'registration_ip', 'created_at'], 'safe'],
            'createdDefault' => ['created_at', 'default', 'value' => null],
        ];
    }

    /** @inheritdoc */
    public function attributeLabels()
    {
        return [
            'type'              =>  Yii::t('account', 'Type'),
            'username'          =>  Yii::t('account', 'Username'),
            'email'             =>  Yii::t('account', 'Email'),
            'created_at'        =>  Yii::t('account', 'Registration time'),
            'registration_ip'   =>  Yii::t('account', 'Registration ip'),
        ];
    }

    /**
     * @param $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = $this->finder->getAccountQuery();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        if ($this->created_at !== null) {
            $date = strtotime($this->created_at);
            $query->andFilterWhere(['between', 'created_at', $date, $date + 3600 * 24]);
        }

        $query->andFilterWhere([
            'type' => $this->type,
        ]);

        $query->andFilterWhere(['like', 'username', $this->username])
            ->andFilterWhere(['like', 'email', $this->email])
            ->andFilterWhere(['registration_ip' => $this->registration_ip]);

        return $dataProvider;
    }
}
