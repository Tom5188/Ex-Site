/**

 @Name：CLTPHP 权限列表
 @Author：chichu
 @Site：http://www.cltphp.com/admin/
 @License：LPPL
 */

layui.define(['admin', 'table', 'util','form','treeGrid'], function (exports) {
    var $ = layui.$, admin = layui.admin, table = layui.table, element = layui.element,form = layui.form,treeGrid = layui.treeGrid, tableId='list';

   alert(111)
    /*****************************权限操作************************************/
    treeGrid.render({
        id:tableId
        ,elem: '#'+tableId
        ,idField:'id'
        ,url:'/manages/auth/rule'
        ,cellMinWidth: 100
        ,treeId:'id'//树形id字段名称
        ,treeUpId:'pid'//树形父id字段名称
        ,treeShowName:'title'//以树形式显示的字段
        ,height:'full-140'
        ,isFilter:false
        ,iconOpen:false//是否显示图标【默认显示】
        ,isOpenDefault:false//节点默认是展开还是折叠【默认展开】
        ,loading:true
        ,method:'post'
        ,cols: [[
            {field: 'id', title: '编号', width: 70, fixed: true},
            {field: 'icon', align: 'center', title: '图标', width: 60, templet: '#icon'},
            {field: 'title', title: '权限名称', width: 200,edit:'text'},
            {field: 'url', title: '控制器/方法', width: 200,edit:'text'},
            {field: 'name', title: '菜单名', width: 200,edit:'text'},
            {field: 'authopen', align: 'center', title: '是否验证权限', width: 150, toolbar: '#auth'},
            {field: 'menustatus', align: 'center', title: '菜单状态', width: 150, toolbar: '#rule_status'},
            {field: 'sort', align: 'center', title: '排序', width: 80, edit:'text'},
            {width: 160, align: 'center', toolbar: '#rule_action'}
        ]]
        ,page:false
    });
    //添加权限页面
    var events = {
        add: function(){
            layer.open({
                type: 2,
                content: '/manages/auth/add',
                area: ['90%', '90%'],
                maxmin: true
            });
        },
    };
    $('.clt-auth-btns .layui-btn').on('click', function(){
        var othis = $(this),thisEvent = othis.data('events');
        events[thisEvent] && events[thisEvent].call(this);
    });
    //添加提交
    form.on('submit(add-rule)', function(data){
        admin.req({
            url: '/manages/auth/add/'
            , type: 'post'
            , data: data.field
            , done: function (res) {
                layer.msg(res.msg, {icon: 1, time: 1000}, function () {
                    layer.closeAll("iframe");
                    //刷新父页面
                    parent.location.reload();
                });
            }
        });
    });
    //修改字段
    treeGrid.on('edit(list)', function(obj){
        var param={};
        param[obj.field] = obj.value;
        param['id'] = obj.data.id;
        admin.req({
            url: '/manages/auth/cf',
            data: param,
            type:'post',
            success: function (res) {
                if(res.code==0){
                    layer.msg(res.msg,{time:1000,icon:1});
                }
                tableIn.reload();
            }
        });
    });
    //修改状态
    form.on('switch()', function(obj){
        loading =layer.load(1, {shade: [0.1,'#fff']});
        var param={};
        param[obj.elem.name] = obj.elem.checked===true?1:0;
        param['id'] = this.value;
        admin.req({
            url: '/manages/auth/cf',
            data: param,
            type:'post',
            success: function (res) {
                if(res.code>0){
                    if(obj.elem.checked==true){
                        $(obj.elem).prop('checked',false);
                    }else{
                        $(obj.elem).prop('checked',true);
                    }
                    form.render();
                }else{
                    layer.msg(res.msg,{time:1000,icon:1});
                }
                layer.closeAll('loading');
            }
        });
    });


    //监听工具条
    treeGrid.on('tool(list)', function(obj){
        var data = obj.data;
        var id = data.id;
        if(obj.event === 'del_btn'){
            layer.confirm('您确定要删除该权限吗？', function(index){
                admin.req({
                    url: '/manages/auth/del/'
                    , type: 'post'
                    , data: {'id':id}
                    , success: function (res) {
                        layer.closeAll('dialog');
                        if(res.code==0){
                            layer.msg(res.msg, {icon: 1, time: 1000}, function () {
                                obj.del();
                            });
                        }
                    }
                });
            });
        } else if(obj.event === 'auth_edit'){
            layer.open({
                type: 2,
                content: '/manages/auth/edit/id/'+id,
                area: ['90%', '90%'],
                maxmin: true
            });
        }else if(obj.event === 'auth_add'){
            layer.open({
                type: 2,
                content: '/manages/auth/add/id/'+id,
                area: ['90%', '90%'],
                maxmin: true
            });
        }
    });
    //提交编辑
    form.on('submit(edit-rule)', function(data){
        admin.req({
            url: '/manages/auth/edit'
            , type: 'post'
            , data: data.field
            , done: function (res) {
                layer.msg(res.msg, {icon: 1, time: 1000}, function () {
                    layer.closeAll("iframe");
                    //刷新父页面
                    parent.location.reload();
                });
            }
        });
    });

    /*****************************分组操作************************************/
    //权限列表
    tableIn = table.render({
        elem: '#group',
        url: '/manages/auth/group',
        toolbar: '#toolbarGroup',
        defaultToolbar:'',
        method: 'post',
        cols: [[
            {field:'id', title: 'ID',width:80, fixed: true,sort: true},
            {field:'title', title: '用户组名', width:180},
            {field:'create_time', title: '添加时间', width:200,sort: true},
            {field:'update_time', title: '修改时间', width:200,sort: true},
            {width:260, align:'center',toolbar:'#group_action'}
        ]]
    });
    //头工具栏事件
    table.on('toolbar(group)', function(obj){
        if(obj.event=='add'){
            layer.open({
                type: 2,
                content: '/manages/auth/groupAdd',
                area: ['90%', '90%'],
                maxmin: true
            });
        }
    });
    //添加提交
    form.on('submit(add-btn)', function(data){
        admin.req({
            url: '/manages/auth/groupAdd/'
            , type: 'post'
            , data: data.field
            , done: function (res) {
                layer.msg(res.msg, {icon: 1, time: 1000}, function () {
                    layer.closeAll("iframe");
                    //刷新父页面
                    parent.location.reload();
                });
            }
        });
    });
    //监听工具条
    table.on('tool(group)', function(obj){
        var data = obj.data;
        var id = data.id;
        if(obj.event === 'group_del'){
            layer.confirm('您确定要删除该分组吗？', function(index){
                admin.req({
                    url: '/manages/auth/groupDel/'
                    , type: 'post'
                    , data: {'id':id}
                    , success: function (res) {
                        layer.closeAll('dialog');
                        if(res.code==0){
                            layer.msg(res.msg, {icon: 1, time: 1000}, function () {
                                obj.del();
                            });
                        }
                    }
                });
            });
        } else if(obj.event === 'group_edit'){
            layer.open({
                type: 2,
                content: '/manages/auth/groupEdit/id/'+id,
                area: ['90%', '90%'],
                maxmin: true
            });
        }else if(obj.event === 'groupAccess'){
            layer.open({
                type: 2,
                content: '/manages/auth/groupAccess.html?id='+id,
                area: ['90%', '90%'],
                maxmin: true
            });
        }
    });
    //提交编辑
    form.on('submit(edit-btn)', function(data){
        admin.req({
            url: '/manages/auth/groupEdit'
            , type: 'post'
            , data: data.field
            , done: function (res) {
                layer.msg(res.msg, {icon: 1, time: 1000}, function () {
                    layer.closeAll("iframe");
                    //刷新父页面
                    parent.location.reload();
                });
            }
        });
    });
    exports('auth', {});
});