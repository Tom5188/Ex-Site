

<?php $__env->startSection('page-head'); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('page-content'); ?>
    <div class="layui-form">
        <div class="layui-inline">
            <label class="layui-form-label">哈希值</label>
            <div class="layui-input-inline" style="width: 200px">
                <input class="layui-input" name="code" type="text" value="" placeholder="请输入哈希值">
            </div>
        </div>
        <div class="layui-inline">
            <label class="layui-form-label">用户ID</label>
            <div class="layui-input-inline" style="width: 200px">
                <input class="layui-input" name="user_id" type="text" value="" placeholder="请输入用户ID">
            </div>
        </div>
        <div class="layui-inline">
            <label class="layui-form-label">支付情况</label>
            <div class="layui-input-inline" style="width:90px;">
                <select name="is_pay">
                    <option value="">全部</option>
                    <option value="1">已支付</option>
                    <option value="0">未支付</option>
                </select>
            </div>
        </div>
        
        <div class="layui-inline" style="margin-left: 10px;">
            <div class="layui-input-inline" style="width: 60px;">
                <button class="layui-btn" lay-submit="bindSuccessOrderSearch" lay-filter="bindSuccessOrderSearch">查询</button>
            </div>
        </div>
    
        <table id="bindSuccessOrderlist" lay-filter="bindSuccessOrderlist"></table>
        <!--<script type="text/html" id="barBind">-->
        <!--    <a class="layui-btn layui-btn-xs" lay-event="edit"><i class="layui-icon layui-icon-edit"></i> 编辑</a>-->
        <!--</script>-->
        <!--<script type="text/html" id="bindTpl">-->
        <!--    <input type="checkbox" name="status" value="{{d.id}}" lay-skin="switch" lay-text="是|否" lay-filter="sexDemo" {{ d.status == 1 ? 'checked' : '' }}>-->
        <!--</script>-->
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
    <script type="text/html" id="bindSuccessOrderRead">
        {{d.is_read==1 ? '<span class="layui-badge layui-bg-green">'+'已读'+'</span>' : '' }}
        {{d.is_read==0 ? '<span class="layui-badge layui-bg-red">'+'未读'+'</span>' : '' }}
    </script>
    <script type="text/html" id="bindSuccessOrderPay">
        {{d.is_pay==1 ? '<span class="layui-badge layui-bg-green">'+'已支付'+'</span>' : '' }}
        {{d.is_pay==0 ? '<span class="layui-badge layui-bg-red">'+'未支付'+'</span>' : '' }}
    </script>
    <script type="text/html" id="bindSuccessOrderStatus">
        {{d.is_expired==1 ? '<span class="layui-badge layui-bg-green">'+'有效'+'</span>' : '' }}
        {{d.is_expired==0 ? '<span class="layui-badge layui-bg-red">'+'无效'+'</span>' : '' }}
    </script>
    <script type="text/html" id="bindCode">
        
    </script>
    <script>
        
        layui.use(['element', 'form', 'layer', 'table'], function () {
            var element = layui.element
            ,layer = layui.layer
            ,table = layui.table
            ,$ = layui.$
            ,form = layui.form
            //第一个实例
            var bindSuccessOrderlist = table.render({
               
                elem: '#bindSuccessOrderlist'
                ,url: '<?php echo e(url('admin/bind_box/success_order_list')); ?>' //数据接口
                ,page: true //开启分页
                ,limit: 20
                ,limits: [20, 50, 100, 200, 500, 1000]
                ,cols: [[ //表头
                    {field: 'id', title: '订单ID', minWidth:80, sort: true}
                    ,{field: 'code', title: '哈希值', minWidth:350, templet:"bindCode"}
                    ,{field: 'currency_name', title:'交易币种', minWidth:80}
                    ,{field: 'user_id', title:'用户ID', minWidth:110}
                    ,{field: 'is_read', title: '已读情况', minWidth:80,templet:"#bindSuccessOrderRead"}
                    ,{field: 'is_pay', title: '支付情况', minWidth:80, templet:"#bindSuccessOrderPay"}
                    ,{field: 'is_expired', title: '状态', minWidth:80, templet:"#bindSuccessOrderStatus"}
                    ,{field: 'created', title:'创建时间', minWidth:155, templet:function(data){
                        return layui.util.toDateString(data.created*1000,"yyyy-MM-dd HH:mm:ss")
                    }}
                    ,{field: 'overtime', title:'过期时间', minWidth:155, templet:function(data){
                        return layui.util.toDateString(data.overtime*1000,"yyyy-MM-dd HH:mm:ss")
                    }}
                    ,{field: 'pay_time', title:'支付时间', minWidth:155, templet:function(data){
                        return layui.util.toDateString(data.pay_time*1000,"yyyy-MM-dd HH:mm:ss")
                    }}
                    // ,{title: '操作',fixed: 'right', minWidth:50, toolbar: '#barBind'}
                ]]
            });
            // //监听热卖操作
            // form.on('switch(sexDemo)', function(obj){
            //     var id = this.value;
            //     $.ajax({
            //         url:'<?php echo e(url('admin/currency_display')); ?>',
            //         type:'post',
            //         dataType:'json',
            //         data:{id:id},
            //         success:function (res) {
            //             if(res.error != 0){
            //                 layer.msg(res.message);
            //             }
            //         }
            //     });
            // });

            // // 监听工具条
            // table.on('tool(bindSuccessOrderlist)', function (obj) {
            //     var layEvent = obj.event
            //         ,data = obj.data
            //     if (layEvent === 'edit') {
            //         var index = layer.open({
            //             title: '编辑'
            //             ,type: 2
            //             ,content: '<?php echo e(url('/admin/bind_box/edit')); ?>?id=' + data.id
            //             ,area: ['960px', '700px']
            //         });
            //     }
            // });

            //监听提交
            form.on('submit(bindSuccessOrderSearch)', function (data) {
                bindSuccessOrderlist.reload({
                    where: data.field
                    ,page: {
                        curr: 1 //重新从第 1 页开始
                    }
                });
                return false;
            });

        });
    </script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin._layoutNew', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>