@extends('admin._layoutNew')

@section('page-head')
<style>
    p.percent {
        text-align: right;
        margin-right: 10px;
    }
    p.percent::after {
        content: '%';
    }
</style>
@endsection

@section('page-content')
    <form class="layui-form layui-form-pane layui-inline" action="">

        <div class="layui-inline" style="margin-left: 50px;">
            <label class="layui-form-label">账号</label>
            <div class="layui-input-inline">
                <input type="text" name="search" autocomplete="off" class="layui-input">
            </div>
        </div>
        <div class="layui-inline">
            <div class="layui-input-inline">
                <button class="layui-btn" lay-submit="" lay-filter="mobile_search"><i class="layui-icon">&#xe615;</i></button>
            </div>
        </div>



    </form>
    <table id="demo" lay-filter="test"></table>
@endsection

@section('scripts')
    <script type="text/html" id="barDemo">
    @{{d.status==0 ? '<a class="layui-btn layui-btn-xs" lay-event="del">确认</a><a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="edit">驳回</a>' : '' }}
        

    </script>

    <script>
        layui.use(['table','form','laydate'], function(){
            var table = layui.table;
            var $ = layui.jquery;
            var form = layui.form;
            var laydate = layui.laydate;
            //第一个实例
            table.render({
                elem: '#demo'
                ,toolbar: '#toolbar'
                ,url: '{{url('admin/ybb/unlock/list')}}' //数据接口
                ,page: true //开启分页
                ,id:'mobileSearch'
                ,cols: [[ //表头
                    {field: 'id', title: 'ID', minWidth:60, sort: true}
                    ,{field: 'account_number', title: '账号', minWidth:80}
                    ,{field: 'amount', title: '存款数量', minWidth:80}
                    ,{field: 'total_interest', title: '已产生利息', minWidth:80}
                    ,{field: 'status', title: '状态', minWidth:80,templet:function(d){
                        if(d.status==1){
                           return '<span style="color:#5fb878">进行中</span>'; 
                        }
                        if(d.status==2){
                            return '<span style="color:#f00">已结束</span>';
                        }
                        return '';
                    }}
                    ,{field: 'start_at', title: '开始时间', minWidth:80}
                    ,{field: 'end_at', title: '完成时间', minWidth:80}
                     ,{field: 'last_settle_time', title: '上次派息时间', minWidth:80}
                    ,{field: 'is_cancel', title: '是否取消', minWidth:80,templet:function(d){
                        if(d.is_cancel==1){
                           return '<span style="color:#5fb878">是</span>'; 
                        }else{
                            return '<span style="color:#f00">否</span>';
                        }
                       
                    }}
                    //,{field: 'create_time', title: '添加时间', minWidth:160}
                    // ,{title:'操作',width:240,toolbar: '#barDemo'}
                ]]
            });
            //监听提交
            // form.on('submit(mobile_search)', function(data){
            //     var account_number = data.field.account_number;
            //     var start_time =  $("#start_time").val()
            //     var end_time =  $("#end_time").val()
            //     var status = $('#status').val()
            //     table.reload('mobileSearch',{
            //         where:{mobile:account_number,status:status,start_time:start_time,end_time:end_time},
            //         page: {curr: 1}         //重新从第一页开始
            //     });
            //     return false;
            // });
            laydate.render({
                elem: '#start_time'
            });
            laydate.render({
                elem: '#end_time'
            });

            table.on('tool(test)', function(obj){
                var data = obj.data;
                if(obj.event === 'del'){
                    layer.confirm('真的确认么', function(index){
                        $.ajax({
                            url:'{{url('admin/flashagainst/affirm')}}',
                            type:'post',
                            dataType:'json',
                            data:{id:data.id},
                            success:function (res) {
                                if (res.type == 'error') {
                                    layer.msg(res.message);
                                } else {
                                    layer.msg(res.message);
                                    setTimeout(function () {
                                        location.reload()
                                    }, 2000)
                                }
                            }
                        });


                    });
                } else if(obj.event === 'edit'){
                    layer.confirm('是否驳回', function(index){
                        $.ajax({
                            url:'{{url('admin/flashagainst/reject')}}',
                            type:'post',
                            dataType:'json',
                            data:{id:data.id},
                            success:function (res) {
                                if(res.type == 'error'){
                                    layer.msg(res.message);
                                }else{
                                    layer.msg(res.message);
                                    setTimeout(function () {
                                        location.reload()
                                    }, 2000)
                                }
                            }
                        });


                    });
                }
            });
            //监听提交
            form.on('submit(mobile_search)', function(data){
                var search = data.field.search;
                table.reload('mobileSearch',{
                    where:{search:search},
                    page: {curr: 1}         //重新从第一页开始
                });
                return false;
            });
        });
    </script>
@endsection