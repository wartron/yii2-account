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
 * @author Will Wharton <w@wartron.com>
 */
class m150815_124311_create_account_organization_table extends Migration
{
    public function up()
    {
        $this->createTable('{{%account_organization}}', [
            'account_id'        =>  Schema::TYPE_INTEGER . ' NOT NULL',
            'organization_id'   =>  Schema::TYPE_INTEGER . ' NOT NULL',
            'type'              =>  Schema::TYPE_SMALLINT . ' NOT NULL',
            'created_at'        =>  Schema::TYPE_INTEGER . ' NOT NULL',
            'deleted_at'        =>  Schema::TYPE_INTEGER ,
        ], $this->tableOptions);

        $this->addForeignKey('fk_account_organization_user', '{{%account_organization}}', 'account_id', '{{%account}}', 'id', 'CASCADE', 'RESTRICT');
        $this->addForeignKey('fk_account_organization_org', '{{%account_organization}}', 'organization_id', '{{%account}}', 'id', 'CASCADE', 'RESTRICT');
    }

    public function down()
    {
        $this->dropTable('{{%account_organization}}');
    }
}
