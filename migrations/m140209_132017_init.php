<?php

/*
 * This file is part of the Dektrium project.
 *
 * (c) Dektrium project <http://github.com/dektrium/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use wartron\yii2account\migrations\Migration;
use yii\db\Schema;

/**
 * @author Dmitry Erofeev <dmeroff@gmail.com
 */
class m140209_132017_init extends Migration
{
    public function up()
    {
        $this->createTable('{{%account}}', [
            'id' => Schema::TYPE_PK,
            'type'                  =>  Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 1',
            'username'              =>  Schema::TYPE_STRING . '(255) NOT NULL',
            'email'                 =>  Schema::TYPE_STRING . '(255) NOT NULL',
            'password_hash'         =>  Schema::TYPE_STRING . '(60) NOT NULL',
            'auth_key'              =>  Schema::TYPE_STRING . '(32) NOT NULL',

            'confirmed_at'          =>  Schema::TYPE_INTEGER,
            'unconfirmed_email'     =>  Schema::TYPE_STRING . '(255)',

            'blocked_at'            =>  Schema::TYPE_INTEGER,
            'registration_ip'       =>  Schema::TYPE_STRING . '(45)',
            'flags'                 =>  Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
            'created_at'            =>  Schema::TYPE_INTEGER . ' NOT NULL',
            'updated_at'            =>  Schema::TYPE_INTEGER . ' NOT NULL',
        ], $this->tableOptions);

        $this->createIndex('account_unique_username', '{{%account}}', 'username', true);
        $this->createIndex('account_unique_email', '{{%account}}', 'email', true);

        $this->createTable('{{%profile}}', [
            'account_id'        =>  Schema::TYPE_INTEGER . ' PRIMARY KEY',
            'name'              =>  Schema::TYPE_STRING . '(255)',
            'public_email'      =>  Schema::TYPE_STRING . '(255)',
            'gravatar_email'    =>  Schema::TYPE_STRING . '(255)',
            'gravatar_id'       =>  Schema::TYPE_STRING . '(32)',
            'location'          =>  Schema::TYPE_STRING . '(255)',
            'website'           =>  Schema::TYPE_STRING . '(255)',
            'bio'               =>  Schema::TYPE_TEXT,
        ], $this->tableOptions);

        $this->addForeignKey('fk_account_profile', '{{%profile}}', 'account_id', '{{%account}}', 'id', 'CASCADE', 'RESTRICT');
    }

    public function down()
    {
        $this->dropTable('{{%profile}}');
        $this->dropTable('{{%account}}');
    }
}
