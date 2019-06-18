<?php

namespace app\models;

/**
 * This is the model class for table " logsparse".
 *
 * @property int $id
 * @property string $ip
 * @property datetime $date
 * @property string $url
 * @property string $agent
 * @property string $os
 * @property string $brous
 * @property string $architecture 
 */
class  Logsparse extends \yii\db\ActiveRecord
{
	/**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'logsparse';
    }
	/**
     * {@inheritdoc}
     */

	public function rules()
    {
        return [
            [['ip', 'url', 'agent', 'os', 'brous'], 'required'],
            [['date'], 'safe'],
            [['os', 'brous'], 'string', 'max' => 100],
			[['architecture'], 'string', 'max' => 3],
			[['ip', 'url', 'agent'], 'string', 'max' => 255],
        ];
    }
	
	public static function getMinDate()
	{
		return self::find()->min('date');
	}
	
	public static function getMaxDate()
	{
		return self::find()->max('date');
	}
	
}
