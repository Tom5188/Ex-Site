<?php $__env->startSection('page-head'); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('page-content'); ?>
	 <div class="layui-fluid">
        <div class="layui-card">
            <div class="layui-form layui-card-header layuiadmin-card-header-auto" lay-filter="layadmin-userfront-formlist">
                <div class="layui-form-item">
                    
                    <button class="layui-btn layui-btn-normal layui-btn-radius" id="add_project">添加项目</button>
                </div>
            </div>

    <table id="data_table" lay-filter="data_table"></table>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('scripts'); ?>
    <script type="text/html" id="barDemo">
        <a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="edit"><i class="layui-icon layui-icon-edit"></i> 编辑</a>
        <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="delete"><i class="layui-icon layui-icon-delete"></i> 删除</a>
    </script>
    <script>
         $('#add_project').click(function() {
                var index = layer.open({
                    title:'添加项目'
                    ,type:2
                    ,content: '/admin/deposit/config/add/view'
                    ,area: ['100%', '100%']
                    ,maxmin: true
                    ,anim: 3
                });
            });
        let url=window.location.href;
        let configId="";
        if(url.indexOf("id")!=-1){
            configId=url.substr(url.lastIndexOf("/")+1);
        }
      
        layui.use(['table', 'layer', 'form'], function() {
            var table = layui.table
                ,layer = layui.layer
                ,form = layui.form
                ,$ = layui.$
            var data_table = table.render({
                elem: '#data_table'
                ,url: '/admin/deposit/config'
                ,height: 'full'
                ,toolbar: 'default'
                ,page: true
                ,cols: [[
                    {field: 'id', title: 'id'}
                    ,{field: 'currency_name', title: '币种'}
                    ,{field: 'intro', title: '储蓄名称'}
                    ,{field: 'day', title: '储蓄期限'}
                    ,{field: 'interest_rate', title: '日利息（%）'}
                    ,{field: 'save_min', title: '最小存币数'}
                    ,{field: 'save_max', title: '最大存币数'}
                    ,{field: 'created_at', title: '创建时间'}
                    ,{fixed: 'right', title: '操作', align: 'center', toolbar: '#barDemo'}
                ]]
            });
            table.on('tool(data_table)', function (obj) {
                var data = obj.data;
                var layEvent = obj.event;
                var tr = obj.tr;
                var selected = table.checkStatus('data_table')
                if (layEvent === 'delete') { //删除
                    layer.confirm('真的要删除吗？', function (index) {
                        //向服务端发送删除指令
                        $.ajax({
                            url: "/admin/deposit/config/delete",
                            type: 'post',
                            dataType: 'json',
                            data: {id: data.id},
                            success: function (res) {
                                if (res.type == 'ok') {
                                    obj.del(); //删除对应行（tr）的DOM结构，并更新缓存
                                    layer.close(index);
                                } else {
                                    layer.close(index);
                                    layer.alert(res.message);
                                }
                            }
                        });
                    });
                }
                 if(layEvent === 'edit'){
                        id = data.id;
                        layer.open({
                            title: '编辑质押'
                            ,type: 2
                            ,content: '/admin/deposit/config/edit/view?id='+id
                            ,area: ['600px', '500px']
                        });
                    
                 }
            });
          
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin._layoutNew', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>