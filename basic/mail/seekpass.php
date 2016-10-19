

<p>尊敬的<?php echo $adminuser;?>，你好</p>



<P>你的找回密码链接如下：</P>


<?php  

//这里要注意是静态资源中的urlManger，yii框架大多组件都是yii:$app中的
$url = yii::$app->urlManager->createAbsoluteUrl(['admin/manage/mailchangepass','timestamp'=>$time,'adminuser'=>$adminuser,'token'=>$token]);

?>
<a href="<?php echo $url; ?>"> <?php echo $url;?> </a>
<p>该链接五分钟内有效</p>
<!-- <P>time: <?php echo $time;?></P>
<P>adminuser: <?php echo $adminuser;?></P>
<P>token: <?php echo $token;?></P> -->


<p>该邮件为系统自动发送，请勿回复</p>

