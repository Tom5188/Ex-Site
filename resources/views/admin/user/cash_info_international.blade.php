@extends('admin._layoutNew')

@section('page-head')

@endsection

@section('page-content')
    <form class="layui-form" action="">
        <div class="layui-form-item">
            <label class="layui-form-label">真实姓名</label>
            <div class="layui-input-block">
                <input type="text" name="real_name" autocomplete="off" class="layui-input" value="{{$cashInfo->real_name ?? ''}}" >
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">开户行</label>
            <div class="layui-input-block">
                <input type="text" name="bank_name" autocomplete="off" class="layui-input" value="{{$cashInfo->bank_name ?? ''}}" >
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">银行卡号</label>
            <div class="layui-input-block">
                <input type="text" name="bank_account" autocomplete="off" class="layui-input" value="{{$cashInfo->bank_account ?? ''}}">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">开户省市</label>
            <div class="layui-input-block">
                <input type="text" name="bank_dizhi" autocomplete="off" class="layui-input" value="{{$cashInfo->bank_dizhi ?? ''}}">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">开户网点</label>
            <div class="layui-input-block">
                <input type="text" name="bank_network" autocomplete="off" class="layui-input" value="{{$cashInfo->bank_network ?? ''}}">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">证件号码</label>
            <div class="layui-input-block">
                <input type="text" name="idcard" autocomplete="off" class="layui-input" value="{{$cashInfo->idcard ?? ''}}">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">国际支付代码</label>
            <div class="layui-input-block">
                <input type="text" name="swift_code" autocomplete="off" class="layui-input" value="{{$cashInfo->swift_code ?? ''}}">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">联系电话</label>
            <div class="layui-input-block">
                <input type="text" name="phone" autocomplete="off" class="layui-input" value="{{$cashInfo->phone ?? ''}}">
            </div>
        </div>
        <div class="layui-form-item">
            <div class="layui-input-block">
                <button class="layui-btn" lay-submit lay-filter="form">立即提交</button>
                <button type="reset" class="layui-btn layui-btn-primary">重置</button>
                <button class="layui-btn layui-btn-danger" id="btn-delete">删除</button>
            </div>
        </div>
    </form>
@endsection

@section('scripts')
<script>
    layui.use(['element', 'form', 'layer'], function () {
        var element = layui.element
            ,form = layui.form
            ,layer = layui.layer
            ,$ = layui.$
        form.on('submit(form)', function (data) {
            $.ajax({
                url: ''
                ,type: 'POST'
                ,data: data.field
                ,success: function (res) {
                    layer.msg(res.message, {
                        time: 2000
                        ,end: function () {
                            if (res.type == 'ok') {
                                var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
                                parent.layer.close(index); //再执行关闭 
                                parent.layui.table.reload('userlist');       
                            }
                        }
                    });
                }
                ,error: function (res) {
                    layer.msg('网络错误');
                }
            });
            return false;
        });

        $('#btn-delete').click(function () {
            if (window.confirm('确认要删除吗？此操作不可恢复')) {
                try {
                    $.ajax({
                        url: '/admin/user/cash_info_international?id={{$user->id}}'
                        ,type: 'DELETE'
                        ,success: function (res) {
                            layer.msg(res.message, {
                                time: 2000,
                                end: function () {
                                    if (res.type == 'ok') {
                                        var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
                                        parent.layer.close(index); //再执行关闭
                                        parent.layui.table.reload('userlist');
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
            }
            return false;
        });
    });
</script>
@endsection