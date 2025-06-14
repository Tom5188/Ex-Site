@extends('admin._layoutNew')

@section('page-head')
    <style type="text/css">
        /*.layui-table-cell{*/
        	/*height: auto;*/
        	/*white-space: normal;*/
        	/*overflow:visible;*/
         /*   text-overflow:inherit;*/
        /*}*/
        /*.layui-table img{*/
        	/*max-width:100px*/
        /*}*/
    </style>
@endsection

@section('page-content')
    <button class="layui-btn layui-btn-normal layui-btn-radius" id="add_mail_message">添加站内信</button>
    <div class="layui-inline">
       <div class="layui-input-inline date_time111" style="margin-left: 50px;">
           <input type="text" name="start_time" id="start_time" placeholder="请输入开始时间" autocomplete="off" class="layui-input" value="">
       </div>
       <div class="layui-input-inline date_time111" style="margin-left: 50px;">
           <input type="text" name="end_time" id="end_time" placeholder="请输入结束时间" autocomplete="off" class="layui-input" value="">
       </div>
        <button class="layui-btn btn-search" id="mobile_search" lay-submit lay-filter="mobile_search"> <i class="layui-icon">&#xe615;</i> </button>
    </div>
    
    <table class="layui-hide" id="mailMessageList" lay-filter="mailMessageList"></table>
    
    <script type="text/html" id="barDemo">
        @{{# if (d.status == 0) { }}
            <a class="layui-btn layui-btn-xs" lay-event="fs">发送</a>
        @{{# } }}
        <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del">删除</a>
         
    </script>
    <script type="text/html" id="user_ids">
        @{{# if (d.user_ids == 0) { }}
            所有用户
        @{{# } else { }}
            @{{d.user_ids}}
        @{{# } }}
    </script>
    
    <script type="text/html" id="status">
       @{{# if (d.status == 0) { }}
            未发送
        @{{# } else { }}
            已发送
        @{{# } }}
    </script>
@endsection

@section('scripts')

    <script>

                window.onload = function() {
                    document.onkeydown=function(event){
                        var e = event || window.event || arguments.callee.caller.arguments[0];
                        if(e && e.keyCode==13){ // enter 键
                            $('#mobile_search').click();
                        }
                    };
                    $('#add_mail_message').click(function(){layer_show('添加站内信', '/admin/message/message_add');});
                    layui.use(['element', 'form', 'layer', 'table','laydate'], function () {
                        var element = layui.element;
                        var layer = layui.layer;
                        var table = layui.table;
                        var $ = layui.$;
                        var form = layui.form;
                        var laydate = layui.laydate;

                        laydate.render({
                            elem: '#start_time'
                        });
                        laydate.render({
                            elem: '#end_time'
                        });

                        form.on('submit(mobile_search)',function(obj){
                            var start_time =  $("#start_time").val()
                            var end_time =  $("#end_time").val()
                            var currency_type =  $("#currency_type").val()
                            var account =  $("input[name='account']").val()
                            var type = $('#type').val()
                            var sign = $('#sign').val()
                            tbRend("{{url('/admin/message/message_list')}}?account="+account
                                +'&type='+type
                                +'&start_time='+start_time
                                +'&end_time='+end_time
                                +'&currency_type='+currency_type
                                +'&sign='+sign
                            );
                            return false;
                        });
                        function tbRend(url) {
                            table.render({
                                elem: '#mailMessageList'
                                ,url: url
                                ,page: true
                                ,limit: 20
                                ,height: 'full-100'
                                ,toolbar: true
                                ,cols: [[
                                    {field: 'id', title: 'ID'}
                                    ,{field:'title',title: '标题'}
                                    ,{field:'content',title: '内容'}
                                    ,{field:'user_ids',title: '收件人', templet: '#user_ids'}
                                    ,{field:'status',title: '状态', templet: '#status'}
                                    ,{field:'create_time',title:'创建时间'}
                                    ,{fixed: 'right', title: '操作', minWidth: 150, align: 'center', toolbar: '#barDemo'}
                                ]]
                            });
                        }
                        tbRend("{{url('/admin/message/message_list')}}");
                        //监听工具条
                        table.on('tool(mailMessageList)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
                            var data = obj.data;
                            var layEvent = obj.event;
                            var tr = obj.tr;

                            if (layEvent === 'del') { //编辑
                                layer.confirm('真的要删除吗？', function(index){
                                    //向服务端发送删除指令
                                    $.ajax({
                                        url:'/admin/message/message_del',
                                        type:'post',
                                        dataType:'json',
                                        data:{id:data.id},
                                        success:function(res){
                                            if(res.type=='ok'){
                                                obj.del(); //删除对应行（tr）的DOM结构，并更新缓存
                                                layer.msg(res.message);
                                                layer.close(index);
                                            }else{
                                                layer.close(index);
                                                layer.alert(res.message);
                                            }
                                        }
                                    });
                                });
                            }
                            if (layEvent === 'fs') { //编辑
                                $.ajax({
                                    url:'/admin/message/message_fs',
                                    type:'post',
                                    dataType:'json',
                                    data:{id:data.id},
                                    success:function(res){
                                        if(res.type=='ok'){
                                            layer.msg(res.message);
                                            tbRend("{{url('/admin/message/message_list')}}");
                                        }else{
                                            layer.alert(res.message);
                                        }
                                    }
                                });
                                
                            }
                        });
                    });
                }
            </script>    
@endsection