<?php $__env->startSection('page-head'); ?>
<style>
    p.percent {
        text-align: right;
        margin-right: 10px;
    }
    p.percent::after {
        content: '%';
    }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('page-content'); ?>
    <table id="demo" lay-filter="test"></table>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
    <script type="text/html" id="barDemo">
        <a class="layui-btn layui-btn-xs" lay-event="edit"><i class="layui-icon layui-icon-edit"></i> 编辑</a>
        {{# if (d.is_legal == 1) { }}
        <a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="match">交易对</a>
        {{# } }}

    </script>
    <script type="text/html" id="toolbar">
        <div>
            <button class="layui-btn layui-btn-sm layui-btn-primary" lay-event="add"> <i class="layui-icon layui-icon-add-1"></i> 添加</button>
        </div>
    </script>
    <script type="text/html" id="switchTpl">
        <input type="checkbox" name="is_display" value="{{d.id}}" lay-skin="switch" lay-text="是|否" lay-filter="isDisplay" {{ d.is_display == 1 ? 'checked' : '' }}>
    </script>
    <script type="text/html" id="micro">
        <input type="checkbox" name="is_micro" value="{{d.id}}" lay-skin="switch" lay-text="是|否" lay-filter="isMicro" disabled {{ d.is_micro == 1 ? 'checked' : '' }}>
    </script>
     <script type="text/html" id="match">
        <input type="checkbox" name="is_match" value="{{d.id}}" lay-skin="switch" lay-text="是|否" lay-filter="isMatch" disabled {{ d.is_match == 1 ? 'checked' : '' }}>
    </script>
    <script type="text/html" id="legal">
        <input type="checkbox" name="is_legal" value="{{d.id}}" lay-skin="switch" lay-text="是|否" lay-filter="isLegal" disabled {{ d.is_legal == 1 ? 'checked' : '' }}>
    </script>
    <script type="text/html" id="lever">
        <input type="checkbox" name="is_lever" value="{{d.id}}" lay-skin="switch" lay-text="是|否" lay-filter="isLever" disabled {{ d.is_lever == 1 ? 'checked' : '' }}>
    </script>
    <script type="text/html" id="insurancable">
        <input type="checkbox" name="insurancable" value="{{d.id}}" lay-skin="switch" lay-text="是|否" lay-filter="isInsurancable"  {{ d.insurancable == 1 ? 'checked' : '' }}>
    </script>
    <script type="text/html" id="is_transfer">
        <input type="checkbox" name="insurancable" value="{{d.id}}" lay-skin="switch" lay-text="是|否" lay-filter="is_transfer"  {{ d.is_transfer == 1 ? 'checked' : '' }}>
    </script>
    <script>
        layui.use(['table','form'], function(){
            var table = layui.table;
            var $ = layui.jquery;
            var form = layui.form;
            //第一个实例
            table.render({
                elem: '#demo'
                ,toolbar: '#toolbar'
                ,url: '<?php echo e(url('admin/currency_list')); ?>' //数据接口
                ,page: true //开启分页
                ,id:'mobileSearch'
                ,cols: [[ //表头
                    {field: 'id', title: 'id', minWidth:60, sort: true}
                    ,{field: 'name', title: '名称', minWidth:90}
                    ,{field: 'min_number', title: '最少提币量', minWidth:60}
                    ,{field: 'rate', title: '提币费率', minWidth:100}
                    ,{field: 'get_address', title: '合约地址', minWidth:150, hide: true}
                    ,{field: 'sort', title: '排序', minWidth:60}
                    ,{field: 'type', title: '基于', minWidth:80, templet: '#typetml'}
                    ,{field: 'is_legal', title: '法币', minWidth:80, templet: '#legal'}
                    ,{field: 'is_lever', title: '杠杆币', minWidth:80, templet: '#lever'}
                    ,{field: 'is_micro', title: '期权交易', minWidth: 90, templet: '#micro'}
                    ,{field: 'is_match', title: '币币', minWidth: 90, templet: '#match'}
                    ,{field: 'is_transfer', title: '币币划转', minWidth: 110, templet: '#is_transfer'}
                    
                    ,{field: 'insurancable', title: '是否购买保险', minWidth: 40, templet: '#insurancable'}
                    ,{field:'is_display', title:'显示', minWidth:85, templet: '#switchTpl', unresize: true}
                    //,{field: 'create_time', title: '添加时间', minWidth:160}
                    ,{title:'操作',width:340,toolbar: '#barDemo'}
                ]]
            });
            //监听是否显示操作
            form.on('switch(isDisplay)', function(obj){
                var id = this.value;
                $.ajax({
                    url:'<?php echo e(url('admin/currency_display')); ?>',
                    type:'post',
                    dataType:'json',
                    data:{id:id},
                    success:function (res) {
                        if(res.error != 0){
                            layer.msg(res.message);
                        }
                    }
                });
            });
        //监听是否购买保险操作
            form.on('switch(isInsurancable)', function(obj){
                var id = this.value;
                $.ajax({
                    url:'<?php echo e(url('admin/is_insurancable')); ?>',
                    type:'post',
                    dataType:'json',
                    data:{id:id},
                    success:function (res) {
                        if(res.error != 0){
                            layer.msg(res.message);
                        }
                    }
                });
            });
            
            //是否开启币币划转
            form.on('switch(is_transfer)', function(obj){
                var id = this.value;
                $.ajax({
                    url:'<?php echo e(url('admin/is_transfer')); ?>',
                    type:'post',
                    dataType:'json',
                    data:{id:id},
                    success:function (res) {
                        if(res.error != 0){
                            layer.msg(res.message);
                        }
                    }
                });
            });
            
            table.on('toolbar(test)', function (obj) {
                switch (obj.event) {
                    case 'add':
                        layer.open({
                            title: '添加币种'
                            ,type: 2
                            ,content: '/admin/currency_add'
                            ,area: ['480px', '650px']
                        });
                        break;
                    default:
                        break;
                }
            });

            table.on('tool(test)', function(obj){
                var data = obj.data;
                if(obj.event === 'del'){
                    layer.confirm('真的删除行么', function(index){
                        $.ajax({
                            url:'<?php echo e(url('admin/currency_del')); ?>',
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
                } else if(obj.event === 'edit'){
                    layer_show('编辑币种','<?php echo e(url('admin/currency_add')); ?>?id='+data.id);
                } else if (obj.event == 'execute'){
                    layer.confirm('确定执行上币脚本？', function(index){
                        $.ajax({
                            url:'<?php echo e(url('admin/currency_execute')); ?>',
                            type:'post',
                            dataType:'json',
                            data:{id:data.id},
                            success:function (res) {
                                layer.msg(res.message);
                            }
                        });
                    });
                } else if (obj.event == 'match') {
                    layer.open({
                        title: '交易对管理'
                        ,type: 2
                        ,content: '/admin/currency/match/' + data.id
                        ,area: ['960px', '600px']
                    });
                } else if(obj.event == 'deposit'){
                    layer.open({
                        title: '质押管理'
                        ,type: 2
                        ,content: '/admin/currency/deposit/' + data.id
                        ,area: ['960px', '600px']
                    });
                }
            });

            //监听提交
            form.on('submit(mobile_search)', function(data){
                var account_number = data.field.account_number;
                table.reload('mobileSearch',{
                    where:{account_number:account_number},
                    page: {curr: 1}         //重新从第一页开始
                });
                return false;
            });

        });
    </script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin._layoutNew', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>