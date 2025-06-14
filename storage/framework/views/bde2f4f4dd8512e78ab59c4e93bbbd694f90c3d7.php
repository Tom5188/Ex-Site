<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>登入 - 内容管理后台</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="stylesheet" href="/layuiadmin/layui/css/layui.css" media="all">
    <link rel="stylesheet" href="/layuiadmin/style/admin.css" media="all">
    <link rel="stylesheet" href="/layuiadmin/style/login.css" media="all">
    <style>
    html, body {
	position:fixed;
  top:0px;
  left:0px;
  height:100%;
  width:100%;
  
  /*Fallback if gradeints don't work */
  background: #141e6e;
  /*Linear gradient... */
  background: 
    radial-gradient(
     at center, #0075c3, #000b61
    );
}

.apImgTitle {
	position: fixed;
	width: 42%;
	left: 30%;
	top: 30%;
}

.apTitle {
    font-size: 40px;
    color: #fff;
    position: fixed;
    top: 32%;
    width: 100%;
    text-align: center;
}

.logcon {
position: fixed;
    width: 100%;
    top: 47%;
    text-align: center;
}

.logcon input {
	padding: 10px 15px;
	border-radius: 3px;
	border: none;
	margin-right: 10px;
	width: 220px;
}

.logcon button {
	padding: 7px 20px 10px 20px;
	border: none;
	background: #fff;
	border-radius: 3px;
}

.logcon button:hover {
	cursor: pointer;
}

canvas {
	display: block;
	vertical-align: bottom;
}

#particles-js {
	width: 100%;
	height: 100%;

	background-size: cover;
	background-position: 50% 50%;
	background-repeat: no-repeat;
	width: 100%;
	height: 100%;
	position: absolute;
	top: 0;
	left: 0;
}
    .login-in{
        background: #fff;
        width: 750px;
        margin: 0 auto;
        box-sizing: border-box;
        border-radius: 6px;
        border: 1px solid #614bf8;
        display: flex;
        border: 1px solid #333;
        display: flex;
        justify-content: space-between;
        box-shadow: 0px 5px 16px 7px rgba(0, 0, 0, .2);
    }
    .login-in .pic{
        width: 375px;
        height: 300px;
    }
    .layadmin-user-login-main{
        background: #fff;
        width: 375px;
        margin: 0 auto;
        box-sizing: border-box;
        /*padding: 40px;*/
        border-radius: 6px;
        /*border: 1px solid #614bf8;*/
    }
    .login-btn{
        background: #3c21f7;
    }
    </style>
</head>
<body>

<div class="layadmin-user-login layadmin-user-display-show" id="LAY-user-login" style="display: none;">
    
    <div id="particles-js" style="display: flex;align-items: center;justify-content: center">
		<canvas class="particles-js-canvas-el" style="width: 100%; height: 100%;" width="472" height="625"></canvas>
	</div>
		
	<div class="login-in">
	    <div class="pic">
	        <img src="/layuiadmin/layui/images/new/bg_share.png"> 
	    </div>
        <div class="layadmin-user-login-main">
        
        
            <div class="layadmin-user-login-box layadmin-user-login-header">
                <h2>内容管理后台</h2>
            </div>
            
            <div class="layadmin-user-login-box layadmin-user-login-body layui-form">
                <div class="layui-form-item">
                    <label class="layadmin-user-login-icon layui-icon layui-icon-username" for="LAY-user-login-username"></label>
                    <input type="text" name="username" id="LAY-user-login-username" lay-verify="required" placeholder="用户名" class="layui-input">
                </div>
                <div class="layui-form-item">
                    <label class="layadmin-user-login-icon layui-icon layui-icon-password" for="LAY-user-login-password"></label>
                    <input type="password" name="password" id="LAY-user-login-password" lay-verify="required" placeholder="密码" class="layui-input">
                </div>
                <div class="layui-form-item" style="display:none">
                    <div class="layui-row">
                        <div class="layui-col-xs7">
                            <label class="layadmin-user-login-icon layui-icon layui-icon-vercode" for="LAY-user-login-vercode"></label>
                            <input type="text" name="vercode" id="LAY-user-login-vercode"  placeholder="图形验证码" class="layui-input">
                        </div>
                        <div class="layui-col-xs5">
                            <div style="margin-left: 10px;">
                                <img src="https://www.oschina.net/action/user/captcha" class="layadmin-user-login-codeimg" id="LAY-user-get-vercode">
                            </div>
                        </div>
                    </div>
                </div>
    
                <div class="layui-form-item">
                    <button class="layui-btn layui-btn-fluid login-btn" lay-submit lay-filter="LAY-user-login-submit">登 入</button>
                </div>
    
            </div>
        </div>
    </div>
        
</div>

<script src="/layuiadmin/layui/layui.js"></script>
<!--<script src="/layuiadmin/layui/bg1.js"></script>-->
<!--<script src="/layuiadmin/layui/bg2.js"></script>-->
<script>
    // $(document).ready(function () {
    // 	if (window != top) {
    // 		top.location.href = location.href;
    // 	}
    // });
    layui.config({
        base: '/manages/' //静态资源所在路径
    }).extend({
        index: 'lib/index' //主入口模块
    }).use(['index', 'user'], function(){
        var $ = layui.$
            ,setter = layui.setter
            ,admin = layui.admin
            ,form = layui.form
            ,router = layui.router()
            ,search = router.search;

        form.render();
        //提交
        form.on('submit(LAY-user-login-submit)', function(obj){
            //请求登入接口
            admin.req({
                url: '/manages/login'
                ,type: 'POST'
                ,data: obj.field
                ,done: function (res) {
                    layer.msg(res.msg, {icon: 1, time: 1000}, function () {
                        location.href = '/manages/index'; //
                    });
                }
            });
        });
    });
</script>
</body>
</html>