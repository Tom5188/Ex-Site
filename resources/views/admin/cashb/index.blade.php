@extends('admin._layoutNew')

@section('page-head')

@endsection

@section('page-content')
<style>
    .status_bg_1{
        background: #1E9FFF;
    }
    .status_bg_2{
        background: #5fb878;
    }
    .status_bg_3{
        background: #ff5722;
    }
</style>
    <div style="margin-top: 10px;width: 100%;">
        <form class="layui-form layui-form-pane layui-inline" action="">
            <div class="layui-inline">
                <div class="layui-input-inline">
                    <input type="text" placeholder="ID,账号" name="user_name" autocomplete="off" class="layui-input" value="">
                </div>
            </div>
            <div class="layui-inline" style="margin-left: 10px">
                <div class="layui-input-inline">
                    <button class="layui-btn" lay-submit="" lay-filter="mobile_search"><i class="layui-icon layui-icon-search"></i></button>
                </div>
            </div>
        </form>
        <button class="layui-btn layui-btn-normal" style="margin-left: 10px" onclick="javascrtpt:window.location.href='{{url('/admin/cashb/csv')}}'"><i class="layui-icon layui-icon-share"></i></button>
    </div>

    <script type="text/html" id="switchTpl">
        <input type="checkbox" name="is_recommend" value="@{{d.id}}" lay-skin="switch" lay-text="是|否" lay-filter="sexDemo" @{{ d.is_recommend == 1 ? 'checked' : '' }}>
    </script>

    <table id="demo" lay-filter="test"></table>
    <script type="text/html" id="barDemo">
        <button type="button" class="layui-btn layui-btn-sm layui-btn-normal" lay-event="show">
            <i class="layui-icon layui-icon-screen-full"></i> 查看
        </button>
    </script>
    <script type="text/html" id="statustml">
        @{{d.status==1 ? '<span class="layui-badge status_bg_1">'+'申请提币'+'</span>' : '' }}
        @{{d.status==2 ? '<span class="layui-badge status_bg_2">'+'提币完成'+'</span>' : '' }}
        @{{d.status==3 ? '<span class="layui-badge status_bg_3">'+'申请拒绝'+'</span>' : '' }}

    </script>
@endsection

@section('scripts')
    <script>

        layui.use(['table','form'], function(){
            var table = layui.table;
            var $ = layui.jquery;
            var form = layui.form;
            //第一个实例
            table.render({
                elem: '#demo'
                ,url: "{{url('admin/cashb_list')}}" //数据接口
                ,page: true //开启分页
                ,id:'mobileSearch'
                ,cols: [[ //表头
                    {field: 'id', title: '订单ID', minWidth:100, sort: true}
                    ,{field: 'user_id', title: '用户ID', minWidth:220}
                    ,{field: 'user_name', title: '用户名', minWidth:220}
                    ,{field: 'currency_name', title: '虚拟币', minWidth:100}
                    ,{field: 'number', title: '提币数量', minWidth:110}
                    ,{field: 'rate', title: '手续费', minWidth:100}
                    ,{field: 'real_number', title: '实际提币', minWidth:110}
                    // ,{field: 'address', title: '提币地址', minWidth:100}
                    ,{field: 'status', title: '状态', minWidth:100, templet: '#statustml'}
                    // ,{field: 'hes_account', title: '承兑商交易账号', minWidth:180}
                    // ,{field: 'money', title: '交易额度', minWidth:100}
                    // ,{field: 'sure_name', title: '交易状态', minWidth:100}
                    ,{field: 'create_time', title: '提币时间', minWidth:180}
                    ,{title:'操作', minWidth:100, toolbar: '#barDemo'}

                ]]
            });

            table.on('tool(test)', function(obj){
                var data = obj.data;
                if(obj.event === 'del'){
                    layer.confirm('真的删除行么', function(index){
                        $.ajax({
                            url:'{{url('admin/cashb_show')}}',
                            type:'post',
                            dataType:'json',
                            data:{id:data.id},
                            success:function (res) {
                                if(res.type == 'error'){
                                    layer.msg(res.message);
                                }else{
                                    obj.del();
                                    layer.close(index);
                                }
                            }
                        });


                    });
                } else if(obj.event === 'show'){
                    layer.open({
                        type: 2,
                        title: '提币信息',
                        shadeClose: true,
                        shade: 0.8,
                        area: ['80%', '80%'],
                        content: '{{url('admin/cashb_show')}}?id='+data.id
                    });
                }
            });

            //监听提交
            form.on('submit(mobile_search)', function(data){
                var account_number = data.field.user_name;
                table.reload('mobileSearch',{
                    where:{account_number:account_number},
                    page: {curr: 1}         //重新从第一页开始
                });
                return false;
            });

        });
    </script>

@endsection