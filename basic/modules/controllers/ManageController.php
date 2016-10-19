<?php

namespace app\modules\controllers;
use yii\data\Pagination;
use yii\web\Controller;
use yii;
use app\modules\models\Admin;
/**
 * Default controller for the `admin` module
 */
class ManageController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionMailchangepass()
    {

    	$this->layout=false;
    	$time = yii::$app->request->get('timestamp');
    	$adminuser = yii::$app->request->get('adminuser');
    	$token = yii::$app->request->get('token');
    	$model = new Admin;
    	$mytoken = $model->createToken($adminuser,$time);
    	if( $mytoken != $token ){
    		$this->redirect(['public/login']);
    		yii::$app->end();
    	}
    	if( time()-$time >3000 ){
    		$this->redirect(['public/login']);
    		yii::$app->end();
    	}
    	if( yii::$app->request->isPost ){
    		$post = yii::$app->request->post();
    		if( $model->changePass($post) ){
    			yii::$app->session->setFlash('info','密码修改成功');
    		}
    	}
    	$model->adminuser = $adminuser;
        // 这里为什么要加这个？ 因为表单穿过来的adminuser是隐藏input，没有值的，所以这里赋值后方便后面的使用
    	return $this->render('mailchangepass',['model'=>$model]);
    }

    public function actionManagers(){
        $this->layout = 'layout1';
        $model = Admin::find();
        $count = $model->count();
        $PageSize = yii::$app->params['pageSize']['managers'];
        $pagination = new Pagination(['totalCount'=>$count,'PageSize'=>$PageSize]);
        $managers = $model->offset($pagination->offset)->limit($pagination->limit)->all();
        return $this->render('managers',['managers'=>$managers,'page'=>$pagination]);
    }

    public function actionReg(){
        $this->layout = 'layout1';
        $model = new Admin;
        if(yii::$app->request->isPost){
            $post = yii::$app->request->post();
            if( $model->manageReg($post) ){
                yii::$app->session->setFlash('info','用户创建成功');
            }else{
                yii::$app->session->setFlash('info','用户创建失败');
            }
        }
        $model->adminpass='';
        $model->repass = '';
        return $this->render('reg',['model'=>$model]);
    }

    public function actionDel(){
        $adminid = (int)yii::$app->request->get('adminid');
        if( empty($adminid) ){
            throw new \Exception();
        }
        $model = new Admin;
        if( $model->deleteAll('adminid = :id',[':id'=>$adminid]) ){
            yii::$app->session->setFlash('info','删除成功');
            $this->redirect(['manage/managers']);
        }

    }

    public function actionChangemail(){
        $this->layout='layout1';
        $model = Admin::find()->where('adminuser = :user',[':user'=>yii::$app->session['admin']['adminuser'] ])->one();
        if(yii::$app->request->isPost){
            $post = yii::$app->request->post();
            if($model->changeemail($post)){
                yii::$app->session->setFlash('info','修改成功');
            }
        }
        $model->adminpass = '';
        return $this->render('changeemail',['model'=>$model]);
    }

    public function actionChangepass(){
        $this->layout = 'layout1';
        $model = Admin::find()->where('adminuser = :user',[':user'=>yii::$app->session['admin']['adminuser'] ])->one();
        if(yii::$app->request->isPost){
            $post = yii::$app->request->post();
            if($model->changePass($post)){
                yii::$app->session->setFlash('info','密码修改成功');
            }
        }
        $model->adminpass = '';
        $model->repass = '';
        return $this->render('changepass',['model'=>$model]);
    }

}
