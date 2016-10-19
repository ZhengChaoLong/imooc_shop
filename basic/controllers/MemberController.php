<?php 
namespace app\controllers;
use yii\web\Controller;
use app\models\User;
use yii;

class MemberController extends Controller{

	public $layout='layout2';
	
	public function actionAuth(){
		$this->layout = 'layout2';
		$model = new User;
		if(yii::$app->request->isPost){
			$post = yii::$app->request->post();
			if($model->login($post)){
				$this->redirect(['index/index']);
				yii::$app->end();
			}
		}
		return $this->render('auth',['model'=>$model]);
	}

	public function actionReg(){
		$model = new User;
		if(yii::$app->request->isPost){
			$post = yii::$app->request->post();
			if( $model->regByMail($post) ){
				yii::$app->session->setFlash('info','电子邮件发送成功');
			}
		}
		return $this->render('auth',['model'=>$model]);
	}

//会员退出操作
	public function actionLogout(){
		yii::$app->session->remove('loginname');
		yii::$app->session->remove('isLogin');
		if( !isset(yii::$app->session['isLogin']) ){
			return $this->goBack(yii::$app->request->referrer);
		}
	}
}
