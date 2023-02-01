<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%ut_task_item}}`.
 */
class m220722_102727_create_ut_task_item_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('ut_task_item', [
            'id' => $this->primaryKey(),
            'interface' => $this->string()->notNull(),
            'code' => $this->string(7),
            'file_id' => $this->string()->notNull(),
            'file_name' => $this->string()->notNull(),
            'file_depth' => $this->integer(),
            'path_to_file' => $this->string()->notNull(),
            'hint_visible' => $this->boolean()->notNull(),
            'description' => $this->text(),
            'order' => $this->integer(),
            'status' => $this->string(),
            'run_at' => $this->timestamp(),
            'task_id' => $this->integer()->notNull(),
        ]);

        $this->addForeignKey(
            'fk-ut_task_item-task_id',
            'ut_task_item',
            'task_id',
            'ut_task',
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
            'fk-ut_task_item-task_id',
            'ut_task_item',
        );

        $this->dropTable('ut_task_item');
    }
}
