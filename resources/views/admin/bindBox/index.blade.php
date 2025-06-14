@extends('admin._layoutNew')

@section('page-head')
    <style type="text/css">
        .layui-table-cell{
        	height: auto;
        	white-space: normal;
        	/*overflow:visible;*/
         /*   text-overflow:inherit;*/
        }
        .layui-table img{
        	/*max-width:100px*/
        }
    </style>
@endsection

@section('page-content')
    <button class="layui-btn layui-btn-normal layui-btn-radius" onclick="layer_show('添加NFT','{{url('admin/bind_box/add')}}')">添加NFT</button>
    <button class="layui-btn layui-btn-danger layui-btn-radius "  lay-event="datadel" id="datadel"><i class="layui-icon layui-icon-delete" style="font-size:20px;"></i> 批量删除</button>
    <div class="layui-form" style="margin-top:10px;">
        <div class="layui-inline">
            <label class="layui-form-label">哈希值</label>
            <div class="layui-input-inline" style="width: 200px">
                <input class="layui-input" name="code" type="text" value="" placeholder="请输入哈希值">
            </div>
        </div>
        <!--@if(!empty($currency))-->
        <!--<div class="layui-inline">-->
        <!--    <label class="layui-form-label"><span class="reddot">*</span>交易币种</label>-->
        <!--    <div class="layui-input-inline">-->
        <!--        <select name='currency'>-->
        <!--        @foreach($currency as $key => $value)-->
        <!--            <option class='layui-option' value="{{$value['id']}}" >{{$value['name']}}</option>-->
        <!--        @endforeach-->
        <!--        </select>-->
        <!--    </div>-->
        <!--</div>-->
        <!--@endif-->
        <div class="layui-inline">
            <label class="layui-form-label">交易类型</label>
            <div class="layui-input-inline" style="width:90px;">
                <select name="pay_type">
                    <option value="">全部</option>
                    <option value="1">一口价</option>
                    <option value="2">竞拍</option>
                    <option value="3">盲盒</option>
                </select>
            </div>
        </div>
        <div class="layui-inline">
            <label class="layui-form-label">名称</label>
            <div class="layui-input-inline" style="width: 200px">
                <input class="layui-input" name="name" type="text" value="" placeholder="请输入名称">
            </div>
        </div>
        <div class="layui-inline">
            <label class="layui-form-label">作者ID</label>
            <div class="layui-input-inline" style="width: 200px">
                <input class="layui-input" name="author" type="text" value="" placeholder="请输入作者ID">
            </div>
        </div>
        <div class="layui-inline">
            <label class="layui-form-label">稀有度</label>
            <div class="layui-input-inline" style="width:90px;">
                <select name="rarity">
                    <option value="">全部</option>
                    <option value="N" style="color:lightgray">N</option>
                    <option value="R" style="color:lightblue">R</option>
                    <option value="SR" style="color:purple">SR</option>
                </select>
            </div>
        </div>
        <div class="layui-inline" style="margin-left: 10px;">
            <div class="layui-input-inline" style="width: 60px;">
                <button class="layui-btn" lay-submit="bindSearch" lay-filter="bindSearch">查询</button>
            </div>
        </div>
        <table id="bindlist" lay-filter="bindlist"></table>
        <script type="text/html" id="barBind">
            <a class="layui-btn layui-btn-xs" lay-event="edit">编辑</a>
            <a class="layui-btn layui-btn-xs layui-btn-danger" lay-event="del">删除</a>
        </script>
        <!--<script type="text/html" id="bindTpl">-->
        <!--    <input type="checkbox" name="status" value="@{{d.id}}" lay-skin="switch" lay-text="是|否" lay-filter="sexDemo" @{{ d.status == 1 ? 'checked' : '' }}>-->
        <!--</script>-->
    </div>
@endsection

@section('scripts')
    <script type="text/html" id="bindType">
        @{{d.type==1 ? '<span class="">'+'图片'+'</span>' : '' }}
        @{{d.type==4 ? '<span class="">'+'视频'+'</span>' : '' }}
    </script>
    <script type="text/html" id="bindTpl">
        @{{d.status==0 ? '<span class="">'+'已结束'+'</span>' : '' }}
        @{{d.status==1 ? '<span class="">'+'开始'+'</span>' : '' }}
        @{{d.status==2 ? '<span class="">'+'未开始'+'</span>' : '' }}
    </script>
    <!--<script type="text/html" id="bindHash">-->
        
    <!--</script>-->
    <!--<script type="text/html" id="logo">-->
    <!--    <img width="40px" src="@{{d.image}}" onmouseover="layer.tips('<div style=\'background-color: #fff;\'><img style=\'width:300px;text-align:center;\' src=@{{d.image}}></div>',this,{tips: [1, '#fff'],area:['350px', 'auto'],time: 100000});" onmouseout="layer.closeAll();">-->
    <!--</script>-->
    <script type="text/html" id="bindTransaction">
        @{{d.pay_type==1 ? '<span class="">'+'一口价'+'</span>' : '' }}
        @{{d.pay_type==2 ? '<span class="">'+'竞拍'+'</span>' : '' }}
        @{{d.pay_type==3 ? '<span class="">'+'盲盒'+'</span>' : '' }}
    </script>
    <script type="text/html" id="bindRarity">
        @{{d.rarity=="N" ? "<span style='color:gray;font-weight:bold'>"+"N"+"</span>" : "" }}
        @{{d.rarity=="R" ? "<span style='color:lightblue;font-weight:bold'>"+"R"+"</span>" : "" }}
        @{{d.rarity=="SR" ? "<span style='color:purple;font-weight:bold'>"+"SR"+"</span>" : "" }}
    </script>
    <script type="text/html" id="bindFiles">
        @{{(/\.(png|jpg|gif|jpeg|webp)$/).test(d.image) == true ? '<img src="'+d.image+'" alt="'+d.image+'" style="width:60px;cursor:pointer" onclick="showBigImage(this)">' : '<video src="'+d.image+'" alt="'+d.name+'" style="width:60px;cursor:pointer" onclick="showBigVideo(this)">' }}
    </script>
    
    <script type="text/javascript">
        //显示大图片
        function showBigImage(e) {
            parent.layer.open({
                type: 1,
                title: false,
                closeBtn: 0,
                shadeClose: true, //点击阴影关闭
                area: [$(e).width + 'px', $(e).height + 'px'], //宽高
                content: "<img style='max-width:1400px;max-height:780px' src=" + $(e).attr('src') + " />"
            });
        }
        function showBigVideo(e) {
            parent.layer.open({
                type: 1,
                title: $(e).attr('alt'),
                closeBtn: 0,
                shadeClose: true, //点击阴影关闭
                offset: 'auto',
                area: 'auto',
                content: "<video controls='controls' style='max-width:1400px;max-height:780px' src=" + $(e).attr('src') + " />"
            });
        }
    </script>
    <script>
        
        layui.use(['element', 'form', 'layer', 'table'], function () {
            var element = layui.element
            ,layer = layui.layer
            ,table = layui.table
            ,$ = layui.$
            ,form = layui.form
            
            //第一个实例
            var bindlist = table.render({
               
                elem: '#bindlist'
                ,url: '{{url('admin/bind_box/list')}}' //数据接口
                ,page: true //开启分页
                ,limit: 20
                ,limits: [20, 50, 100, 200, 500, 1000]
                ,cols: [[ //表头
                    {field: 'check', type: 'checkbox', width: 40}
                    // ,{field: 'id', title: 'ID', width:70, sort: true}
                    ,{field: 'code', title: '哈希值', minWidth:110}
                    ,{field: 'currency_name', title: '交易币种', minWidth:85}
                    ,{field: 'pay_type', title: '交易类型', minWidth:100, templet: '#bindTransaction'}
                    ,{field: 'per_increase', title:'竞拍每次加价', minWidth:120,}
                    ,{field: 'name', title: '名称', minWidth:110}
                    // ,{field: 'number', title: '数量', Width:30}
                    // ,{field: 'description', title: '资产详情', minWidth:80}
                    ,{field: 'author', title: '作者ID', minWidth:80}
                    // ,{field: 'author_description', title: '关于艺术家', minWidth:80}
                    ,{field: 'type', title: '文件类型', minWidth:85, templet: '#bindType'}
                    // ,{field: 'image', title: '文件内容', minWidth:80, templet:'<div><img src="@{{d.image}}" alt="" style="width:60px;cursor:pointer" onclick="showBigImage(this)"></div>'}
                    ,{title: '封面图', minWidth:80, templet: "#bindFiles" }
                    ,{field: 'rarity', title:'稀有度', minWidth:80, templet: '#bindRarity'}
                    ,{field: 'price', title: '价格', minWidth:110, sort: true}
                    ,{field: 'margin', title: '保证金', sort: true}
                    // ,{field: 'status', title:'状态', minWidth:85, templet: '#bindTpl'}
                    // ,{field: 'token_id', title:'唯一Token', minWidth:120}
                    ,{field: 'created', title:'创建时间', minWidth:150}
                    // ,{field: 'start_time', title:'开始时间', minWidth:155 }
                    // ,{field: 'end_time', title:'失效时间', minWidth:155 }
                    // ,{field: 'start_time', title:'开始时间', minWidth:155, templet:function(data){
                    //     return layui.util.toDateString(data.start_time*1000,"yyyy-MM-dd HH:mm:ss")
                    // }}
                    // ,{field: 'end_time', title:'失效时间', minWidth:155, templet:function(data){
                    //     return layui.util.toDateString(data.start_time*1000,"yyyy-MM-dd HH:mm:ss")
                    // }}
                    ,{title: '操作', minWidth:150, toolbar: '#barBind'}
                ]]
            });
            //监听热卖操作
            // form.on('switch(sexDemo)', function(obj){
            //     var id = this.value;
            //     $.ajax({
            //         url:'{{url('admin/currency_display')}}',
            //         type:'post',
            //         dataType:'json',
            //         data:{id:id},
            //         success:function (res) {
            //             if(res.error != 0){
            //                 layer.msg(res.message);
            //             }
            //         }
            //     });
            // });
            
            //监听提交
            form.on('submit(bindSearch)', function (data) {
                bindlist.reload({
                    where: data.field
                    ,page: {
                        curr: 1 //重新从第 1 页开始
                    }
                });
                return false;
            });
            
            
            
            
            //单个删除与编辑
            table.on('tool(bindlist)', function(obj){
                var data = obj.data;
                var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
                var tr = obj.tr; //获得当前行 tr 的DOM对象
                if (layEvent === 'edit') {
                    var index = layer.open({
                        title: '编辑'
                        ,type: 2
                        ,content: '{{url('/admin/bind_box/edit')}}?id=' + data.id
                        ,area: ['960px', '600px']
                    });
                }
                if(layEvent === 'del'){
                    layer.confirm('真的删除此行数据吗？删除后不可恢复！！！', function(index){
                        $.ajax({
                            type: 'POST'
                            ,dataType: 'json'
                            ,url: '/admin/bind_box/del' 
                            ,data:{'id':data.id}
                            ,success: function (data) {
                                if(data.type=='ok'){
                                    layer.msg('操作成功！');
                                    window.location.reload()
                                }else if(data.type=='error'){
                                    layer.msg(data.message);
                                }else{
                                    layer.msg(data.message);
                                    // parent.layer.close(index);
                                }
                            }
                        });
                    });
                }    
            });
            
            //批量删除
            $("#datadel").on('click',function(){
                //获取选中状态            
                var checkStatus = table.checkStatus('bindlist');
                
                const ids = checkStatus.data.map(item => item.id)
                
                //获取选中数量
                if(ids.length == 0){
                    layer.msg('批量删除至少选中一项数据',function(){});
                    return false;
                }
                layer.confirm('真的要删除选中的项吗？删除后不可恢复！！！',function(index){
                    //layer.close(index);
                    
                    $.ajax({
                        type:'post',
                        data:{id:ids},
                        url: '/admin/bind_box/del',
                        success:function(data){
                            if(data.type=='ok'){
                                layer.msg('操作成功！');
                                window.location.reload()
                            }else if(data.type=='error'){
                                layer.msg(data.message);
                            }else{
                                layer.msg(data.message);
                                // parent.layer.close(index);
                                // parent.location.reload();
                            }
                        },error:function(code){   
                            layer.msg('操作失败!',data.message);
                        }
                    });
                })
            });               
            
            
            // //监听工具条
            // table.on('tool(adminList)', function(obj){ //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            //     var data = obj.data; //获得当前行数据
            //     var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            //     var tr = obj.tr; //获得当前行 tr 的DOM对象

            //     if(layEvent === 'del'){ //删除
            //         layer.confirm('真的要删除吗？', function(index){
            //             //向服务端发送删除指令
            //             $.ajax({
            //                 url:'/admin/manager/delete',
            //                 type:'post',
            //                 dataType:'json',
            //                 data:{id:data.id},
            //                 success:function(res){
            //                     if(res.type=='ok'){
            //                         obj.del(); //删除对应行（tr）的DOM结构，并更新缓存
            //                         layer.msg(res.message);
            //                         layer.close(index);
            //                     }else{
            //                         layer.close(index);
            //                         layer.alert(res.message);
            //                     }
            //                 }
            //             });
            //         });
            //     } else if(layEvent === 'edit'){ //编辑
            //         //do something
            //             layer_show('修改管理员', '/admin/manager/add?id=' + data.id);

            //     }
            // });
            
        });
    </script>

@endsection