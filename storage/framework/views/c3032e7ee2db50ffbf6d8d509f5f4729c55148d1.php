

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
            <label class="layui-form-label">交易币种</label>
            <div class="layui-input-inline" style="width: 150px">
                <select name='currency_id'>
                    <option class='layui-option' value="" >请选择</option>
                <?php $__currentLoopData = $currency; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option class='layui-option' value="<?php echo e($value['id']); ?>" ><?php echo e($value['name']); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
        </div>
        <!--<div class="layui-inline">-->
        <!--    <label class="layui-form-label">交易类型</label>-->
        <!--    <div class="layui-input-inline" style="width:90px;">-->
        <!--        <select name="pay_type">-->
        <!--            <option value="">全部</option>-->
        <!--            <option value="1">一口价</option>-->
        <!--            <option value="2">竞拍</option>-->
        <!--            <option value="3">盲盒</option>-->
        <!--        </select>-->
        <!--    </div>-->
        <!--</div>-->
        <div class="layui-inline">
            <label class="layui-form-label">名称</label>
            <div class="layui-input-inline" style="width: 200px">
                <input class="layui-input" name="name" type="text" value="" placeholder="请输入名称">
            </div>
        </div>
        <div class="layui-inline">
            <label class="layui-form-label">买入者ID</label>
            <div class="layui-input-inline" style="width: 200px">
                <input class="layui-input" name="buyer_id" type="text" value="" placeholder="请输入买入者ID">
            </div>
        </div>
        <div class="layui-inline">
            <label class="layui-form-label">卖出者ID</label>
            <div class="layui-input-inline" style="width: 200px">
                <input class="layui-input" name="sell_id" type="text" value="" placeholder="请输入卖出者ID">
            </div>
        </div>
        <div class="layui-inline">
            <label class="layui-form-label">创作者ID</label>
            <div class="layui-input-inline" style="width: 200px">
                <input class="layui-input" name="author_id" type="text" value="" placeholder="请输入创作者ID">
            </div>
        </div>
        <div class="layui-inline" style="margin-left: 10px;">
            <div class="layui-input-inline" style="width: 60px;">
                <button class="layui-btn" lay-submit="bindOrderSearch" lay-filter="bindOrderSearch">查询</button>
            </div>
        </div>
    
        <table id="bindOrderlist" lay-filter="bindOrderlist"></table>
        <!--<script type="text/html" id="barBind">-->
        <!--    <a class="layui-btn layui-btn-xs" lay-event="edit"><i class="layui-icon layui-icon-edit"></i> 编辑</a>-->
        <!--</script>-->
        <!--<script type="text/html" id="bindTpl">-->
        <!--    <input type="checkbox" name="status" value="{{d.id}}" lay-skin="switch" lay-text="是|否" lay-filter="sexDemo" {{ d.status == 1 ? 'checked' : '' }}>-->
        <!--</script>-->
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
    <script type="text/html" id="bindOrderStatus">
        {{d.status==1 ? '<span class="layui-badge layui-bg-green">'+'有效'+'</span>' : '' }}
        {{d.status==0 ? '<span class="layui-badge layui-bg-red">'+'无效'+'</span>' : '' }}
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
            var bindOrderlist = table.render({
               
                elem: '#bindOrderlist'
                ,url: '<?php echo e(url('admin/bind_box/orderList')); ?>' //数据接口
                ,page: true //开启分页
                ,limit: 20
                ,limits: [20, 50, 100, 200, 500, 1000]
                ,cols: [[ //表头
                    {field: 'id', title: '订单ID', minWidth:50, sort: true}
                    ,{field: 'code', title: '哈希值', minWidth:350, templet:"bindCode"}
                    ,{field: 'buyer_id', title: '买入者ID', minWidth:85}
                    ,{field: 'sell_id', title: '卖出者ID', minWidth:100}
                    ,{field: 'author_id', title: '创作者ID', minWidth:110}
                    ,{field: 'status', title: '状态', minWidth:80, templet:"bindOrderStatus"}
                    ,{field: 'order_price', title: '成交价', minWidth:110, sort: true}
                    ,{field: 'currency_name', title:'交易币种', minWidth:80}
                    // ,{field: 'updated', title:'修改时间', minWidth:150}
                    ,{field: 'created', title:'成交时间', minWidth:150}
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
            // table.on('tool(bindOrderlist)', function (obj) {
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
            form.on('submit(bindOrderSearch)', function (data) {
                bindOrderlist.reload({
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