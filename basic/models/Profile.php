<?php 
namespace app\models;
use yii\db\ActiveRecord;
use Yii;

class Profile extends ActiveRecord{

	public static function tableName(){
		return "{{%profile}}";
	}
}