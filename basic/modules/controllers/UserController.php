<?php

namespace app\modules\controllers;
use yii\data\Pagination;
use yii\web\Controller;
use yii;
use app\models\User;
use app\models\Profile;
/**
 * Default controller for the `admin` module
 */
class UserController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
    
    public function actionUsers(){
        $this->layout = 'layout1';
        $model = User::find()->joinWith('profile');//public function getProfile()
        $count = $model->count();
        $PageSize = yii::$app->params['pageSize']['users'];
        $pagination = new Pagination(['totalCount'=>$count,'PageSize'=>$PageSize]);
        $users = $model->offset($pagination->offset)->limit($pagination->limit)->all();
        return $this->render('users',['users'=>$users,'page'=>$pagination]);
    }

    public function actionReg(){
        $this->layout = 'layout1';
        $model = new User;
        if(yii::$app->request->isPost){
            $post = yii::$app->request->post();
            if( $model->userReg($post,'useradd') ){
                yii::$app->session->setFlash('info','用户创建成功');
            }
        }
        $model->userpass='';
        $model->repass = '';
        return $this->render('reg',['model'=>$model]);
    }


    /**
    **删除某个用户
    **先删除profile中用户的信息，再删除user中的用户
    **采用事务操作
    **/
    public function actionDel(){
        try{
            $userid = (int)yii::$app->request->get('userid');
            if( empty($userid) ){
                throw new \Exception();
            }
            $trans = yii::$app->db->beginTransaction();
            if($obj = Profile::find()->where('userid = :id',[':id'=>$userid])->one()){
                $res = Profile::deleteAll('userid = :id',[':id'=>$userid]);
                if(empty($res)){
                    throw new \Exception();
                }
            }
            if( !User::deleteAll('userid = :id',[':id'=>$userid])){
                throw new \Exception();
            }
            $trans->commit();
        }catch(\Exception $e){
            if(yii::$app->db->getTransaction()){
                $trans->rollback();
            }
        }
        $this->redirect(['user/users']);
    }

}
