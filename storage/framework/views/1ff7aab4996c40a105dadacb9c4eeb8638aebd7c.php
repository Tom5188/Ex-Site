

<?php $__env->startSection('page-head'); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('page-content'); ?>

    <div class="layui-fluid">
        <div class="layui-card">
            <div class="layui-form layui-card-header layuiadmin-card-header-auto" lay-filter="layadmin-userfront-formlist">
                <div class="layui-form-item">
                    <!--<div class="layui-inline">-->
                    <!--    <label class="layui-form-label">ID</label>-->
                    <!--    <div class="layui-input-block">-->
                    <!--        <input type="text" name="id" placeholder="请输入" autocomplete="off" class="layui-input">-->
                    <!--    </div>-->
                    <!--</div>-->
                    <!--<div class="layui-inline">-->
                    <!--    <label class="layui-form-label">用户</label>-->
                    <!--    <div class="layui-input-block">-->
                    <!--        <input type="text" name="mobile" placeholder="请输入" autocomplete="off" class="layui-input">-->
                    <!--    </div>-->
                    <!--</div>-->
                    <!--<div class="layui-inline">-->
                    <!--    <label class="layui-form-label">状态</label>-->
                    <!--    <div class="layui-input-inline">-->
                    <!--       <select name="status"  class="layui-input">-->
                    <!--           <option value="">选择状态</option>-->
                    <!--           <option value="1">进行中</option>-->
                    <!--           <option value="2">到期</option>-->
                    <!--           <option value="3">到期</option>-->
                    <!--       </select>-->
                    <!--    </div>-->
                    <!--</div>-->
                    <!--<div class="layui-inline">-->
                    <!--    <button class="layui-btn layuiadmin-btn-useradmin" lay-submit lay-filter="searchtj">-->
                    <!--        <i class="layui-icon layui-icon-search layuiadmin-button-btn"></i>-->
                    <!--    </button>-->
                    <!--</div>-->
                    <button class="layui-btn layui-btn-normal layui-btn-radius" id="add_project">添加项目</button>
                </div>
            </div>

            <div class="layui-card-body">

                <table id="boss" lay-filter="boss"></table>
            </div>
        </div>
    </div>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
    <script type="text/html" id="barDemo">
       <a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="edit"><i class="layui-icon layui-icon-friends"></i>编辑</a>
       <a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="check"><i class="layui-icon layui-icon-friends"></i>查看订单</a>
    </script>

    <script>
        layui.use(['table','form'], function(){
            // console.log(layui.setter.base)
            var table = layui.table;
            var $ = layui.jquery;
            var form = layui.form;
            $('#add_project').click(function() {
                var index = layer.open({
                    title:'添加项目'
                    ,type:2
                    ,content: '/admin/currency/project/detail/view'
                    ,area: ['100%', '100%']
                    ,maxmin: true
                    ,anim: 3
                });
            });
             //第一个实例
            table.render({
                elem: '#boss'
                ,url: '/admin/currency/project/list' //数据接口
                ,page: true //开启分页
                ,id:'mobileSearch'
                ,cols: [[ //表头
                    {field: 'id', title: 'ID', width:80, sort: true}

                     ,{field: 'currency_name', title: '币种', width:100}
                    ,{field: 'title', title: '标题', width:200}
                    ,{field: 'summary', title: '摘要', width:200}
                    ,{field:'amount',title: '发行总量', width:200}
                    ,{field: 'price', title: '价格', width:100}
                    ,{field: 'start_at', title: '开始时间', width:200}
                    ,{field: 'end_at', title: '结束时间',width:200}
                    ,{ title: '状态',templet:function(d){

                        if(d.time_status==1){
                            return '<span style="color:#00c087">未开始</span>';
                        }else if(d.time_status==2){
                            return '<span style="color:#f00">进行中</span>';
                        }else{
                            return '<span style="color:#f00">已结束</span>';
                        }
                    }
                    }
                     ,{title:'操作',minWidth:100,toolbar: '#barDemo'}
                ]]
            });
            //监听提交
            form.on('submit(searchtj)', function(data){
                // var seller_name = data.field.seller_name
                var account_number = data.field.mobile,
                    status= data.field.status,
                    is_cancel=0;
                    if(status==2){
                        is_cancel=1;
                    }
                    if(status==3){
                        status=2;
                        is_cancel=0;
                    }
                    if(status==1 || status==""){
                        is_cancel='';
                    }
                    // ,currency_id = $('#currency_id').val()

                table.reload('mobileSearch',{
                    where:{
                        // is_cancel:is_cancel,
                        account_number:account_number,
                    	status:status,
                        // currency_id:currency_id,
                    },
                    page: {curr: 1}         //重新从第一页开始
                });
                return false;
            });
            table.on('tool(boss)', function(obj){
                var data = obj.data;
                if(obj.event === 'edit'){
                    var index = layer.open({
                        title:'修改项目'
                        ,type:2
                        ,content: '/admin/currency/project/detail/view?id='+data.id
                        ,area: ['100%', '100%']
                        ,maxmin: true
                        ,anim: 3
                    });
                // 	table.reload('mobileSearch',{
                //     where:{
	               //         p_id:data.id,
	               //         // currency_id:currency_id,
	               //     },
	               //     page: {curr: 1}         //重新从第一页开始
	               // });
                }

                if(obj.event == 'check'){
                    var index = layer.open({
                        title:'查看订单'
                        ,type:2
                        ,content: '/admin/currency/project/order/view?id='+data.id
                        ,area: ['100%', '100%']
                        ,maxmin: true
                        ,anim: 3
                    });
                }
            });
        });
    </script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('agent.layadmin', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>