
<?php $__env->startSection('page_head'); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('page-content'); ?>

    

    <table class="layui-hide" id="adminUsers" lay-filter="adminList"></table>


    <script type="text/html" id="switchTpl">
        <input type="checkbox" name="status" value="{{d.id}}" lay-skin="switch" lay-text="是|否" lay-filter="status" {{ d.status == 1 ? 'checked' : '' }}>
    </script>
    <script type="text/html" id="barDemo">
        {{#if(d.status == 1) { }}
        <a class="layui-btn layui-btn-xs" lay-event="edit">
            未审核
        </a>
        {{#} else if(d.status == 2){ }}
        <span class="layui-btn layui-btn-xs layui-btn-disabled">
           已审核
        </span>
        {{#} else if(d.status == 3){ }}
        <span class="layui-btn layui-btn-xs layui-btn-disabled">
           审核不通过
        </span>
        {{#}}}


        
        
        
    </script>
    <script type="text/html" id="statustpl">
        {{#if(d.status == 1) { }}
        未审核
        {{#} else if(d.status == 2) { }}
        已审核通过
        {{#} else if(d.status == 3) { }}
        审核不通过
        {{#}else{}}
        {{#}}}
    </script>
    
        
        
        
        
        
    
<?php $__env->stopSection(); ?>
<?php $__env->startSection('scripts'); ?>
    <script type="text/javascript">
        window.onload = function () {
            layui.use(['layer', 'table'], function () { //独立版的layer无需执行这一句
                var $ = layui.jquery;
                var layer = layui.layer; //独立版的layer无需执行这一句
                var table = layui.table;
                var form = layui.form;
//                $('#add_admin').click(function(){layer_show('修改购买上限', '/admin/carrules/showseting');});
                table.render({
                    elem: '#adminUsers',
                    url: '/admin/levertolegal/list',
                    page: true,
                    cols: [[
                        {field: 'id', title: 'ID', minWidth: 100, sort: true},
                        {field: 'phone', title: '用户', minWidth: 150},
                        {field: 'number', title: '划转数量', minWidth: 150},
                        {field: 'type', title: '类型', minWidth: 150},
                        {field:'status',title:'状态', minWidth:250, templet: '#statustpl'},
                        {field: 'add_time', title: '购买时间', minWidth: 150},
                        {fixed: 'right', title: '操作', minWidth: 150, align: 'center', toolbar: '#barDemo'}
                    ]]
                });

                
                    
                    
                        
                        
                        
                        
                        
                            
                        
                    
                

  
                //监听工具条
                table.on('tool(adminList)', function(obj){ //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
                    var data = obj.data; //获得当前行数据
                    var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
                    var tr = obj.tr; //获得当前行 tr 的DOM对象

                    if(layEvent === 'del'){ //删除
                        layer.confirm('真的要删除吗？', function(index){
                            //向服务端发送删除指令
                            $.ajax({
                                url:'/admin/carrules/del',
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
                    } else if(layEvent === 'edit'){ //审核
                        //do something
                            layer_show('审核', '/admin/levertolegal/addshow?id=' + data.id);
                    }else if(layEvent === 'detail'){ //编辑
                        //do something
                            layer_show('众筹明细', '/admin/carrules/detail?id=' + data.id);
                    }
                });


            });


        }

    </script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin._layoutNew', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>