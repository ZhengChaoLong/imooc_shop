<?php
namespace  app\controllers;
use yii\web\Controller;

class IndexController extends Controller{

 	public $layout = 'layout1';
 	
	public function actionIndex(){
		
		return $this->render('index',array( ));
	}
}
