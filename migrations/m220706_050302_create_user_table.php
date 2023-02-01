<?php

use yii\db\Migration;

/**
 * Handles the creation of table `user`.
 */
class m220706_050302_create_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('user', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'email' => $this->string()->unique()->notNull(),
            'auth_key' => $this->string(),
            'access_token' => $this->string(),
            'g_access_token' => $this->text(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('user');
    }
}
