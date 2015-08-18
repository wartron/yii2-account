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
 * @author Dmitry Erofeev <dmeroff@gmail.com>
 */
class m140504_130429_create_token_table extends Migration
{
    public function up()
    {
        $this->createTable('{{%token}}', [
            'account_id'    =>  'BINARY(16) NOT NULL',
            'code'          =>  Schema::TYPE_STRING . '(32) NOT NULL',
            'created_at'    =>  Schema::TYPE_INTEGER . ' NOT NULL',
            'type'          =>  Schema::TYPE_SMALLINT . ' NOT NULL',
        ], $this->tableOptions);

        $this->createIndex('token_unique', '{{%token}}', ['account_id', 'code', 'type'], true);
        $this->addForeignKey('fk_account_token', '{{%token}}', 'account_id', '{{%account}}', 'id', 'CASCADE', 'RESTRICT');
    }

    public function down()
    {
        $this->dropTable('{{%token}}');
    }
}
