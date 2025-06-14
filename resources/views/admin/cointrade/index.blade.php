@extends('admin._layoutNew')


@section('page-head')
<style>
    [hide] {
        display: none;
    }
</style>
@endsection

@section('page-content')

    <div class="layui-fluid">
        <div class="layui-card">
            <div class="layui-form  layuiadmin-card-header-auto" lay-filter="layadmin-userfront-formlist">
                <div class="layui-form-item">
                    <!--<div class="layui-inline">-->
                    <!--    <label class="layui-form-label">ID</label>-->
                    <!--    <div class="layui-input-block">-->
                    <!--        <input type="text" name="id" placeholder="请输入" autocomplete="off" class="layui-input">-->
                    <!--    </div>-->
                    <!--</div>-->
                    <div class="layui-inline">
                        <label class="layui-form-label">用户</label>
                        <div class="layui-input-inline">
                            <input type="text" name="mobile" placeholder="请输入" autocomplete="off" class="layui-input">
                        </div>
                    </div>
                    <div class="layui-inline">
                        <label class="layui-form-label">交易类型</label>
                        <div class="layui-input-inline">
                           <select name="type"  class="layui-input">
                               <option value="">选择交易类型</option>
                               <option value="1">买</option>
                               <option value="2">卖</option>
                           </select>
                        </div>
                    </div>
                    <div class="layui-inline">
                        <label class="layui-form-label">状态</label>
                        <div class="layui-input-inline">
                           <select name="status"  class="layui-input">
                               <option value="">选择状态</option>
                               <option value="1">委托中</option>
                               <option value="2">已完成</option>
                               <option value="3">已取消</option>
                           </select>
                        </div>
                    </div>
                    
                    <div class="layui-inline">
                        <button class="layui-btn layuiadmin-btn-useradmin" lay-submit lay-filter="searchtj">
                            <i class="layui-icon layui-icon-search layuiadmin-button-btn"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="layui-card-body">

                <table id="coin_trade" lay-filter="coin_trade"></table>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script type="text/html" id="barDemo">
       <!--<a class="layui-btn layui-btn-normal layui-btn-xs" lay-event="getsons"><i class="layui-icon layui-icon-friends"></i>下级</a>-->
     <a  class="layui-btn layui-btn-xs" style="@{{ d.status == 1 ? '' : 'display:none' }}" lay-event="qiangzhi">@{{ d.status == 1 ? '强制交易' : '' }}</a>  
    </script>

    <script>
        layui.use(['table','form'], function(){
            // console.log(layui.setter.base)
            var table = layui.table;
            var $ = layui.jquery;
            var form = layui.form;

             //第一个实例
            table.render({
                elem: '#coin_trade'
                ,url: '{{url('admin/coin_trade/list')}}' //数据接口
                ,page: true //开启分页
                ,id:'mobileSearch'
                ,cols: [[ //表头
                    {field: 'id', title: 'ID', width:80, sort: true}
                    
                    ,{field: 'account_number', title: '用户', width:200}
                    ,{ title: '交易类型', width:200,templet:function(d){
                        if(d.type==1){
                             return "<span style='color:#00c087'>买</span>";
                        }else if(d.type==2){
                            return "<span style='color:#f00'>卖</span>";
                        }
                    }}
                    ,{ title: '交易对', width:200,templet:function(d){
                        return d.currency_name+"/"+d.legal_name;
                    }}
                    ,{ title: '委托价', width:100,templet:function(d){
                        return (parseFloat(d.target_price).toFixed(4));
                    }}
                    ,{ title: '委托量', width:120,templet:function(d){
                        return (parseFloat(d.trade_amount).toFixed(4));
                    }}
                    // ,{field: 'charge_fee', title: '手续费'}
                    ,{ title: '手续费',templet:function(d){
                        return (d.target_price*d.trade_amount*d.charge_fee).toFixed(4);
                    }}
                    ,{field: 'created_at', title: '委托时间'}
                    ,{title: '状态' ,templet:function(d){
                        if(d.status==1){
                            return "<span style='color:#00c087'>委托中</span>";
                        }else if(d.status==2){
                            return "<span style='color:#f00'>已完成</span>";
                        }else  if(d.status==3){
                            return "<span style='color:#999'>已取消</span>";
                        }
                    }}
                     ,{title:'操作',minWidth:100,toolbar: '#barDemo'}
                ]]
            });
            //监听提交
            form.on('submit(searchtj)', function(data){
                // var seller_name = data.field.seller_name
                var account_number = data.field.mobile,
                    type = data.field.type,
                    status = data.field.status;
                    // ,currency_id = $('#currency_id').val()

                table.reload('mobileSearch',{
                    where:{
                        type:type,
                        status:status,
                        search:account_number,
                        // currency_id:currency_id,
                    },
                    page: {curr: 1}         //重新从第一页开始
                });
                return false;
            });
            table.on('tool(coin_trade)', function(obj){
                var data = obj.data;
                if(obj.event === 'getsons'){
                	table.reload('mobileSearch',{
                    where:{
	                        p_id:data.id,
	                        // currency_id:currency_id,
	                    },
	                    page: {curr: 1}         //重新从第一页开始
	                });
                }
                if(obj.event === 'qiangzhi'){
                    $.post('close',{
                        id:data.id
                    },function(res){
                        if(res.type=="ok"){
                            layer.msg('操作成功');
                            setTimeout(function (){
                                table.reload('mobileSearch',{
            	                    page: {curr: 1}         //重新从第一页开始
            	                });
                            },1500)
                        }else{
                            layer.msg('操作失败');
                        }
                    })
                }
            });
        });
    </script>
    
@endsection
