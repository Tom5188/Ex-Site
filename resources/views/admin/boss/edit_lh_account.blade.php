@extends('admin._layoutNew')
@section('page-head')

@stop
@section('page-content')
    <div class="larry-personal-body clearfix">
        <form class="layui-form col-lg-5">
            <div class="layui-form-item">
                <label class="layui-form-label">用户账号</label>
                <div class="layui-input-block">
                    <input type="text" name="account" id="account" autocomplete="off" class="layui-input layui-disabled" value="" placeholder="" disabled>
                </div>
            </div>
            <!--<div class="layui-form-item">-->
            <!--    <label class="layui-form-label">理财账户币种</label>-->
            <!--    <div class="layui-input-block">-->
            <!--        <select name="type" lay-verify="required" lay-filter="type"  id="type">-->
            <!--            <option value="1">AUTH</option>-->
            <!--            <option value="2">USDT</option>-->
            <!--        </select>-->
            <!--    </div>-->
            <!--</div>-->
            <div class="layui-form-item">
                <label class="layui-form-label">调节DF币种余额</label>
                <div class="layui-input-block">
                    <input type="text" name="df_balance"  id="df_balance" required  lay-verify="required" placeholder="请输入要改成多少余额" autocomplete="off" class="layui-input">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">调节USDT币种余额</label>
                <div class="layui-input-block">
                    <input type="text" name="usdt_balance"  id="usdt_balance" required  lay-verify="required" placeholder="请输入要改成多少余额" autocomplete="off" class="layui-input">
                </div>
            </div>
            <div class="layui-form-item">
                <div class="layui-input-block">
                    <button class="layui-btn" lay-submit lay-filter="user_submit">立即提交</button>
                    <button type="reset" class="layui-btn layui-btn-primary">重置</button>
                </div>
            </div>
        </form>
    </div>
@stop
@section('scripts')
    <script type="text/javascript">
        let url=window.location.href;
        var account_id='';
        var account='';
        var df_balance='';
        var usdt_balance='';
        if(url.indexOf("?")!=-1){
            url=url.substr(url.indexOf('?')+1);
            url=url.split('&');
            for(let i in url){
                if(i==0){
                    user_id=url[i].split("=")[1]
                }else{
                    account=url[i].split("=")[1]
                }
            }
            $("#account").val(account);
            $.ajax({
                url:'{{url('admin/ybb/account')}}',
                type: 'get',
                dataType: 'json',
                data: {
                    user_id:user_id
                },
                success: function (res) {
                    account_id=res.message.id;
                    df_balance = res.message.df_balance;
                     usdt_balance=res.message.usdt_balance
                    $("#df_balance").val(df_balance);
                    $("#usdt_balance").val(usdt_balance);
                }
            });
        }
        layui.use(['form','upload','layer'], function () {
            var layer = layui.layer;
            var form = layui.form;
            var $ = layui.$;
            form.on('select(type)', function (data) {
                if(data.elem.value==1){
                    $("#balance").val(df_balance?df_balance:0)
                }else if(data.elem.value==2){
                    $("#balance").val(usdt_balance?usdt_balance:0);
                }
            	   console.log(data);
        	});
            form.on('submit(user_submit)', function (data) {
                // var balance=$("#balance").val();
                // var type=$("#type option:selected").val();
                var data = data.field;
                data.id=account_id;
                // if(type==1){
                //     data.df_balance=balance;
                // }else if(type==2){
                //     data.usdt_balance=balance;
                // }
                
                $.ajax({
                    url:'{{url('admin/ybb/account/edit')}}',
                    type: 'post',
                    dataType: 'json',
                    data: data,
                    success: function (res) {
                        layer.msg(res.message);
                        if(res.type == 'ok') {
                            var index = parent.layer.getFrameIndex(window.name);
                            parent.layer.close(index);
                            parent.window.location.reload();
                        }else{
                            return false;
                        }
                    }
                });
                return false;
            });
        });


    </script>
@stop