<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%big_data}}`.
 */
class m220706_022158_create_big_data_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%big_data}}', [
            'id' => $this->primaryKey(),
            'big_text' => $this->text(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%big_data}}');
    }
}
