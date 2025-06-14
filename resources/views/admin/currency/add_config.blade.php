@extends('admin._layoutNew')

@section('page-head')
<style>
    .layui-form-label {
        width: 150px;
    }
    .layui-input-block {
        margin-left: 180px;
    }
</style>
@endsection

@section('page-content')
    <form class="layui-form" action="">
        <input type="hidden" id="myid" data-type="0">
        <div class="layui-form-item">
            <label class="layui-form-label">期限</label>
            <div class="layui-input-block">
                <input type="text" name="day" id="day" required autocomplete="off" placeholder="" class="layui-input">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">利息</label>
            <div class="layui-input-block">
                <input type="text" name="rate" id="rate" required autocomplete="off" placeholder="" class="layui-input">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">最小值</label>
            <div class="layui-input-block">
                <input type="text" name="save_min" id="save_min"  autocomplete="off" placeholder="" class="layui-input">
            </div>
        </div>
        <div class="layui-form-item">
            <div class="layui-input-block">
                <button class="layui-btn" lay-submit="" lay-filter="demo1">立即提交</button>
                <button type="reset" class="layui-btn layui-btn-primary">重置</button>
            </div>
        </div>
    </form>

@endsection

@section('scripts')
    <script>
    let url=window.location.href;
    if(url.indexOf("c_id")!=-1){
        let id=url.substr(url.indexOf("c_id")+5);
        $("#myid").attr('data-type',0)
        $("#myid").val(id);
    }else if(url.indexOf("id")!=-1){
        let arr=url.substr(url.indexOf("?")+1);
        arr=arr.split("&");
        for(let i=0;i<arr.length;i++){
            if(i==0){
            $("#myid").attr('data-type',1)
            $("#myid").val(arr[i].split("=")[1]);
            }
            if(i==1){
            $("#day").val(arr[i].split("=")[1]);
            }
            if(i==2){
            $("#rate").val(arr[i].split("=")[1]);
            }
            if(i==3){
            $("#save_min").val(arr[i].split("=")[1]);
            }
        }
        
    }
        layui.use(['form','laydate'],function () {
            var form = layui.form
                ,$ = layui.jquery
                ,laydate = layui.laydate
                ,index = parent.layer.getFrameIndex(window.name);
            //监听提交
            form.on('submit(demo1)', function(data){
                var data = data.field;
                if($("#myid").attr('data-type')==0){
                    data['currency_id']=$("#myid").val();
                    $.ajax({
                        url:'{{url('admin/currency/add_deposit_config')}}'
                        ,type:'post'
                        ,dataType:'json'
                        ,data : data
                        ,success:function(res){
                            if(res.type=='error'){
                                layer.msg(res.message);
                            }else{
                                parent.layer.close(index);
                                parent.window.location.reload();
                            }
                        }
                    });
                    return false;
                 }
                 if($("#myid").attr('data-type')==1){
                     data['id']=$("#myid").val();
                    $.ajax({
                        url:'{{url('admin/currency/edit_deposit_config')}}'
                        ,type:'post'
                        ,dataType:'json'
                        ,data : data
                        ,success:function(res){
                            if(res.type=='error'){
                                layer.msg(res.message);
                            }else{
                                parent.layer.close(index);
                                parent.window.location.reload();
                            }
                        }
                    });
                    return false;
                 }
            });
        });
    </script>

@endsection