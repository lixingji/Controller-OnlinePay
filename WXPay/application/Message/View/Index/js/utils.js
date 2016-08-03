/**
 *POST请求json数据通用方法
 * @author 黎兴济
 * @since 2016.05.06
 * @param _url 请求地址
 * @param _params 请求参数
 * @param success_callback 成功处理
 * @param error_callback 错误处理,"直接处理返回错误信息"
 */

var preUrl="http://119.29.8.60/index.php/";
//var preUrl="http://test.37service.com/index.php/";
var postRequest = function (_url, _params, success_callback,error_callback) {
    $.ajax({
        type: "post",
        url: _url,
        dataType: "json",
        dataType: "json",
        async: false,
        data: _params,
        success: success_callback,
        error: function () {
            if(error_callback instanceof Function){
                error_callback();
            }else{
                //请求出错处理
                alert("请求出错");
            }
        }
    })
}

/**
 * 根据表单数组标签获取数组
 * @author 黎兴济
 * @since 2016.05.06
 * @param attr 标签名
 */
var getFormArray = function (attr) {
    var arr = new Array();
    $(attr).each(function (i) {
        arr[i] = $(this).val();
    });
    return arr;
}

/**
 * 前端验证表单
 * @author 黎兴济
 * @since 2016.05.06
 * @type {{setProperty: Function, validateEmpty: Function}}
 */
var ValidateForm = {
    /**
     * 参数说明
     * attr 传入的id或者class等
     * content 提示的内容
     **/
    setProperty: function (attr, content) {
        $(content).each(function (key, value) {
            //if(!validateEmpty($(value[0]).val())){
            alert($(value[0]).html());
            //}
            //if()

        });
    },
    /**
     * 参数说明
     * _validate 验证的数组   _validate（Array(文本，提示内容)，Array2,...）或者  _validate（文本，提示内容）
     **/
    validateEmpty: function (_validate) {
        /**
         **  _validateArr（需要判断的文本）
         ** errArr（为空时错误提示）
         **/
        var _validateArr = _validate[0];
        var errArr = _validate[1];
        var flag = false;
        var alertText = '';
        //传入的是否为数组
        if (_validateArr instanceof Array) {
            for(var i=0;i<_validate.length;i++){
                var _validateArr = _validate[i][0];
                var errArr = _validate[i][1];
                var kVal = _validateArr;
                kVal = (!kVal && isNaN(kVal) || kVal == null) ? '' : kVal;
                //判断kVal的值为空
                if (kVal.length < 1) {
                    alertText = errArr ? errArr : "文本不能为空";
                    flag = true;
                    break;
                }
            }
        } else {
            var kVal = _validateArr;
            kVal = (!kVal && isNaN(kVal) || kVal == null) ? '' : kVal;
            //判断kVal的值为空
            if (kVal.length < 1) {
                alertText = errArr ? errArr : "文本不能为空";
                flag = true;
            }
        }
        //传入的内容为空，则显示错误提示
        if (flag) {
            alert(alertText);
            return true;
        }
        return false;
    }
}
