

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

<div class="layui-form">
    <!--<button class="layui-btn layui-btn-normal layui-btn-radius" onclick="layer_show('添加双币项目','<?php echo e(url('admin/dual/add')); ?>')">添加双币项目</button>-->
    
    <!--搜索-->
    <div class="layui-inline">
        <label class="layui-form-label">用户ID</label>
        <div class="layui-input-inline" style="width: 200px">
            <input class="layui-input" name="user_id" type="text" value="" placeholder="请输入用户ID">
        </div>
    </div>
    <div class="layui-inline">
        <label class="layui-form-label">消耗币种</label>
        <div class="layui-input-inline" style="width:90px;">
            <select name="currency_id">
                <option value="">全部</option>
                <option value="1">BTC</option>
                <option value="2">ETH</option>
                <option value="3">USDT</option>
            </select>
        </div>
    </div>
    <div class="layui-inline" style="margin-left: 10px;">
        <div class="layui-input-inline" style="width: 60px;">
            <button class="layui-btn" lay-submit="search" lay-filter="search"><i class="layui-icon">&#xe615;</i></button>
        </div>
    </div>
</div>
     
    
    <table id="orderList" lay-filter="test"></table>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
    
    <script type="text/html" id="statusCurreny">
        {{d.currency_id==1 ? '<span class="">'+'BTC'+'</span>' : '' }}
        {{d.currency_id==2 ? '<span class="">'+'ETH'+'</span>' : '' }}
        {{d.currency_id==3 ? '<span class="">'+'USDT'+'</span>' : '' }}
    </script>
    <script>
        layui.use(['table','form'], function(){
            var table = layui.table;
            var $ = layui.jquery;
            var form = layui.form;
            //第一个实例
            var orderList = table.render({
                elem: '#orderList'
                ,toolbar: '#toolbar'
                ,url: '<?php echo e(url('admin/dual/order_lists')); ?>' //数据接口
                ,page: true //开启分页
                // ,id:'mobileSearch'
                ,cols: [[ //表头
                    {field: 'id', title: '订单ID', minWidth:110, sort: true}
                    ,{field: 'user_id', title: '用户ID', minWidth: 110}
                    ,{field: 'name', title: '理财名称', minWidth:200}
                    ,{field: 'order_rate', title: '年化利率', minWidth:110}
                    ,{field: 'day', title: '持仓期限', minWidth:200}
                    ,{field: 'number', title: '购买份数', minWidth:110}
                    ,{field: 'price', title: '购买时价格', minWidth:110}
                    ,{field: 'total', title: '总计', minWidth:110}
                    ,{field: 'currency_id', title: '购买消耗币种', minWidth:110, templet: '#statusCurreny'}
                     ,{field: 'created', title: '创建时间', minWidth:110}
                    // ,{title:'操作',width:80,toolbar: '#barorderList'}
                ]]
            });

            //监听提交
            form.on('submit(search)', function (data) {
                orderList.reload({
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