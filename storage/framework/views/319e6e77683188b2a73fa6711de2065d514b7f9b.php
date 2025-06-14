

<?php $__env->startSection('page-head'); ?>
    <style type="text/css">
        .layui-table-cell{
        	height: auto;
        	white-space: normal;
        }
        .layui-table img{
        	/*max-width:100px*/
        }
    </style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('page-content'); ?>
    <button class="layui-btn layui-btn-normal layui-btn-radius" onclick="layer_show('添加盲盒','<?php echo e(url('admin/bind_box/add_rarity_house_view')); ?>','960','500')">添加盲盒</button>
    <div class="layui-form">
        <div class="layui-inline">
            <label class="layui-form-label">名称</label>
            <div class="layui-input-inline" style="width: 200px">
                <input class="layui-input" name="name" type="text" value="" placeholder="请输入名称">
            </div>
        </div>
        <div class="layui-inline">
            <label class="layui-form-label">稀有度</label>
            <div class="layui-input-inline" style="width:90px;">
                <select name="rarity">
                    <option value="">全部</option>
                    <option value="N">N</option>
                    <option value="R">R</option>
                    <option value="SR">SR</option>
                </select>
            </div>
        </div>
        <div class="layui-inline" style="margin-left: 10px;">
            <div class="layui-input-inline" style="width: 60px;">
                <button class="layui-btn" lay-submit="bindBoxSearch" lay-filter="bindBoxSearch">查询</button>
            </div>
        </div>
    
        <table id="bindBoxlist" lay-filter="bindBoxlist"></table>
        
        
        <!--<script type="text/html" id="bindTpl">-->
        <!--    <input type="checkbox" name="status" value="{{d.id}}" lay-skin="switch" lay-text="是|否" lay-filter="sexDemo" {{ d.status == 1 ? 'checked' : '' }}>-->
        <!--</script>-->
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
    
    <script type="text/html" id="bindBoxstatus">
        {{d.status==0 ? '<span class="">'+'未开启'+'</span>' : '' }}
        {{d.status==1 ? '<span class="">'+'已开启'+'</span>' : '' }}
    </script>
    <script type="text/html" id="barBindbox">
        {{d.status==0 ? '<a class="layui-btn layui-btn-xs" lay-event="edit">编辑</a>' : '' }}
    </script>
    <script type="text/html" id="bindRarity">
        {{d.rarity=="N" ? "<span style='color:lightgray;font-weight:bold'>"+"N"+"</span>" : "" }}
        {{d.rarity=="R" ? "<span style='color:lightblue;font-weight:bold'>"+"R"+"</span>" : "" }}
        {{d.rarity=="SR" ? "<span style='color:purple;font-weight:bold'>"+"SR"+"</span>" : "" }}
    </script>
    <script type="text/html" id="bindRarity">
        {{d.rarity=="N" ? "<span style='color:lightgray;font-weight:bold'>"+"N"+"</span>" : "" }}
        {{d.rarity=="R" ? "<span style='color:lightblue;font-weight:bold'>"+"R"+"</span>" : "" }}
        {{d.rarity=="SR" ? "<span style='color:purple;font-weight:bold'>"+"SR"+"</span>" : "" }}
    </script>
    <script type="text/html" id="bindBoxfile">
        {{(/\.(png|PNG|jpg|JPG|gif|GIF|jpeg|JPEG|webp|WEBP)$/).test(d.file) == true ? '<img src="'+d.file+'" alt="" style="width:60px;cursor:pointer" onclick="showBigImage(this)">' : '<video src="'+d.file+'" alt="'+d.name+'" style="width:60px;cursor:pointer" onclick="showBigVideo(this)">' }}
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
            var bindBoxlist = table.render({
               
                elem: '#bindBoxlist'
                ,url: '<?php echo e(url('admin/bind_box/rarity_house_list')); ?>' //数据接口
                ,page: true //开启分页
                ,limit: 20
                ,limits: [20, 50, 100, 200, 500, 1000]
                ,cols: [[ //表头
                    {field: 'id', title: '盲盒ID', width:110, sort: true}
                    ,{field: 'name', title: '名称', width:150}
                    ,{field: 'file', title: '文件内容', width:150, templet:'#bindBoxfile'}
                    // ,{field: 'rarity', title: '稀有度', width:85, templet: '#bindRarity'}
                    ,{field: 'status', title: '状态', width:85, templet:"#bindBoxstatus"}
                    ,{field: 'created', title: '创建时间', width:180}
                    ,{title: '操作',fixed: 'right', width:100, toolbar: '#barBindbox'}
                ]]
            });

            // 监听工具条
            table.on('tool(bindBoxlist)', function (obj) {
                var layEvent = obj.event
                    ,data = obj.data
                if (layEvent === 'edit') {
                    var index = layer.open({
                        title: '编辑'
                        ,type: 2
                        ,content: '<?php echo e(url('/admin/bind_box/edit_rarity_house_view')); ?>?id=' + data.id
                        ,area: ['960px', '700px']
                    });
                }
            });
            //监听提交
            form.on('submit(bindBoxSearch)', function (data) {
                bindBoxlist.reload({
                    where: data.field
                    ,page: {
                        curr: 1 //重新从第 1 页开始
                    }
                });
                return false;
            });

        });
    </script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin._layoutNew', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>