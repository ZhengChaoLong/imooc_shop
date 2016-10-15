<?php
namespace  app\controllers;
use yii\web\Controller;

class IndexController extends Controller{
	public function actionIndex(){
		$hello = 'hello world';
		return $this->render('index',['hello'=>$hello]);
	}
}
