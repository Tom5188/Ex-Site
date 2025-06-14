@extends('admin._layoutNew')

@section('page-head')

@endsection

@section('page-content')
    <div class="layui-form">
        <div class="layui-inline">
            <label class="layui-form-label">哈希值</label>
            <div class="layui-input-inline" style="width: 200px">
                <input class="layui-input" name="code" type="text" value="" placeholder="请输入哈希值">
            </div>
        </div>
        <div class="layui-inline">
            <label class="layui-form-label">交易币种</label>
            <div class="layui-input-inline" style="width: 150px">
                <select name='currency'>
                    <option class='layui-option' value="" >请选择</option>
                @foreach($currency as $key => $value)
                    <option class='layui-option' value="{{$value['id']}}" >{{$value['name']}}</option>
                @endforeach
                </select>
            </div>
        </div>
        <div class="layui-inline">
            <label class="layui-form-label">买入者ID</label>
            <div class="layui-input-inline" style="width: 200px">
                <input class="layui-input" name="buyer_id" type="text" value="" placeholder="请输入买入者ID">
            </div>
        </div>
        <div class="layui-inline" style="margin-left: 10px;">
            <div class="layui-input-inline" style="width: 60px;">
                <button class="layui-btn" lay-submit="bindQuotationSearch" lay-filter="bindQuotationSearch">查询</button>
            </div>
        </div>
        <table id="bindQuotationList" lay-filter="bindQuotationList"></table>
        <!--<script type="text/html" id="barBind">-->
        <!--    <a class="layui-btn layui-btn-xs" lay-event="edit">编辑</a>-->
        <!--</script>-->
        <!--<script type="text/html" id="bindTpl">-->
        <!--    <input type="checkbox" name="status" value="@{{d.id}}" lay-skin="switch" lay-text="是|否" lay-filter="sexDemo" @{{ d.status == 1 ? 'checked' : '' }}>-->
        <!--</script>-->
    </div>
@endsection

@section('scripts')
    <script type="text/html" id="bindOrderStatus">
        @{{d.status==1 ? '<span class="layui-badge layui-bg-green">'+'有效'+'</span>' : '' }}
        @{{d.status==0 ? '<span class="layui-badge layui-bg-red">'+'无效'+'</span>' : '' }}
    </script>
    <script type="text/html" id="bindCode">
        
    </script>
    <script>
        
        layui.use(['element', 'form', 'layer', 'table'], function () {
            var element = layui.element
            ,layer = layui.layer
            ,table = layui.table
            ,$ = layui.$
            ,form = layui.form
            //第一个实例
            var bindQuotationList = table.render({
               
                elem: '#bindQuotationList'
                ,url: '{{url('admin/bind_box/quotationList')}}' //数据接口
                ,page: true //开启分页
                ,limit: 20
                ,limits: [20, 50, 100, 200, 500, 1000]
                ,cols: [[ //表头
                    {field: 'id', title: '订单ID', minWidth:50, sort: true}
                    ,{field: 'code', title: '哈希值', minWidth:350}
                    ,{field: 'currency_name', title:'交易币种', minWidth:80}
                    ,{field: 'buyer_id', title: '买入者ID', minWidth:85}
                    ,{field: 'status', title: '状态', minWidth:80, templet:"#bindOrderStatus"}
                    ,{field: 'price', title: '成交价', minWidth:110, sort: true}
                    ,{field: 'created', title:'出价时间', minWidth:150}
                    // ,{title: '操作',fixed: 'right', Width:50, toolbar: '#barBind'}
                ]]
            });

            // // 监听工具条
            // table.on('tool(bindQuotationList)', function (obj) {
            //     var layEvent = obj.event
            //         ,data = obj.data
            //     if (layEvent === 'edit') {
            //         var index = layer.open({
            //             title: '编辑'
            //             ,type: 2
            //             ,content: '{{url('/admin/bind_box/edit')}}?id=' + data.id
            //             ,area: ['960px', '700px']
            //         });
            //     }
            // });
            
            //监听提交
            form.on('submit(bindQuotationSearch)', function (data) {
                bindQuotationList.reload({
                    where: data.field
                    ,page: {
                        curr: 1 //重新从第 1 页开始
                    }
                });
                return false;
            });

        });
    </script>

@endsection