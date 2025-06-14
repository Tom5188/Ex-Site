layui.use(['element', 'form', 'layer', 'jquery', 'layedit', 'laydate'], function() {
	var element = layui.element
		, $ = layui.$
		, form = layui.form
		, layer = layui.layer
		, laydate = layui.laydate
		, layedit = layui.layedit;

	//初始化日期控件和富文本编辑器
	laydate.render({
	    type:"datetime",
		elem: '#start_at', //指定元素
		trigger: 'click'//呼出事件改成click
	});
    laydate.render({
        type:"datetime",
		elem: '#end_at', //指定元素
		trigger: 'click'//呼出事件改成click
	});
	laydate.render({
        type:"datetime",
		elem: '#sell_begin', //指定元素
		trigger: 'click'//呼出事件改成click
	});
	
	//百度富文本初始化
	var ue = UE.getEditor('news_content');
	var current_editor;

	ueditor_image_callback = function(urlArray) {
		for(var i = 0 ;i<urlArray.length;i++)
		{
			current_editor.execCommand('insertHtml', "<img class='imgloading' data-original='"+urlArray[i]+"' alt='' src='"+urlArray[i]+"' style='display: inline;'>");
		}
		layer.closeAll();
	}

	upload_image_callback = function (url)
	{
		$(".wx_qr_code").show()
		$("#wx_qr_code").val(url[0])
		$(".wx_qr_code").attr("src",url[0])
	}

	baidu.editor.commands['customupload'] = 
	{
		execCommand: function() {
			upload_select('ueditor_image_callback',10)
			current_editor = this;
			return true;
		}, queryCommandState: function() { }
	};

	

	//表单提交事件
	form.on('submit(submit)', function(dataObj){
	    if(dataObj.field.currency_id==dataObj.field.pay_currency_id){
	        layer.msg('项目币种和支付币种不能相同');
	        return false;
	    }
	    var url='/admin/currency/project/add';
	    if(dataObj.field.project_id){
	        url="/admin/currency/project/edit"
	    }
		var serData = $(dataObj.form).serialize();
		$.ajax({
			type: 'POST'
// 			,url: window.location.href			
            ,url: url
			,data: serData
			,success: function(data) {
				if(data.type == 'ok') {
					layer.msg(data.message, {
						icon: 1,
						time: 1000,
						end: function() {
							var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
							parent.layer.close(index);
							parent.window.location.reload();
						}
					});				
				} else {
					layer.msg(data.message, {icon:2});
				}
			}
			,error: function(data) {
				//重新遍历获取JSON的KEY
				var str = '服务器验证失败！';
				for(var o in data.responseJSON.errors) {
					str += data.responseJSON.errors[o];
				}  
				layer.msg(str, {icon:2});
			}
		});
		parent.layui.layer.close();
		return false; //阻止表单跳转。如果需要表单跳转，去掉这段即可。
	});
});