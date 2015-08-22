<?php

/*
 * This file is part of the Dektrium project.
 *
 * (c) Dektrium project <http://github.com/dektrium/>
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

use wartron\yii2account\migrations\Migration;
use yii\db\Schema;

/**
 * @author Dmitry Erofeev <dmeroff@gmail.com
 */
class m140403_174025_create_account_table extends Migration
{
    public function up()
    {
        $this->createTable('{{%account_network}}', [
            'id'            =>  'BINARY(16) NOT NULL PRIMARY KEY',
            'account_id'    =>  'BINARY(16)',
            'provider'      =>  Schema::TYPE_STRING . ' NOT NULL',
            'client_id'     =>  Schema::TYPE_STRING . ' NOT NULL',
            'data'          =>  Schema::TYPE_TEXT,
            'code'          =>  Schema::TYPE_STRING . '(32)',
            'email'         =>  Schema::TYPE_STRING,
            'username'      =>  Schema::TYPE_STRING,
            'created_at'    =>  Schema::TYPE_INTEGER . ' NOT NULL',
        ], $this->tableOptions);

        $this->createIndex('account_network_unique', '{{%account_network}}', ['provider', 'client_id'], true);
        $this->createIndex('account_unique_code', '{{%account_network}}', 'code', true);
        $this->addForeignKey('fk_account_account_network', '{{%account_network}}', 'account_id', '{{%account}}', 'id', 'CASCADE', 'RESTRICT');
    }

    public function down()
    {
        $this->dropTable('{{%account_network}}');
    }
}
