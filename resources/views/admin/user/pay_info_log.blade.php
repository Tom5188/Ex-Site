@extends('admin._layoutNew')

@section('page-head')

@endsection

@section('page-content')
<style>
    .status_bg_1{
        background: #1E9FFF;
    }
    .status_bg_2{
        background: #5fb878;
    }
    .status_bg_3{
        background: #ff5722;
    }
</style>
    <div style="margin-top: 10px;width: 100%;">
        

        <form class="layui-form layui-form-pane layui-inline" action="">

            <div class="layui-inline">
                <label class="layui-form-label">用户ID</label>
                <div class="layui-input-inline" style="margin-left: 10px">
                    <input type="text" name="user_id" autocomplete="off" class="layui-input">
                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label">状态</label>
                <div class="layui-input-inline" style="margin-left: 10px">
                    <select name="status">
                        <option value=""></option>
                        <option value="0">未支付</option>
                        <option value="1">支付成功</option>
                  </select>
                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label">日期范围</label>
                <div class="layui-input-inline" style="width: 150px;">
                    <input class="layui-input layui-date" type="text" name="start_time" autocomplete="off" >
                </div>
                <div class="layui-input-inline" style="width: 150px;">
                   <input class="layui-input layui-date" type="text" name="end_time" autocomplete="off" >
                </div>
            </div>
            <!--<div class="layui-form-item">-->
            <!--<label class="layui-form-label">选择框</label>-->
            <!--<div class="layui-input-block">-->
            <!--  <select name="status" lay-verify="required">-->
            <!--        <option value=""></option>-->
            <!--        <option value="0">未支付</option>-->
            <!--        <option value="1">支付成功</option>-->
            <!--      </select>-->
            <!--</div>-->
          <!--</div>-->
            <div class="layui-inline" style="margin-left: 10px">
                <div class="layui-input-inline">
                    <button class="layui-btn" lay-submit="search" lay-filter="search"><i class="layui-icon">&#xe615;</i></button>
                </div>
            </div>
        </form>
       
    </div>

 <!--   <script type="text/html" id="switchTpl">-->
 <!--       <input type="checkbox" name="is_recommend" value="@{{d.id}}" lay-skin="switch" lay-text="是|否" lay-filter="sexDemo" @{{ d.is_recommend == 1 ? 'checked' : '' }}>-->
 <!--   </script>-->

    <table id="demo" lay-filter="demo"></table>
 <!--   <script type="text/html" id="barDemo">-->
    
 <!--   <a class="layui-btn layui-btn-xs" lay-event="show">查看</a>-->
    
 <!--   </script>-->
    <script type="text/html" id="statustml">
        @{{d.status==0 ? '<span class="layui-badge status_bg_1">'+'未支付'+'</span>' : '' }}
        @{{d.status==1 ? '<span class="layui-badge status_bg_2">'+'支付成功'+'</span>' : '' }}
    </script>
	<script type="text/html" id="ophtml">
	   @{{d.status ==1 && d.notify_status == 0 ? '<a class="layui-btn layui-btn-xs" lay-event="edit">关闭通知</a>' : '' }}
    </script>

 <!--   <script type="text/html" id="acc">-->
       
 <!--   </script>-->

@endsection

@section('scripts')
    <script>

        layui.use(['table','form', 'laydate'], function(){
            var table = layui.table;
            var $ = layui.jquery;
            var form = layui.form;
            var laydate = layui.laydate;
            //第一个实例
            table.render({
                elem: '#demo'
                ,url: "{{url('admin/pay/payLog')}}" //数据接口
                ,page: true //开启分页
                ,id:'mobileSearch'
                ,cols: [[ //表头
                    {field: 'id', title: 'ID', width:80, sort: true}
                    ,{field: 'user_id', title: '用户ID', width:100}
                    ,{field: 'country_code', title: '国家', width:80}
                    ,{field: 'currency_code', title: '币种', width:80}
                    ,{field: 'payment_type', title: '支付通道', width:150}
                    ,{field: 'payment_amount', title: '订单金额', width:200}
                    ,{field: 'rate', title: '费率', width:150}
                    ,{field: 'order_id', title: '订单编号', width:200}
                    ,{field: 'status', title: '状态', width:200,templet:"#statustml"}
                    ,{field: 'create_time', title: '创建时间', width:200}
                    // ,{field: 'currency_name', title: '虚拟币', width:80}
                    // ,{field: 'user_account', title: '支付账号', minWidth:110}
                    // ,{field: 'user_account', title: '支付凭证', minWidth:110,templet:"#acc"}
                    // ,{field: 'user_account', title: '封面图', minWidth:110, templet:"#acc"}
                    // ,{field: 'bank_account', title: '银行卡号', minWidth:80,templet:function(d){
                    //     if(d.type){
                    //         return d.bank_account;
                    //     }else{
                    //         return '';
                    //     }
                    // }}
                    // ,{field: 'address', title: '提币地址', minWidth:100}
                    // ,{field: 'amount', title: '数量', minWidth:80}
                    // ,{field: 'give', title: '赠送数量', minWidth:80}
                    // ,{field: 'amount', title: '充值金额￥', minWidth:80,templet:function(d){
                    //     let give = 0;
                    //     if(d.give) give = d.give;
                    //     return (d.amount*d.rmb_relation*d.price) + (give*d.rmb_relation*d.price) +"元";
                    // }}
                    // ,{field: 'hes_account', title: '承兑商交易账号', minWidth:180}
                    // ,{field: 'money', title: '交易额度', minWidth:100}
                    // ,{field: 'status', title: '交易状态', minWidth:100, templet: '#statustml'}
                    // ,{field: 'created_at', title: '充币时间', minWidth:180}
                   
                    ,{title:'操作',minWidth:120,templet: '#ophtml'}

                ]]
            });
            form.on('submit(search)', function (data) {
                var user_id = data.field.user_id;
                var status = data.field.status;
                var start_time = data.field.start_time;
                var end_time = data.field.end_time;
                table.reload('mobileSearch',{
                    where:{user_id:user_id,status:status,start_time:start_time,end_time:end_time},
                    page: {curr: 1}         //重新从第一页开始
                });
                return false;
            });
            table.on('tool(demo)', function (obj) {
                var data = obj.data;
                var layEvent = obj.event;
                var tr = obj.tr;
                var selected = table.checkStatus('demo')
                 if(layEvent === 'edit'){
                     layer.confirm('确认不再提醒吗？', function (index) {
                        //向服务端发送删除指令
                        $.ajax({
                            url: "/admin/pay/close_nofity",
                            type: 'post',
                            dataType: 'json',
                            data: {id: data.id},
                            success: function (res) {
                                if (res.type == 'ok') {
                                    layer.alert(res.message);
                                    layer.close(index);
                                    table.reload('mobileSearch',{
                                        where:{},
                                        page: {curr: 1}         //重新从第一页开始
                                    });
                                    return false;
                                } else {
                                    layer.close(index);
                                    layer.alert(res.message);
                                }
                            }
                        });
                    });
                 }
            });
            $('input.layui-date').each(function () {
                laydate.render({
                    elem: this
                    ,type: 'datetime'
                    ,format: 'yyyy-MM-dd HH:mm:ss'
                });
            });
		});
		
    </script>

@endsection