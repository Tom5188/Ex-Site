
@extends('manages.layadmin')

@section('page-head')

@endsection

@section('page-content')
    <div class="layui-fluid" id="LAY-app-message">
        <div class="layui-card">
            <div class="layui-card-header">权限列表</div>
            <div class="layui-card-body layui-text">
                <div class="clt-auth-btns" style="margin-bottom: 10px;">
                    <button class="layui-btn layui-btn-sm" data-events="add">添加权限</button>
                </div>
                <table class="layui-table" id="list" lay-filter="list"></table>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script type="text/html" id="status">
        <input type="checkbox" name="status" value="@{{d.id}}" lay-skin="switch" lay-text="是|否" lay-filter="status" @{{ d.status == 1 ? 'checked' : '' }}>
    </script>
    <script>
        layui.use(['table','form'], function(){
            var $ = layui.$, admin = layui.admin, table = layui.table, element = layui.element,form = layui.form;
            //第一个实例
            table.render({
                elem: '#list'
                ,url: '{{url('admin/level_list')}}' //数据接口
                ,page: true //开启分页
                ,id:'mobileSearch'
                ,height: 'full-100'
                ,cols: [[ //表头
                    {field: 'id', title: 'ID', minWidth:80, sort: true}
                    ,{field: 'name', title: '名称', minWidth:80}
                    ,{field: 'fill_currency', title: '充币数量', minWidth:80}
                    ,{field: 'direct_drive_count', title: '直推数量', minWidth:80}
                    ,{field: 'direct_drive_price', title: '直推金额', minWidth:80}
                    ,{field: 'max_algebra', title: '最大代数', minWidth:80}
                    ,{field: 'level', title: '级别', minWidth:80}
                    ,{title:'操作',minWidth:100,toolbar: '#barDemo'}

                ]]
            });
            //添加权限页面
            var events = {
                add: function(){
                    alert(222)
                    layer.open({
                        type: 2,
                        content: '/manages/auth/add',
                        area: ['90%', '90%'],
                        maxmin: true
                    });
                },
            };

        });
    </script>

@endsection
