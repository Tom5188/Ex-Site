@extends('agent.layadmin')

@section('page-head')

@endsection

@section('page-content')
    <!-- <div class="layui-form"> -->
        <table id="userlist" lay-filter="userlist">
            <input type="hidden" name="user_id" value="{{$user_id}}">
        </table>


@endsection

@section('scripts')
    <script>
        window.onload = function() {
            
            layui.use(['element', 'form', 'layer', 'table'], function () {
                var element = layui.element;
                var layer = layui.layer;
                var table = layui.table;
                var $ = layui.$;
                var form = layui.form;

                function tbRend(url) {
                    table.render({
                        elem: '#userlist'
                        ,url: url
                        ,page: true
                        ,limit: 20
                        ,toolbar: true
                        ,totalRow: true
                        ,height: 'full-100'
                        ,cols: [[
                                {field: 'id', title: '币种id', minWidth: 70}
                                ,{field: 'name', title: '币种', minWidth: 100, totalRowText: '小计'}
                                
                                ,{field: '_ru', title: '入金', minWidth: 150, totalRow: true}
                                ,{field: '_chu', title: '出金', minWidth: 150, totalRow: true}
                                ,{field: '_caution_money', title: '杠杆可用保证金', minWidth: 150, totalRow: true}
                                ,{field: '_zongyue', title: '总余额', minWidth: 150, totalRow: true}
                                // ,{field: '_zongyue', title: '总余额', minWidth: 150,templet:function(d){
                                //     return d._zongyue.change_balance*1+d._zongyue.lock_change_balance*1+d._zongyue.legal_balance*1+d._zongyue.lock_legal_balance*1+d._zongyue.lever_balance+d._zongyue.lock_lever_balance+d._zongyue.lock_micro_balance*1+d._zongyue.micro_balance*1;
                                // }}
                                                             
                        ]]
                    });
                }
                var user_id = $("input[name='user_id']").val();
                tbRend("{{url('/agent/users_wallet_total')}}?user_id=" + user_id);
               
            });
        }
    </script>

@endsection