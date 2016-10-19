<?php

namespace app\modules\controllers;

use yii\web\Controller;
use app\modules\models\Admin;
use Yii;
/**
 * Default controller for the `admin` module
 */
class PublicController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionLogin()
    {
        // session_start();
        // var_dump($_SESSION);
    	$this->layout = false;
    	$model = new Admin;
        if( Yii::$app->request->ispost ){
            $post = Yii::$app->request->post();
            if( $model->login($post) ){ 
                $this->redirect( ['default/index'] );
                Yii::$app->end();
            }
        }
        return $this->render('login',array('model'=>$model));
    }

    public function actionLogout(){
        Yii::$app->session->removeAll();
        if( !isset(Yii::$app->session['admin']['isLogin']) ){
            $this->redirect(['public/login']);
            Yii::$app->end();
        }
        $this->goback();
    }

    public function actionSeekpassword(){

        $this->layout=false;
        $model = new Admin;
        if(Yii::$app->request->ispost){
            $post = Yii::$app->request->post();
            if( $model->seekPass($post) ){
                yii::$app->session->setflash('info','电子邮件已成功发送，请注意查收');
            }

        }
        return $this->render('seekpassword',['model'=>$model]);
    }
    

}
