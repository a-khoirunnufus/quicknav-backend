<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%ut_task_item_report}}`.
 */
class m220725_232920_create_ut_task_item_report_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('ut_task_item_report', [
            'id' => $this->primaryKey(),
            'interface' => $this->string(),
            'code' => $this->string(7),
            'file_id' => $this->string(),
            'file_name' => $this->string(),
            'file_depth' => $this->integer(),
            'path_to_file' => $this->string(),
            'hint_visible' => $this->boolean(),
            'description' => $this->text(),
            'order' => $this->integer(),
            'is_success' => $this->boolean(),
            'time_completion' => $this->integer()->notNull(),
            'number_of_step' => $this->integer()->notNull(),
            'use_adaptive_interface' => $this->boolean(),
            'details' => $this->text(),
            'generate_at' => $this->timestamp()->notNull(),
            'task_item_id' => $this->integer()->notNull(),
            'task_id' => $this->integer()->notNull(),
        ]);

        $this->addForeignKey(
            'fk-ut_task_item_report-task_id',
            'ut_task_item_report',
            'task_id',
            'ut_task',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-ut_task_item_report-task_item_id',
            'ut_task_item_report',
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
            'fk-ut_task_item_report-task_item_id',
            'ut_task_item_report',
        );

        $this->dropForeignKey(
            'fk-ut_task_item_report-task_id',
            'ut_task_item_report',
        );

        $this->dropTable('{{%ut_task_item_report}}');
    }
}
