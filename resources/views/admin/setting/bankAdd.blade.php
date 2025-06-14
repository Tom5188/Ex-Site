@extends('admin._layoutNew')
@section('page-head')

@stop
@section('page-content')
    <div class="larry-personal-body clearfix">
        <form class="layui-form col-lg-5">
            <div class="layui-form-item layui-form-text">
                <label class="layui-form-label">币种 logo</label>
                <div class="layui-input-block">
                    <button class="layui-btn" type="button" id="upload_test">选择图片</button>
                    <br>
                    <img src="@if(!empty($result->logo)){{$result->logo}}@endif" id="img_thumbnail" class="thumbnail" style="display: @if(!empty($result->logo)){{"block"}}@else{{"none"}}@endif;max-width: 200px;height: auto;margin-top: 5px;">
                    <input type="hidden" name="currency_logo" id="thumbnail" value="@if(!empty($result->logo)){{$result->logo}}@endif">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">币种</label>
                <div class="layui-input-block">
                    <input type="text" lay-verify="required" name="currency_name" autocomplete="off" class="layui-input" value="" placeholder="">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">支付方式</label>
                <div class="layui-input-block">
                    <input type="text" lay-verify="required" name="pay_way_name" autocomplete="off" class="layui-input" value="" placeholder="">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">手续费（%）</label>
                <div class="layui-input-block">
                    <input type="text" lay-verify="required" name="commissions" autocomplete="off" class="layui-input" value="" placeholder="">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">账户名称</label>
                <div class="layui-input-block">
                    <input type="text" lay-verify="required" name="account_name" autocomplete="off" class="layui-input" value="" placeholder="">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">IBAN</label>
                <div class="layui-input-block">
                    <input type="text" name="iban" lay-verify="required" autocomplete="off" class="layui-input" value="" placeholder="">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">收款人国家/地区</label>
                <div class="layui-input-block">
                    <input type="text" lay-verify="required" name="beneficiary_country" autocomplete="off" class="layui-input" value="" placeholder="">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">银行编码</label>
                <div class="layui-input-block">
                    <input type="text" lay-verify="required" name="bank_code" autocomplete="off" class="layui-input" value="" placeholder="">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">银行名称</label>
                <div class="layui-input-block">
                    <input type="text" lay-verify="required" name="bank_name" autocomplete="off" class="layui-input" value="" placeholder="">
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label">银行地址</label>
                <div class="layui-input-block">
                    <input type="text" lay-verify="required" name="bank_address" autocomplete="off" class="layui-input" value="" placeholder="">
                </div>
            </div>
            <div class="layui-form-item">
                <div class="layui-input-block">
                    <a class="layui-btn layui-btn-primary" id="qx">取消</a>
                    <button class="layui-btn" lay-submit lay-filter="adminuser_submit">立即提交</button>
                </div>
            </div>
        </form>
    </div>
@stop
@section('scripts')
    <script type="text/javascript">

        layui.use(['form','upload','layer'], function () {
            var layer = layui.layer;
            var form = layui.form;
            var $ = layui.$;
            var upload = layui.upload;
            var uploadInst = upload.render({
                elem: '#upload_test' //绑定元素
                ,url: '{{URL("api/upload")}}?scene=admin' //上传接口
                ,done: function(res){
                    //上传完毕回调
                    if (res.type == "ok"){
                        $("#thumbnail").val(res.message)
                        $("#img_thumbnail").show()
                        $("#img_thumbnail").attr("src",res.message)
                    } else{
                        alert(res.message)
                    }
                }
                ,error: function(){
                    //请求异常回调
                }
            }); 
            form.on('submit(adminuser_submit)', function (data) {
                var data = data.field;
                $.ajax({
                    url: '/admin/setting/bank_add',
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
            $("#qx").click(function(){
                var index=parent.layer.getFrameIndex(window.name);
                parent.layer.close(index);
            });
        });
    
        

    </script>
@stop