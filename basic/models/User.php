<?php

namespace app\models;
use yii\db\ActiveRecord;
use app\models\Profile;
use Yii;

class User extends ActiveRecord{

	//活动记录下默认表的字段作为它的属性
	public $repass = null;
	public $loginname = null;
	public $rememberMe = true;
	public static function tableName(){
		return "{{%user}}";
	}

	public function attributeLabels(){
		return [
			'username'=>'用户名',
			'useremail'=>'电子邮箱',
			'userpass'=>'用户密码',
			'repass'=>'确认密码',
			'loginname'=>'用户/用户邮箱'
		];
	}

	public function rules(){
		return [
			['loginname','required','message'=>'登录用户名不能为空','on'=>['login']],
			['rememberMe','boolean','on'=>'login'],
			['username','required','message'=>'用户名不能为空','on'=>['useradd','regbymail']],
			['username','unique','message'=>'用户名已被注册','on'=>['useradd','regbymail']],
			['userpass','required','message'=>'密码不能为空','on'=>['useradd','regbymail','login'] ],
			['useremail','required','message'=>'电子邮箱不能为空','on'=>['useradd','regbymail'] ],
			['useremail','email','message'=>'电子邮箱格式不正确','on'=>['useradd','regbymail'] ],
			['useremail','unique','message'=>'电子邮箱已被注册','on'=>['useradd','regbymail'] ],
			['repass','required','message'=>'确认密码不能为空','on'=>['useradd'] ],
			['repass','compare','compareAttribute'=>'userpass','message'=>'两次输入的密码不一致','on'=>['useradd'] ],
			['userpass','validatePass','on'=>'login'],
		];
	}

	/**
	会员登录验证 
	既可以用用户名登录，也可以用邮箱登陆
	**/
	public function validatePass(){
		if(!$this->hasErrors()){
			$loginname = 'username';
			if( preg_match('/@/', $this->loginname) ){
				$loginname = 'useremail';
			}
			$data = self::find()->where( $loginname.' = :loginname and userpass = :pass',[':loginname'=>$this->loginname,':pass'=>md5($this->userpass)])->one();
			if(is_null($data)){
				$this->addError('userpass','用户名或密码错误');
			}
		}
	}

//前台会员登录验证
	public function login($data){
		$this->scenario = 'login';
		if( $this->load($data) && $this->validate() ){
			$lifetime = $this->rememberMe? 24*3600 : 0;
			session_set_cookie_params($lifetime);
			$session = Yii::$app->session;
			$session['loginname'] = $this->loginname;
			$session['isLogin'] = 1;
			return (bool)$session['isLogin'];
		}
		return false;
	}


	//用户注册
	public function regByMail($data){
		$this->scenario  = 'regbymail';
		$data['User']['username'] = 'imooc_'.uniqid();
		$data['User']['userpass'] = uniqid();
		/**
		对于不是从表单传过来的模型数据，想要加入到模型当中，
		必须在rules规则里面给你的数据加上该场景，load方法只会加载该场景的数据。
		**/
		if($this->load($data)&&$this->validate() ){
				$mailer = Yii::$app->mailer->compose('createuser',['username'=>$data['User']['username'],'userpass'=>$data['User']['userpass'] ]);
		      	$mailer->setFrom('15521215331@163.com');
		      	$mailer->setTo($data['User']['useremail']);
		      	$mailer->setSubject('慕课商城--新建用户');
		      	if( $this->userReg($data,'regbymail') && $mailer->send() ){
		      		return true;
		      	}
		}
		return false;
	}

    public function userReg($data , $scenario='useradd'){
    	$this->scenario = $scenario;
    	if( $this->load($data) && $this->validate() ){
    		$this->userpass = md5($this->userpass);
    		$this->createtime = time();
    		if( $this->save(false) ){ 
    		//$this->save()方法是自动调用validate后保存的，参数加个false则不调用validate方法
    			return true;
    		}
    		return false;
    	}
    	return false;
    }

     public function getProfile(){
    	return $this->hasOne(Profile::className(),['userid'=>'userid']);
    }
 


}

?>