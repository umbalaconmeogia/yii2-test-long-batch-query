<?php

namespace common\models;

use batsg\models\BaseModel;
use Yii;

/**
 * This is the model class for table "big_data".
 *
 * @property int $id
 * @property string|null $big_text
 */
class BigData extends BaseModel
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'big_data';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['big_text'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'big_text' => 'Big Text',
        ];
    }
}
