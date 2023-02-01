<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%ut_task}}`.
 */
class m220722_102646_create_ut_task_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('ut_task', [
            'id' => $this->primaryKey(),
            'interface' => $this->string()->notNull(),
            'code' => $this->string(3)->notNull(),
            'name' => $this->string()->notNull(),
            'order' => $this->integer(),
            'hint_visible' => $this->boolean()->notNull(),
            'is_lock' => $this->boolean()->defaultValue(true),
            'participant_id' => $this->integer()->notNull(),
        ]);

        $this->addForeignKey(
            'fk-ut_task-participant_id',
            'ut_task',
            'participant_id',
            'ut_participant',
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
            'fk-ut_task-participant_id',
            'ut_task',
        );

        $this->dropTable('ut_task');
    }
}
