<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%file}}`.
 */
class m260424_072624_create_file_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
{
    $this->createTable('{{%file}}', [
        'id' => $this->primaryKey(),
        'file_true_name' => $this->string(255)->notNull(),
        'file_name' => $this->string(255)->notNull(),
        'file_path' => $this->string(255)->notNull(),
        'user_name' => $this->string(255)->notNull(),
        'user_id' => $this->integer()->notNull(),
        'time_modify' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
    ]);

    $this->createTable('{{%user}}', [
        'user_id' => $this->primaryKey(),
        'user_name' => $this->string()->notNull()->unique(),
        'auth_key' => $this->string(32)->notNull(), //плашка запомнить меня
        'password_hash' => $this->string()->notNull(),
        'access_token' => $this->string()->unique(),
    ]);

}

public function safeDown()
{
    $this->dropTable('{{%file}}');
}

}
