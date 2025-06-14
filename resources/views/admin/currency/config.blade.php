@extends('admin._layoutNew')

@section('page-head')

@endsection

@section('page-content')
<div style="display:none">
     <div class="layui-form-item">
            <label class="layui-form-label">期限</label>
            <div class="layui-input-block">
                <input type="text" id="day" name="day" autocomplete="off" placeholder="" class="layui-input" >
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">利息</label>
            <div class="layui-input-block">
                <input type="text" name="total_interest_rate" autocomplete="off" placeholder="" class="layui-input" id="total_interest_rate">
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">最小值</label>
            <div class="layui-input-block">
                <input type="text" name="save_min" id="save_min" autocomplete="off" placeholder="" class="layui-input">
            </div>
        </div>
</div>
<table id="data_table" lay-filter="data_table"></table>
@endsection
@section('scripts')
<script type="text/html" id="barDemo">
    <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="delete">删除</a>
</script>
<script>
let url=window.location.href;
let configId="";
if(url.indexOf("id")!=-1){
    configId=url.substr(url.lastIndexOf("/")+1);
}
    var currency_id = {{ Request::route('currency_id') }}
    layui.use(['table', 'layer', 'form'], function() {
        var table = layui.table
            ,layer = layui.layer
            ,form = layui.form
            ,$ = layui.$
        var data_table = table.render({
            elem: '#data_table'
            ,url: '/admin/currency/deposit/'+ currency_id +'/list'
            ,height: 'full'
            ,toolbar: 'default'
            ,page: true
            ,cols: [[
                {type: 'radio'}
                ,{field: 'id', title: 'id', width: 70}
                ,{field: 'name', title: '交易币', width: 80}
                ,{field: 'day', title: '期限', width: 80}
                ,{field: 'total_interest_rate', title: '利率(%)', width: 80}
                ,{field: 'save_min', title: '最少存币数', width: 80}
                ,{field: 'created_at', title: '创建时间', width: 180}
                // ,{fixed: 'right', title: '操作', width: 190, align: 'center', toolbar: '#barDemo'}
            ]]
        });
         table.on('tool(data_table)', function (obj) {
             var data = obj.data;
            var layEvent = obj.event;
            var tr = obj.tr;
            if (layEvent === 'delete') { //删除
                layer.confirm('真的要删除吗？', function (index) {
                    //向服务端发送删除指令
                    $.ajax({
                        url: "admin//currency/delete_deposit_config",
                        type: 'post',
                        dataType: 'json',
                        data: {id: data.id},
                        success: function (res) {
                            if (res.type == 'ok') {
                                obj.del(); //删除对应行（tr）的DOM结构，并更新缓存
                                layer.close(index);
                            } else {
                                layer.close(index);
                                layer.alert(res.message);
                            }
                        }
                    });
                });
            }
            // console.log(obj)
            // var id = 0
            //     ,selected = table.checkStatus('data_table')
            // switch (obj.event) {
            //     case 'add':
            //         layer.open({
            //             title: '添加交易对'
            //             ,type: 2
            //             ,content: '/admin/currency/add_config?id=' + configId
            //             ,area: ['600px', '380px']
            //         });
            //         break;
            //     case 'update':
            //         if (selected.data.length != 1) {
            //             layer.msg('只能编辑一个交易对');
            //             return false;
            //         }
            //         id = selected.data[0].id
            //         layer.open({
            //             title: '编辑交易对'
            //             ,type: 2
            //             ,content: '/admin/currency/edit_config/' + id
            //             ,area: ['600px', '380px']
            //         });
            //         break;
            //     case 'delete':
            //         if (selected.data.length != 1) {
            //             layer.msg('选择一个交易对才能删除');
            //             return false;
            //         }
            //         id = selected.data[0].id
            //         layer.confirm('真的确定要删除吗?', function (index) {
            //             $.ajax({
            //                 url: '/admin/currency/delete_config/' + id
            //                 ,type: 'GET'
            //                 ,success: function (res) {
            //                     layer.msg(res.message, {
            //                         time: 2000
            //                         ,end: function () {
            //                             if (res.type == 'ok') {
            //                                 data_table.reload();
            //                             }
            //                         }
            //                     });
            //                 }
            //                 ,error: function (res) {

            //                 }
            //             });
            //         });
            //         break;
            //     default:
            //         break;
            // }
        });
        table.on('toolbar(data_table)', function (obj) {
            console.log(obj)
            var id = 0
                ,selected = table.checkStatus('data_table')
            switch (obj.event) {
                case 'add':
                    layer.open({
                        title: '添加质押挖矿'
                        ,type: 2
                        ,content: '/admin/currency/deposit_config?c_id='+currency_id
                        ,area: ['600px', '380px']
                    });
                    break;
                case 'update':
                    if (selected.data.length != 1) {
                        layer.msg('只能编辑一个质押挖矿');
                        return false;
                    }
                    id = selected.data[0].id    
                    var data=selected.data[0];
                    layer.open({
                        title: '编辑质押挖矿'
                        ,type: 2
                        ,content: '/admin/currency/deposit_config?id=' + id+"&day="+data.day+"&rate="+data.total_interest_rate+"&save_min="+data.save_min
                        ,area: ['600px', '380px']
                    });
                    break;
                case 'delete':
                    if (selected.data.length != 1) {
                        layer.msg('选择一个质押挖矿才能删除');
                        return false;
                    }
                    id = selected.data[0].id
                    layer.confirm('真的确定要删除吗?', function (index) {
                         $.ajax({
                        url: "/admin/currency/delete_deposit_config",
                        type: 'post',
                        dataType: 'json',
                        data: {id: id},
                        success: function (res) {
                            layer.msg(res.message, {
                                    time: 2000
                                    ,end: function () {
                                        if (res.type == 'ok') {
                                            data_table.reload();
                                        }
                                    }
                                });
                        }
                    });
                });
                        // $.ajax({
                        //     url: 'admin/currency/delete_deposit_config'
                        //     ,type: 'post'，
                        //      data: {id: data.id},
                        //     ,success: function (res) {
                        //         layer.msg(res.message, {
                        //             time: 2000
                        //             ,end: function () {
                        //                 if (res.type == 'ok') {
                        //                     data_table.reload();
                        //                 }
                        //             }
                        //         });
                        //     }
                        //     ,error: function (res) {

                        //     }
                        // });
                    
                    break;
                default:
                    break;
            }
        });
    });
</script>
@endsection
