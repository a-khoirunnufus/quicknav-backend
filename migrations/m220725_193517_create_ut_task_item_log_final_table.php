<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%ut_task_item_log_final}}`.
 */
class m220725_193517_create_ut_task_item_log_final_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('ut_task_item_log_final', [
            'id' => $this->primaryKey(),
            'task_item_id' => $this->integer()->notNull(),
            'task_item_log_id' => $this->integer()->notNull(),
            'time' => $this->timestamp()->notNull(),
        ]);

        $this->addForeignKey(
            'fk-ut_task_item_log_final-task_item_id',
            'ut_task_item_log_final',
            'task_item_id',
            'ut_task_item',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-ut_task_item_log_final-task_item_log_id',
            'ut_task_item_log_final',
            'task_item_log_id',
            'ut_task_item_log',
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
            'fk-ut_task_item_log_final-task_item_log_id',
            'ut_task_item_log_final',
        );

        $this->dropForeignKey(
            'fk-ut_task_item_log_final-task_item_id',
            'ut_task_item_log_final',
        );
        
        $this->dropTable('{{%ut_task_item_log_final}}');
    }
}
