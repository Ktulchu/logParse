<?php

use yii\db\Migration;

/**
 * Handles adding position to table `{{%logsparse}}`.
 */
class m190616_070802_add_position_column_to_logsparse_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%logsparse}}', 'os', $this->string(100));
		$this->addColumn('{{%logsparse}}', 'brous', $this->string(100));
		$this->addColumn('{{%logsparse}}', 'architecture', $this->string(3));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%logsparse}}', 'os');
    }
}
