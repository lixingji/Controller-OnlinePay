/**
 * 登陆
 */
$(function () {
    init();
    $("#_id_login").click(add_login_click);
    $("._class_remember").click(function(){
        if($("._class_remember").val()==1){
            localStorage.username="";
            localStorage.password="";
            $("._class_remember").val(0);
        }else{
            localStorage.username=$("#_id_username").val();
            localStorage.password=$("#_id_password").val();
            $("._class_remember").val(1);
        }
    });
});

function init(){
    localStorage.token="";//清空token
    if(localStorage.username){
        $("#_id_username").val(localStorage.username);
        $("#_id_password").val(localStorage.password);
    }
}

//登陆事件监听
function add_login_click(){

    if(!judgeForm($("#_id_username").val(),$("#_id_password").val())){
        return;
    }
    var username=$("#_id_username").val();
    var password=$("#_id_password").val();

    var url = preUrl + "Message/Login/login";

    var parmas = {
        username: username,
        password: password
    }
    var success_callback = function (data) {
        var result=data.result;
        layer.msg(result.description);
        if(result.state==1){
            localStorage.token=data.data.token;
            setTimeout(function () {
                var gotoUrl=preUrl + "Message/Index/index";
                $("#_id_this").attr("action",gotoUrl);
                $("#_id_token").attr("value",data.data.token);
                $("#_id_this").submit();
            },1000);
        }
    }
    //没有药品返回时不做处理
    var error_callback= function () {}
    postRequest(url, parmas, success_callback,error_callback);

}

//判断表单
function judgeForm(username,psw){
    if(username==""||username==undefined){
        //提示层
        layer.msg('用户名不能为空');
        return false;
    }

    if(psw==""||username==undefined){
        //提示层
        layer.msg('密码不能为空');
        return false;
    }
    if(psw.trim().length<6){
        layer.msg('密码不少于6位');
        return false;
    }
    if(psw.trim().length>16){
        layer.msg('密码不大于16位');
        return false;
    }
    return true;
}
