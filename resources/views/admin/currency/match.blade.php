@extends('admin._layoutNew')

@section('page-head')

@endsection

@section('page-content')
<table id="data_table" lay-filter="data_table"></table>
@endsection
@section('scripts')
<script type="text/html" id="is_display">
    <input type="checkbox" name="is_display" value="@{{d.id}}" lay-skin="switch" lay-text="是|否" lay-filter="is_display" disabled @{{ d.is_display == 1 ? 'checked' : '' }}>
</script>

<script type="text/html" id="open_transaction">
    <input type="checkbox" name="open_transaction" value="@{{d.id}}" lay-skin="switch" lay-text="开启|关闭" lay-filter="open_transaction" disabled @{{ d.open_transaction == 1 ? 'checked' : '' }}>
</script>
<script type="text/html" id="open_lever">
    <input type="checkbox" name="open_lever" value="@{{d.id}}" lay-skin="switch" lay-text="开启|关闭" lay-filter="open_lever" disabled @{{ d.open_lever == 1 ? 'checked' : '' }}>
</script>
<script type="text/html" id="open_microtrade">
    <input type="checkbox" name="open_microtrade" value="@{{d.id}}" lay-skin="switch" lay-text="开启|关闭" lay-filter="open_microtrade" disabled @{{ d.open_microtrade == 1 ? 'checked' : '' }}>
</script>

<script type="text/html" id="open_coin_trade">
    <input type="checkbox" name="open_coin_trade" value="@{{d.id}}" lay-skin="switch" lay-text="开启|关闭" lay-filter="open_coin_trade" disabled @{{ d.open_coin_trade == 1 ? 'checked' : '' }}>
</script>
<script type="text/html" id="coin_trade_success">
    <input type="checkbox" name="coin_trade_success" value="@{{d.id}}" lay-skin="switch" lay-text="开启|关闭" lay-filter="coin_trade_success" disabled @{{ d.coin_trade_success == 1 ? 'checked' : '' }}>
</script>
<script>
    var legal_id = {{ Request::route('legal_id') }}
    layui.use(['table', 'layer', 'form'], function() {
        var table = layui.table
            ,layer = layui.layer
            ,form = layui.form
            ,$ = layui.$
        var data_table = table.render({
            elem: '#data_table'
            ,url: '/admin/currency/match_list/' + legal_id
            ,height: 'full'
            ,toolbar: 'default'
            ,page: true
            ,cols: [[
                {type: 'radio'}
                ,{field: 'id', title: 'id', minWidth: 70}
                ,{field: 'legal_name', title: '法币', minWidth: 80}
                ,{field: 'currency_name', title: '交易币', minWidth: 80}
                ,{field: 'is_display', title: '显示', minWidth: 90, templet: '#is_display'}
                ,{field: 'category_text', title: '分类', minWidth: 90}
                ,{field: 'open_transaction', title: '撮合交易', minWidth: 100, templet: '#open_transaction'}
                ,{field: 'open_lever', title: '杠杆交易', minWidth: 100, templet: '#open_lever'}
                ,{field: 'open_microtrade', title: '期权', minWidth: 100, templet: '#open_microtrade'}
                ,{field: 'open_coin_trade', title: '币币交易', minWidth: 100, templet: '#open_coin_trade'}
                ,{field: 'coin_trade_success', title: '币币交易撮合', minWidth: 100, templet: '#coin_trade_success'}
                ,{field: 'market_from_name', title: '行情来自', minWidth: 110}
                ,{field: 'create_time', title: '创建时间', minWidth: 180}
            ]]
        });
        table.on('toolbar(data_table)', function (obj) {
            console.log(obj)
            var id = 0
                ,selected = table.checkStatus('data_table')
            switch (obj.event) {
                case 'add':
                    layer.open({
                        title: '添加交易对'
                        ,type: 2
                        ,content: '/admin/currency/match_add/' + legal_id
                        ,area: ['600px', '380px']
                    });
                    break;
                case 'update':
                    if (selected.data.length != 1) {
                        layer.msg('只能编辑一个交易对');
                        return false;
                    }
                    id = selected.data[0].id
                    layer.open({
                        title: '编辑交易对'
                        ,type: 2
                        ,content: '/admin/currency/match_edit/' + id
                        ,area: ['600px', '380px']
                    });
                    break;
                case 'delete':
                    if (selected.data.length != 1) {
                        layer.msg('选择一个交易对才能删除');
                        return false;
                    }
                    id = selected.data[0].id
                    layer.confirm('真的确定要删除吗?', function (index) {
                        $.ajax({
                            url: '/admin/currency/match_del/' + id
                            ,type: 'GET'
                            ,success: function (res) {
                                layer.msg(res.message, {
                                    time: 2000
                                    ,end: function () {
                                        if (res.type == 'ok') {
                                            data_table.reload();
                                        }
                                    }
                                });
                            }
                            ,error: function (res) {

                            }
                        });
                    });
                    break;
                default:
                    break;
            }
        });
    });
</script>
@endsection