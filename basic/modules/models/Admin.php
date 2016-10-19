<?php

namespace app\modules\models;
use yii\db\ActiveRecord;
use Yii;

class Admin extends ActiveRecord{

	public $rememberMe = true;
	public $repass = null;
	public static function tableName(){
		return "{{%admin}}";
	}

	public function attributeLabels(){
		return [
			'adminuser'=>'管理员账号',
			'adminemail'=>'管理员邮箱',
			'adminpass'=>'管理员密码',
			'repass'=>'确认密码'
		];
	}

	public function rules(){
		return [
			['adminuser','required','message'=>'管理员账号不能为空','on'=>['login','seekpass','adminadd','changeemial']],
			['adminuser','unique','message'=>'管理员账号已被注册','on'=>['adminadd']],
			['adminpass','required','message'=>'管理员密码不能为空','on'=>['login','adminadd','changeemail'] ],
			['adminpass','required','message'=>'新密码不能为空','on'=>'changepass'],
			['rememberMe','boolean','on'=>'login'],
			['adminpass','validatePass','on'=>'login'],
			['adminemail','required','message'=>'电子邮箱不能为空','on'=>['seekpass','adminadd','changeemail'] ],
			['adminemail','email','message'=>'电子邮箱格式不正确','on'=>['seekpass','adminadd','changeemail'] ],
			['adminemail','unique','message'=>'电子邮箱已被注册','on'=>['adminadd','changeemail'] ],
			['adminemail','validateEmail','on'=>'seekpass'],
			['repass','required','message'=>'确认密码不能为空','on'=>['changepass','adminadd'] ],
			['repass','compare','compareAttribute'=>'adminpass','message'=>'两次输入的密码不一致','on'=>['changepass','adminadd'] ],
		];
	}
//活动记录下默认表的字段作为它的属性
	public function validatePass(){
		if(!$this->hasErrors()){
			$data = self::find()->where('adminuser =:user and adminpass = :pass',[':user'=>$this->adminuser,':pass'=>md5($this->adminpass)])->one();
			if(is_null($data)){
				$this->addError('adminpass','用户名或密码错误');
			}
		}
	}

	public function validateEmail(){
		if(!$this->hasErrors()){
			$data = self::find()->where('adminuser = :user and adminemail = :email',[':user'=>$this->adminuser,':email'=>$this->adminemail] )->one();
			if(is_null($data)){
				$this->addError('adminemail','用户邮箱错误');
			}
		}
	}

	public function login($data){
		$this->scenario = 'login';
		if( $this->load($data) && $this->validate() ){
			$lifetime = $this->rememberMe? 24*3600 : 0;
			session_set_cookie_params($lifetime);
			$session = Yii::$app->session;
			$session['admin']=[
				'adminuser'=>$this->adminuser,
				'isLogin'=>1,
			];
			//updateAll($attributes, $condition = '', $params = [])
			$this->updateAll( ['logintime'=>time(),'loginip'=>ip2long(Yii::$app->request->userIP) ], 'adminuser=:user',[':user'=>$this->adminuser] );
			return (bool)$session['admin']['isLogin'];
		}
		return false;
	}

	public function seekPass($data){
		$this->scenario  = 'seekpass';
		if($this->load($data)&&$this->validate() ){
				$time = time();
	            $token = $this->createToken($data['Admin']['adminuser'],$time);
				$mailer = Yii::$app->mailer->compose('seekpass',['adminuser'=>$data['Admin']['adminuser'],'time'=>$time,'token'=>$token ]);
		      	$mailer->setFrom('15521215331@163.com');
		      	$mailer->setTo($data['Admin']['adminemail']);
		      	$mailer->setSubject('慕课商城--找回密码');
		      	if( $mailer->send() ){
		      		return true;
		      	}
		}
		return false;
	}

	public function createToken($adminuser, $time){
        return  md5( md5($adminuser).base64_encode(yii::$app->request->userIP ).md5($time) );
    }

    public function changePass($data){
    	$this->scenario = 'changepass';
    	if($this->load($data) && $this->validate() ){
    		return (bool)$this->updateAll(['adminpass'=>md5($this->adminpass)],'adminuser = :user',[':user'=>$this->adminuser]);
    		//return (bool)$this->updateAll(['adminpass'=>md5($data['Admin']['adminpass'] )],'adminuser = :user',[ ':user'=>$data['Admin']['adminuser'] ] );
    		/**使用第二个return是错误的,因为传过来的数据$data不一定含有adminuser，
    		例如当你修改密码时，并没有传递adminuser过来，adminuser而是存储在session中的，这时就会报错
			而使用$this ,每次在修改时先查询出数据模型，获取所有属性,这时通过$this就可以轻松访问.
    		**/
    	}
    	return false;
    }

    public function manageReg($data){
    	$this->scenario = 'adminadd';
    	if( $this->load($data) && $this->validate() ){
    		$this->adminpass = md5($this->adminpass);
    		if( $this->save(false) ){ 
    		//$this->save()方法是自动调用validate后保存的，参数加个false则不调用validate方法
    			return true;
    		}
    		return false;
    	}
    	return false;
    }

    public function changeemail($data){
    	$this->scenario = 'changeemail';
    	if( $this->load($data) && $this->validate() ){
    		return (bool)$this->updateAll(['adminemail'=>$this->adminemail],'adminuser = :user',[':user'=>$this->adminuser]);
    	}
    	return false;
    }
    

}

?>