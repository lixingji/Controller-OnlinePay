/**
 * 登陆
 */
$(function () {
    navClick();//导航菜单点击监听
    confirmClick();//确认事件监听
});


//导航菜单点击监听
function navClick() {
    //首页事件监听
    $("#_id_home_click").click(function add_home_click() {
        var homeUrl = preUrl + "Message/Index/index";
        $("#_id_this").attr("action", homeUrl);
        $("#_id_token").attr("value", localStorage.token);
        $("#_id_this").submit();
    });

    //退出登录
    $("#_id_logout").click(function add_logout_click() {
        location.href = preUrl + "Message/Index/login";
    });
    //修改密码
    $("#_id_nav_modifyPsw").click(function () {
        $(".myPage").load('modifyPsw.html');
    });
    //查看用户列表
    $("._class_user_list").click(function () {
        $(".myPage").load('userList.html');
    });

    //添加品牌
    $("#_id_nav_add_brand").click(function () {
        $(".myPage").load('addBrand.html');
    });

    //查看品牌列表
    $("._class_nav_brand_list").click(function () {
        $(".myPage").load('brandList.html');
    });

    //添加商店
    $("#_id_nav_add_shop").click(function () {
        $(".myPage").load('addShop.html');
    });

    //查看商店列表
    $("._class_nav_shop_list").click(function () {
        $(".myPage").load('shopList.html');
    });

//查看记录列表
    $("._class_nav_record_list").click(function () {
        $(".myPage").load('statisticsList.html');
    });

}

//确认事件监听
function confirmClick() {
    $("#_id_modify_psw_sure").click(function () {
        alert(232);
        if ($("#_id_midify_oldPsw").val()) {
            if ($("#_id_midify_oldPsw").val() != $("#_id_midify_confirmPsw").val()) {
                alert("一致");
            }
        }
    });
}