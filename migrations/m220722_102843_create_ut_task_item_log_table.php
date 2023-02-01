<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%ut_task_item_log}}`.
 */
class m220722_102843_create_ut_task_item_log_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('ut_task_item_log', [
            'id' => $this->primaryKey(),
            'action' => $this->string()->notNull(),
            'object' => $this->text()->notNull(),
            'time' => $this->timestamp()->notNull(),
            'task_item_id' => $this->integer()->notNull(),
        ]);

        $this->addForeignKey(
            'fk-ut_task_item_log-task_item_id',
            'ut_task_item_log',
            'task_item_id',
            'ut_task_item',
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
            'fk-ut_task_item_log-task_item_id',
            'ut_task_item_log',
        );

        $this->dropTable('ut_task_item_log');
    }
}
