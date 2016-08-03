/**
 * 前端Ajax分页
 * @author 黎兴济
 * @since 2016-6-12
 */

/**
 * 分页模板类
 * 变量说明：
 var currentPage = 1;//当前页
 var count = 0;//数据记录总条数
 var pageSize = 10;//每页显示条数
 var showSize = 6;//每页显示的页数
 var params={};//参数
 **/
var mPage = {
    //分页模板pageTpl([总记录数=1]，   [分页大小=10]，     [当前页=1]，         [显示页数=6]，     [分页参数='page'],      [触发方法],[参数]，[方法所属对象])
    pageTpl: function (count, pageSize, currentPage, showSize,params) {
        params=JSON.stringify(params);
        var staticPage = "<span>共有" + count + " 条记录,当前第&nbsp;" + currentPage + "页&nbsp;共&nbsp;" + Math.ceil(count / pageSize) + "&nbsp;页</span>";
        var firstPage = (currentPage == 1 && Math.ceil(count / pageSize) != 1) ? "" : "<a onclick='getPageData(1,"+params+")'>首页</a>";
        var lastPage = (currentPage != Math.ceil(count / pageSize) && Math.ceil(count / pageSize) != 1) ? "<a onclick='getPageData("+Math.ceil(count / pageSize)+","+params+")'>尾页</a>" : "";
        var tempSize = Math.ceil(count / pageSize) >= showSize ? showSize : Math.ceil(count / pageSize);//数据页数长度
        var str = staticPage + firstPage;
        for (var i = 1; i <= tempSize; i++) {
            if (i == currentPage) {
                str += "<span class=\"current\">第" + i + "页</span>";
            } else {
                str += "<a onclick='getPageData("+i+","+params+")'> 第" + i + "页</a>";
            }
        }
        str += (currentPage != 1) ? "<a onclick='getPageData("+(currentPage-1)+","+params+")'>上一页</a>" : "";
        str += (currentPage != Math.ceil(count / pageSize)) ? "<a onclick='getPageData("+(currentPage+1)+","+params+")'>下一页</a>" : "";
        str += lastPage;
        str += "&nbsp;&nbsp;跳转到&nbsp;<input type=\"text\" size='3' title=\"请输入要跳转到的页数并回车\" onkeyup='javascript:if(event.charCode==13||event.keyCode==13){if(!isNaN(this.value)){getPageData(this.value,"+params+"); }}'>&nbsp;页";
        return str;
    }
}


