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
    <div style="margin-top: 10px;width: 100%;">
        

        <form class="layui-form layui-form-pane layui-inline" action="">

            <div class="layui-inline">
                <div class="layui-input-inline">
                    <input type="text" placeholder="ID,账号" name="account_name" autocomplete="off" class="layui-input">
                </div>
            </div>
            <div class="layui-inline" style="margin-left: 10px">
                <div class="layui-input-inline">
                    <button class="layui-btn" lay-submit="search" lay-filter="search"><i class="layui-icon">&#xe615;</i></button>
                </div>
            </div>
            



        </form>
       
    </div>

    <script type="text/html" id="switchTpl">
        <input type="checkbox" name="is_recommend" value="@{{d.id}}" lay-skin="switch" lay-text="是|否" lay-filter="sexDemo" @{{ d.is_recommend == 1 ? 'checked' : '' }}>
    </script>

    <table id="demo" lay-filter="test"></table>
    <script type="text/html" id="barDemo">
        <button type="button" class="layui-btn layui-btn-sm layui-btn-normal" lay-event="show">
            <i class="layui-icon layui-icon-screen-full"></i> 查看
        </button>
    </script>
    <script type="text/html" id="statustml">
        @{{d.status==1 ? '<span class="layui-badge status_bg_1">'+'申请充值'+'</span>' : '' }}
        @{{d.status==2 ? '<span class="layui-badge status_bg_2">'+'充值完成'+'</span>' : '' }}
        @{{d.status==3 ? '<span class="layui-badge status_bg_3">'+'申请拒绝'+'</span>' : '' }}

    </script>

    <script type="text/html" id="acc">
        <a class="layui-btn layui-btn-primary layui-btn-xs" lay-event="img">显示凭证</a>
    </script>

@endsection

@section('scripts')
    <script type="text/javascript">
        //显示大图片
        function showBigImage(e) {
            parent.layer.open({
                type: 1,
                title: false,
                closeBtn: 0,
                shadeClose: true, //点击阴影关闭
                area: [$(e).width + 'px', $(e).height + 'px'], //宽高
                content: "<img style='max-width:1400px;max-height:800px' src=" + $(e).attr('src') + " />"
            });
        }
    </script>
    <script>

        layui.use(['table','form'], function(){
            var table = layui.table;
            var $ = layui.jquery;
            var form = layui.form;
            //第一个实例
            table.render({
                elem: '#demo'
                ,url: "{{url('admin/user/charge_list')}}" //数据接口
                ,page: true //开启分页
                ,id:'mobileSearch'
                ,cols: [[ //表头
                    {field: 'id', title: 'ID', minWidth:100, sort: true}
                    ,{field: 'uid', title: '用户ID', minWidth:200}
                    ,{field: 'account_name', title: '用户名', minWidth:200}
                    ,{field: 'currency_name', title: '虚拟币', minWidth:100}
                    // ,{field: 'user_account', title: '支付账号', minWidth:110}
                    // ,{field: 'user_account', title: '支付凭证', minWidth:110,templet:"#acc"}
                    ,{field: 'user_account', title: '充值凭证', minWidth:110, templet:"#acc"}
                    // ,{field: 'bank_account', title: '银行卡号', minWidth:80,templet:function(d){
                    //     if(d.type){
                    //         return d.bank_account;
                    //     }else{
                    //         return '';
                    //     }
                    // }}
                    // ,{field: 'address', title: '提币地址', minWidth:100}
                    ,{field: 'amount', title: '数量', minWidth:100}
                    ,{field: 'give', title: '赠送数量', minWidth:100}
                    // ,{field: 'amount', title: '充值金额￥', minWidth:80,templet:function(d){
                    //     let give = 0;
                    //     if(d.give) give = d.give;
                    //     return (d.amount*d.rmb_relation*d.price) + (give*d.rmb_relation*d.price) +"元";
                    // }}
                    // ,{field: 'hes_account', title: '承兑商交易账号', minWidth:180}
                    // ,{field: 'money', title: '交易额度', minWidth:100}
                    ,{field: 'status', title: '交易状态', minWidth:100, templet: '#statustml'}
                    ,{field: 'created_at', title: '充币时间', minWidth:180}
                   
                    ,{title:'操作', minWidth:120, templet: '#barDemo'}

                ]]
            });
            form.on('submit(search)', function (data) {
                // data_table.reload({
                //     where: data.field
                //     ,page: {
                //         curr: 1 //重新从第 1 页开始
                //     }
                // });
                // return false;
                var account_number = data.field.account_name;
                table.reload('mobileSearch',{
                    where:{account_name:account_number},
                    page: {curr: 1}         //重新从第一页开始
                });
                return false;
            });
            //监听热卖操作
            // form.on('switch(sexDemo)', function(obj){
            //     var id = this.value;
            //     $.ajax({
            //         url:'{{url('admin/product_hot')}}',
            //         type:'post',
            //         dataType:'json',
            //         data:{id:id},
            //         success:function (res) {
            //             if(res.error != 0){
            //                 layer.msg(res.msg);
            //             }
            //         }
            //     });
            // });
            table.on('tool(test)', function(obj){
                var data = obj.data;
                if(obj.event === 'show'){
                    layer.open({
                        type: 2,
                        title: '充值信息',
                        shadeClose: true,
                        shade: 0.8,
                        area: ['80%', '80%'],
                        content: '{{url('admin/user/charge_show')}}?id='+data.id
                    });
                }else if(obj.event === 'img'){
                    var resourcesUrl = '{{$imageServerUrl}}'+data.user_account;
                    if (resourcesUrl == "") {
                        layer.msg("没有发现图片！");
                        return;
                    }
                    var img = new Image();
                    img.onload = function () {//避免图片还未加载完成无法获取到图片的大小。
                        //避免图片太大，导致弹出展示超出了网页显示访问，所以图片大于浏览器时下窗口可视区域时，进行等比例缩小。
                        var max_height = $(window).height() - 100;
                        var max_width = $(window).width();
        
                        //rate1，rate2，rate3 三个比例中取最小的。
                        var rate1 = max_height / img.height;
                        var rate2 = max_width / img.width;
                        var rate3 = 1;
                        var rate = Math.min(rate1, rate2, rate3);
                        //等比例缩放
                        var imgHeight = img.height * rate; //获取图片高度
                        var imgWidth = img.width * rate; //获取图片宽度
        
                        var imgHtml = "<img src='" + resourcesUrl + "' width='" + imgWidth + "px' height='" + imgHeight + "px'/>";
                        //弹出层
                        layer.open({
                           type:1,//可传入的值有：0（信息框，默认）1（页面层）2（iframe层）3（加载层）4（tips层）
                           shade: 0.6,
                           maxmin: true,
                           anim: 1,
                           title: ' ',
                           area: ['auto', 'auto'],
                           // skin: 'layui-layer-nobg', //没有背景色
                           shadeClose: true,
                           content: imgHtml
                        });
                    }
                    img.src = resourcesUrl;
                    // layer.photos({
                    //     photos: '{{$imageServerUrl}}'+data.user_account //格式见API文档手册页
                    //     ,anim: 5 //0-6的选择，指定弹出图片动画类型，默认随机
                    // });
                }
            });

		})
    </script>

@endsection