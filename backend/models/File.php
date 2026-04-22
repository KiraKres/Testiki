<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "file".
 *
 * @property int $id
 * @property string $file_true_name
 * @property string $file_name
 * @property string $file_path
 * @property string $user_name
 * @property int $user_id
 * @property string $time_modify
 */
class File extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'file';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['file_true_name', 'file_name', 'file_path', 'user_name', 'user_id'], 'required'],
            [['user_id'], 'integer'],
            [['time_modify'], 'safe'],
            [['file_true_name', 'file_name', 'file_path', 'user_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'file_true_name' => 'File True Name',
            'file_name' => 'File Name',
            'file_path' => 'File Path',
            'user_name' => 'User Name',
            'user_id' => 'User ID',
            'time_modify' => 'Time Modify',
        ];
    }

}
