@extends('admin._layoutNew')

@section('page-head')
<style>
    [hide] {
        display: none;
    }
</style>
@endsection

@section('page-content')
<table id="userlist" lay-filter="userlist"></table>
@endsection

@section('scripts')

<script type="text/html" id="barDemo">
    <a class="layui-btn layui-btn-xs" lay-event="edit">编辑</a>
</script>
<script type="text/html" id="pic">

    @{{# if(d.pic){ }}
        <img src="@{{d.pic}}" style="width: 30px" onmouseover="layer.tips('<div style=\'background-color: #fff;\'><img style=\'max-width: 100px;\' src=@{{d.pic}}></div>',this,{tips: [1, '#fff']});" onmouseout="layer.closeAll();">
    @{{# } }}

</script>

<script>        
    layui.use(['element', 'form', 'layer', 'table'], function () {
        var element = layui.element
            ,layer = layui.layer
            ,table = layui.table
            ,$ = layui.$
            ,form = layui.form
        var user_table = table.render({
            elem: '#userlist'
            ,toolbar: true
            ,url: '/admin/user/level/list'
            ,page: false
            ,method:'post'
            ,cols: [[
                {field: 'id', title: 'ID', width: 60}
                ,{field:'name', title:'级别名称', width:120}
                ,{field:'pic', title:'级别徽章', width:120,templet:"#pic"}
                ,{field:'amount', title:'升级金额', width:120}
                ,{field:'give', title:'赠送比例(%)', width:120}
                ,{field:'updated_at', title:'更新时间', width:180}
                ,{fixed: 'right', title: '操作', width:100, align: 'center', toolbar: '#barDemo'}
            ]]
        });

        //监听工具条
        table.on('tool(userlist)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var data = obj.data;
            var layEvent = obj.event;
            if (layEvent == 'edit') {
                var index = layer.open({
                    title: '编辑用户级别'
                    ,type: 2
                    ,content: '/admin/user/level/edit?id=' + data.id
                    ,maxmin: true
                    ,area:["80%", "80%"],
                });
                // layer.full(index);
            }
        });
    });
</script>
@endsection