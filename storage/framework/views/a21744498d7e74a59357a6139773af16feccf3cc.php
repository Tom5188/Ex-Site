<?php $__env->startSection('page-head'); ?>
<style>
    [hide] {
        display: none;
    }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('page-content'); ?>
 <form class="layui-form layui-form-pane layui-inline" action="">

    <div class="layui-inline">
        <!--<label class="layui-form-label"></label>-->
        <div class="layui-input-inline">
            <input type="text" placeholder="ID,手机号，邮箱，姓名" name="account_number" autocomplete="off" class="layui-input">
        </div>
    </div>
    <div class="layui-inline" style="margin-left: 10px">
        <div class="layui-input-inline">
            <button class="layui-btn" lay-submit="" lay-filter="mobile_search"><i class="layui-icon">&#xe615;</i></button>
        </div>
    </div>
</form>
<table id="userlist" lay-filter="userlist"></table>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script type="text/html" id="barDemo">
    <a class="layui-btn layui-btn-xs" lay-event="users_wallet">钱包</a>
    <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="lock_user">锁定</a>
    
    <a class="layui-btn layui-btn-warm layui-btn-xs" lay-event="edit">编辑</a>
    <a class="layui-btn layui-btn-warm layui-btn-xs" lay-event="score">积分</a>
    <a class="layui-btn layui-btn-warm layui-btn-xs" lay-event="licai" hide>理财账户</a>
    <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="delete" >删除</a>
</script>
<script type="text/html" id="switchTpl">
    <input type="checkbox" name="status" value="{{d.id}}" lay-skin="switch" lay-text="是|否" lay-filter="status" {{ d.status == 1 ? 'checked' : '' }}>
</script>
<script type="text/html" id="isAtelier">
    <input type="checkbox" name="is_atelier" value="{{d.id}}" lay-skin="switch" lay-text="是|否" lay-filter="is_atelier" {{ d.is_atelier == 1 ? 'checked' : '' }} disabled>
</script>
<script type="text/html" id="status_t">
    <a class="layui-btn layui-btn-xs {{ d.status == 1 ? 'layui-btn-danger' : 'layui-btn-primary' }} " >{{ d.status == 1 ? '已锁定' : '正　常' }}</a>
</script>
<script type="text/html" id="user_level">
    <p>{{ d.user_level == 0 ? '无' : (d.user_level == 1 ? '红牌会员' : (d.user_level == 2 ? '蓝牌会员' : '金牌会员'))}}</p>
</script>
<script type="text/html" id="bind_box">
        <input type="checkbox" name="is_bind_box_author" value="{{d.id}}" lay-skin="switch" lay-text="是|否" lay-filter="bind_box" {{ d.is_bind_box_author == 1 ? 'checked' : '' }} >
      </script>
<script type="text/html" id="is_trader">
<input type="checkbox" name="is_trader" value="{{d.id}}" lay-skin="switch" lay-text="是|否" lay-filter="is_trader" {{ d.is_trader == 1 ? 'checked' : '' }} >
</script>
<script type="text/html" id="cash_info">
<div style="text-align:center">
<a class="layui-btn layui-btn-primary layui-btn-xs" lay-event="edit_cash_info">{{ d.cash_info ? '修改' : '新增' }}</a>
</div>
</script>
<script type="text/html" id="cash_info_international">
<div style="text-align:center">
<a class="layui-btn layui-btn-primary layui-btn-xs" lay-event="edit_cash_info_international">{{ d.cash_info_international ? '修改' : '新增' }}</a>
</div>
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
            ,url: '/admin/user/list'
            ,page: true
            ,limit: 100
            ,limits: [20, 50, 100, 200, 500, 1000]
            ,height: 'full-60'
            ,cols: [[
                {field: '', type: 'checkbox'}
                ,{field: 'id', title: '用户ID', minWidth: 100}
                ,{field:'phone', title:'手机号', minWidth:150}
                ,{field:'userreal_name', title:'真实姓名', minWidth:120}
                ,{field:'level_text', title:'用户等级', minWidth:110}
                ,{field:'score', title:'积分', minWidth:100}
                ,{field:'account_number', title:'交易账号', minWidth:150, hide: true}
                ,{field:'common', title:'备注', minWidth:150}
                ,{field:'email', title:'邮箱', minWidth:150}
                ,{field:'card_id', title: '身份证号',minWidth: 180, hide: true}
                ,{field:'parent_id', title:'上级ID', minWidth:80}
                ,{field:'extension_code', title:'邀请码', minWidth:80}
                ,{field: 'risk_name', title: '期权控制', minWidth: 90}
                ,{field: 'status', title: '状态', minWidth: 80, templet:"#status_t"}
                ,{field: 'cash_info', title: '银行卡', minWidth: 80, templet:"#cash_info"}
                ,{field: 'cash_info_international', title: '钱包地址', minWidth: 80, templet:"#cash_info_international"}
                // ,{field:'status', title:'是否锁定', width:90, templet: '#switchTpl'}
                ,{field:'is_bind_box_author', title:'开通盲盒作者', minWidth:120, templet: '#bind_box'}
                ,{field:'is_trader', title:'是否为交易员', minWidth:120, templet: '#is_trader'}
                ,{field:'time', title:'注册时间', minWidth:150}
                ,{fixed: 'right', title: '操作', minWidth:330, align: 'center', toolbar: '#barDemo'}
            ]]
        });

        $('input[name=account]').keypress(function (event) {
            if (event.charCode == 13) {
                $('#mobile_search').click();
            }
        });

        /*$('#add_user').click(function(){layer_show('添加会员', '/admin/user/add');});*/

      
     //开通盲盒作者
                        form.on('switch(bind_box)', function(obj){
                            var id = this.value;
                            
                            $.ajax({
                                url:'<?php echo e(url('admin/user/set_bind_box')); ?>',
                                type:'post',
                                dataType:'json',
                                data:{id:id},
                                success:function (res) {
                                    layer.msg(res.message);
                                   
                                }
                            });
                        });
        //修改是否为交易员
        form.on('switch(is_trader)', function(obj){
            var id = this.value;
            
            $.ajax({
                url:'<?php echo e(url('admin/user/trader')); ?>',
                type:'post',
                dataType:'json',
                data:{user_id:id},
                success:function (res) {
                    layer.msg(res.message);
                   
                }
            });
        });
                        
        //监听锁定操作
        form.on('switch(status)', function(obj){
            var id = this.value;
            $.ajax({
                url:'<?php echo e(url('admin/user/lock')); ?>',
                type:'post',
                dataType:'json',
                data:{id:id},
                success:function (res) {
                    layer.msg(res.message);
                }
            });
        });

        $('#btn-set').click(function () {
            var checkStatus = table.checkStatus('userlist');
            var risk = $('#risk').val();
            var ids = [];
            try {
                if (checkStatus.data.length <= 0) {
                    throw '请先选择用户';
                }
                if (risk <= -2) {
                    throw '请选择输赢类型';
                }
                checkStatus.data.forEach(function (item, index, arr) {
                    ids.push(item.id);
                });
                $.ajax({
                    url: '/admin/user/batch_risk'
                    ,type: 'POST'
                    ,data: {risk: risk, ids: ids}
                    ,success: function (res) {
                        layer.msg(res.message, {
                            time: 2000,
                            end: function () {
                                if (res.type == 'ok') {
                                    user_table.reload();
                                }
                            }
                        });
                    }
                    ,error: function (res) {
                        layer.msg('网络错误');
                    }
                })
                
            } catch (error) {
                layer.msg(error);
            }
        });
        //监听提交
        form.on('submit(mobile_search)', function(data){
            var account_number = data.field.account_number;
            table.reload('userlist',{
                where:{account:account_number},
                page: {curr: 1}         //重新从第一页开始
            });
            return false;
        });
        //监听工具条
        table.on('tool(userlist)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var data = obj.data;
            var layEvent = obj.event;
            var tr = obj.tr;
            if (layEvent === 'delete') { //删除
                layer.confirm('真的要删除吗？', function (index) {
                    //向服务端发送删除指令
                    $.ajax({
                        url: "/admin/user/del",
                        type: 'post',
                        dataType: 'json',
                        data: {id: data.id},
                        success: function (res) {
                            if (res.type == 'ok') {
                                obj.del(); //删除对应行（tr）的DOM结构，并更新缓存
                                layer.alert(res.message);
                                layer.close(index);
                            } else {
                                layer.close(index);
                                layer.alert(res.message);
                            }
                        }
                    });
                });
            } else if (layEvent === 'edit') { //编辑
                layer_show('编辑会员','/admin/user/edit?id=' + data.id);
            } else if (layEvent === 'users_wallet') {
                var index = layer.open({
                    title: '钱包管理'
                    ,type: 2
                    ,content: '/admin/user/users_wallet?id=' + data.id
                    ,maxmin: true
                });
                layer.full(index);
            } else if (layEvent == 'candy_change') {
                var index = layer.open({
                    title: '通证调节'
                    ,type: 2
                    ,content: '/admin/user/candy_conf/' + data.id
                    ,maxmin: true
                });
                layer.full(index);
            }else if (layEvent == 'score') {
                var index = layer.open({
                    title: '积分调节'
                    ,type: 2
                    ,content: '/admin/user/score?id=' + data.id
                    ,maxmin: true
                    ,area:["80%", "80%"],
                });
                // layer.full(index);
            } else if (layEvent === 'lock_user') {
                var index = layer.open({
                    title: '用户锁定'
                    ,type: 2
                    ,content: '/admin/user/lock?id=' + data.id
                    ,maxmin: true
                    ,area:["380px", "430px"],
                });
            }else if(layEvent=="licai"){
                $.ajax({
                    url:'<?php echo e(url('admin/ybb/account')); ?>',
                    type: 'get',
                    dataType: 'json',
                    data: {
                        user_id:data.id
                    },
                    success: function (res) {
                        if(res.message){
                            var index = layer.open({
                                title: '编辑理财账户'
                                ,type: 2
                                ,content: '/admin/ybb/account/edit/view?id=' + data.id+"&account="+data.account_number
                                ,maxmin: true
                                ,area:["580px", "430px"],
                            });
                        }else{
                            layer.msg('旧账号不存在理财账户')
                        }
                    }
                });
            }else if (layEvent == 'edit_cash_info') {
                var index = layer.open({
                    title: '银行卡'
                    ,type: 2
                    ,content: '/admin/user/cash_info?id=' + data.id
                    ,maxmin: true
                    ,area:["80%", "80%"],
                });
            }else if (layEvent == 'edit_cash_info_international') {
                var index = layer.open({
                    title: '钱包地址'
                    ,type: 2
                    ,content: '/admin/user/userWalletList?id=' + data.id
                    ,maxmin: true
                    ,area:["80%", "80%"],
                });
            }
        });
    });
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin._layoutNew', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>