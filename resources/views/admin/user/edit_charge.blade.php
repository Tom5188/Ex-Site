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
    <form class="layui-form" action="">
        <div class="layui-form-item">
               <table class="layui-table">
                <tbody>
                    <tr>
                        <td>
                            账户名：{{$charge_info->account_name}}
                        </td>
                        <td>
                            币种：{{$charge_info->currency_name}}
                        </td>
                    </tr>
                     <tr>
                        <td>
                            充值数量：{{$charge_info->amount}}
                        </td>
                        <td>
                            @if($charge_info->type == 1 )
                                充值方式：银行卡
                            @endif
                            @if($charge_info->type == 0 )
                                充值方式：区块链
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td>
                            费率：{{$charge_info->give}}
                        </td>
                         <td>
                            申请时间：{{$charge_info -> created_at}}
                        </td>
                    </tr>
                    @if($charge_info->type == 0 )
                        <tr>
                            <td>
                                当前状态：@if($charge_info->status==1) 
                                    <span class="layui-badge status_bg_1">申请充值</span>
                                @elseif($charge_info->status==2)
                                    <span class="layui-badge status_bg_2">充值完成</span>
                                @elseif($charge_info->status==3) 
                                    <span class="layui-badge status_bg_3">申请拒绝</span>
                                @endif
                            </td>
                             <td>
                                地址：{{$charge_info -> address}}
                            </td>
                        </tr>
                    @endif
                     @if($charge_info->type == 1 )
                         <tr>
                            <td>
                                账户名称：{{$charge_info->bank_user_name}}
                            </td>
                             <td>
                                IBAN：{{$charge_info -> iban}}
                            </td>
                        </tr>
                         <tr>
                            <td>
                                收款人国家/地区：{{$charge_info->beneficiary_country}}
                            </td>
                             <td>
                                银行编码（BIC/SWIFT)：{{$charge_info -> bank_code}}
                            </td>
                        </tr>
                         <tr>
                            <td>
                                银行名称：{{$charge_info->bank_name}}
                            </td>
                             <td>
                                银行地址：{{$charge_info -> bank_address}}
                            </td>
                        </tr>
                     @endif
                    <tr>
                        <td  colspan="2">
                            <textarea  class="layui-textarea" name="desc" placeholder="请输入拒绝理由">{{$charge_info->desc}}</textarea>
                        </td>
                    </tr>
                    
                    <tr>
                        <td  colspan="2">
                            <input type="hidden" name='id' value='{{$charge_info->id}}'>
                            @if($charge_info->status==1)
                                <button class="layui-btn" lay-submit lay-filter="demo1">通过</button>
                                <button class="layui-btn layui-btn-danger" lay-submit lay-filter="demo2">拒绝</button>
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </form>

@endsection

@section('scripts')
    <script>
        layui.use(['form','laydate'],function () {
            var form = layui.form
                ,$ = layui.jquery
                ,laydate = layui.laydate
                ,index = parent.layer.getFrameIndex(window.name);
            $('#get_code').click(function () {
                var that_btn = $(this);
                $.ajax({
                    url: '/admin/safe/verificationcode'
                    ,type: 'GET'
                    ,success: function (res) {
                        if (res.type == 'ok') {
                            that_btn.attr('disabled', true);
                            that_btn.toggleClass('layui-btn-disabled');
                        }
                        layer.msg(res.message, {
                            time: 3000
                        });
                    }
                    ,error: function () {
                        layer.msg('网络错误');
                    }
                });
            });
            //监听提交
            form.on('submit(demo1)', function(data) {
                var data = data.field;
                // console.log(data);
                // if (data.verificationcode == '') {
                //     layer.msg('请填写安全验证码');
                //     return false;
                // }
                layer.confirm('确定允许充币?', function (index) {
                    var loading = layer.load(1, {time: 30 * 1000});
                    layer.close(index);
                    $.ajax({
                        url: '{{url('admin/user/pass_req')}}'
                        ,type: 'post'
                        ,dataType: 'json'
                        ,data : data
                        ,success: function(res) {
                            if (res.type=='error') {
                                layer.msg(res.message);
                            } else {
                                layer.msg(res.message);
                                parent.layer.close(index);
                                parent.window.location.reload();
                            }
                        }
                        ,complete: function () {
                            layer.close(loading);
                        }
                    });
                });
                return false;
            });
            form.on('submit(demo2)', function(data){
                var data = data.field;
                if (data.desc == '') {
                    layer.msg('请填写拒绝理由');
                    return false;
                }
                $.ajax({
                    url:'{{url('admin/user/refuse_req')}}'
                    ,type:'post'
                    ,dataType:'json'
                    ,data : data
                    ,success:function(res){
                        if(res.type=='error'){
                            layer.msg(res.message);
                        }else{
                            parent.layer.close(index);
                            parent.window.location.reload();
                        }
                    }
                });
                return false;
            });
        });
    </script>

@endsection