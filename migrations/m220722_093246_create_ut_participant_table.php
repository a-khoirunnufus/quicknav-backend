<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%ut_participant}}`.
 */
class m220722_093246_create_ut_participant_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('ut_participant', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'name' => $this->string()->notNull(),
            'age' => $this->integer(),
            'job' => $this->string(),
        ]);

        $this->addForeignKey(
            'fk-ut_participant-user_id',
            'ut_participant',
            'user_id',
            'user',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(
            'fk-ut_participant-user_id',
            'ut_participant',
        );

        $this->dropTable('ut_participant');
    }
}
