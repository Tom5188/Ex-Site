@extends('admin._layoutNew')

@section('page-content')
    <form class="layui-form" action="">
        <fieldset class="layui-elem-field">
            <legend>普通用户配置</legend>
            <div class="layui-form-item">
                <label class="layui-form-label" style="width: 270px;">矿池收益提高</label>
                <div class="layui-input-inline">
                    <input type="text" name="ordinary_ore_pool_income" required lay-verify="required" placeholder="请输入"
                           autocomplete="off" class="layui-input"
                           value="{{$setting['ordinary']['ore_pool_income'] ?? ''}}">
                </div>
                <div class="layui-form-mid layui-word-aux">%</div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label" style="width: 270px;">是否可以对交割合约30s的产品下单</label>
                <div class="layui-input-block">
                    <input type="radio" name="ordinary_delivery_contract_30s_switch" value="1" title="是"
                           @if (isset($setting['ordinary']['delivery_contract_30s_switch'])) {{$setting['ordinary']['delivery_contract_30s_switch'] == 1 ? 'checked' : ''}} @endif>
                    <input type="radio" name="ordinary_delivery_contract_30s_switch" value="0"
                           title="否" @if (isset($setting['ordinary']['delivery_contract_30s_switch'])) {{$setting['ordinary']['delivery_contract_30s_switch'] == 0 ? 'checked' : ''}} @else checked @endif>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label" style="width: 270px;">所有交易手续费</label>
                <div class="layui-input-inline">
                    <input type="text" name="ordinary_transaction_service_change" required lay-verify="required"
                           placeholder="请输入" autocomplete="off" class="layui-input"
                           value="{{$setting['ordinary']['transaction_service_change'] ?? ''}}">
                </div>
                <div class="layui-form-mid layui-word-aux">%</div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label" style="width: 270px;">提币手续费</label>
                <div class="layui-input-inline">

                    <input type="text" name="ordinary_withdraw_coin_service_change" required lay-verify="required"
                           placeholder="请输入" autocomplete="off" class="layui-input"
                           value="{{$setting['ordinary']['withdraw_coin_service_change'] ?? ''}}">
                </div>
                <div class="layui-form-mid layui-word-aux">%（0 代表不需要手续费）</div>
            </div>
        </fieldset>

        <fieldset class="layui-elem-field">
            <legend>VIP用户配置</legend>
            <div class="layui-form-item">
                <label class="layui-form-label" style="width: 270px;">矿池收益提高</label>
                <div class="layui-input-inline">
                    <input type="text" name="vip_ore_pool_income" required lay-verify="required" placeholder="请输入"
                           autocomplete="off" class="layui-input" value="{{$setting['vip']['ore_pool_income'] ?? ''}}">
                </div>
                <div class="layui-form-mid layui-word-aux">%</div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label" style="width: 270px;">是否可以对交割合约30s的产品下单</label>
                <div class="layui-input-block">
                    <input type="radio" name="vip_delivery_contract_30s_switch" value="1" title="是"
                           @if (isset($setting['vip']['delivery_contract_30s_switch'])) {{$setting['vip']['delivery_contract_30s_switch'] == 1 ? 'checked' : ''}} @else checked @endif>
                    <input type="radio" name="vip_delivery_contract_30s_switch" value="0"
                           title="否" @if (isset($setting['vip']['delivery_contract_30s_switch'])) {{$setting['vip']['delivery_contract_30s_switch'] == 0 ? 'checked' : ''}} @endif>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label" style="width: 270px;">所有交易手续费</label>
                <div class="layui-input-inline">
                    <input type="text" name="vip_transaction_service_change" required lay-verify="required"
                           placeholder="请输入" autocomplete="off" class="layui-input"
                           value="{{$setting['vip']['transaction_service_change'] ?? ''}}">
                </div>
                <div class="layui-form-mid layui-word-aux">%</div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label" style="width: 270px;">提币手续费</label>
                <div class="layui-input-inline">

                    <input type="text" name="vip_withdraw_coin_service_change" required lay-verify="required"
                           placeholder="请输入" autocomplete="off" class="layui-input"
                           value="{{$setting['vip']['withdraw_coin_service_change'] ?? ''}}">
                </div>
                <div class="layui-form-mid layui-word-aux">%（0 代表不需要手续费）</div>
            </div>
        </fieldset>

        <fieldset class="layui-elem-field">
            <legend>SVIP用户配置</legend>
            <div class="layui-form-item">
                <label class="layui-form-label" style="width: 270px;">矿池收益提高</label>
                <div class="layui-input-inline">
                    <input type="text" name="svip_ore_pool_income" required lay-verify="required" placeholder="请输入"
                           autocomplete="off" class="layui-input" value="{{$setting['svip']['ore_pool_income'] ?? ''}}">
                </div>
                <div class="layui-form-mid layui-word-aux">%</div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label" style="width: 270px;">是否可以对交割合约30s的产品下单</label>
                <div class="layui-input-block">
                    <input type="radio" name="svip_delivery_contract_30s_switch" value="1" title="是"
                           @if (isset($setting['svip']['delivery_contract_30s_switch'])) {{$setting['svip']['delivery_contract_30s_switch'] == 1 ? 'checked' : ''}} @else checked @endif>
                    <input type="radio" name="svip_delivery_contract_30s_switch" value="0"
                           title="否" @if (isset($setting['svip']['delivery_contract_30s_switch'])) {{$setting['svip']['delivery_contract_30s_switch'] == 0 ? 'checked' : ''}} @endif>
                </div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label" style="width: 270px;">所有交易手续费</label>
                <div class="layui-input-inline">
                    <input type="text" name="svip_transaction_service_change" required lay-verify="required"
                           placeholder="请输入" autocomplete="off" class="layui-input"
                           value="{{$setting['svip']['transaction_service_change'] ?? ''}}">
                </div>
                <div class="layui-form-mid layui-word-aux">%</div>
            </div>
            <div class="layui-form-item">
                <label class="layui-form-label" style="width: 270px;">提币手续费</label>
                <div class="layui-input-inline">

                    <input type="text" name="svip_withdraw_coin_service_change" required lay-verify="required"
                           placeholder="请输入" autocomplete="off" class="layui-input"
                           value="{{$setting['svip']['withdraw_coin_service_change'] ?? ''}}">
                </div>
                <div class="layui-form-mid layui-word-aux">%（0 代表不需要手续费）</div>
            </div>
        </fieldset>
        <div class="layui-form-item">
            <div class="layui-input-block">
                <button class="layui-btn" lay-submit lay-filter="form">立即提交</button>
            </div>
        </div>
    </form>

@stop
@section('scripts')
    <script>
        //Demo
        layui.use('form', function () {
            var form = layui.form;

            //监听提交
            form.on('submit(form)', function (data) {
                var data = data.field;
                $.ajax({
                    url: '/admin/userGradeSetting/store',
                    type: 'post',
                    dataType: 'json',
                    data: data,
                    success: function (res) {
                        layer.msg(res.message);
                    }
                });
                return false;
            });
        });
    </script>
@stop