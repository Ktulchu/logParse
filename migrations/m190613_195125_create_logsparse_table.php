<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%logsparse}}`.
 */
class m190613_195125_create_logsparse_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%logsparse}}', [
            'id'   => $this->primaryKey(),
			'ip'   => $this->string()->notNull(),
			'date' => $this->dateTime()->notNull(),
			'url'  => $this->string()->notNull(),
			'agent'=> $this->string()->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%logsparse}}');
    }
}
