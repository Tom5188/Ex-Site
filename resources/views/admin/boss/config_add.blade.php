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
        <input type="hidden" id="id" data-type="0" >
        <div class="layui-form-item">
			<label for="currency_id" class="layui-form-label">币种</label>
			<div class="layui-input-block">
				<select name="currency_id" lay-verify="required" id="currency_id" lay-search>
					@foreach ($currencies as $currency)
						<option value="{{ $currency->id }}"> {{ $currency->name }}</option>
					@endforeach
				</select>
			</div>
		</div>
        <div class="layui-form-item">
            <label class="layui-form-label">期限</label>
            <div class="layui-input-block">
                <input type="text" name="day" id="day" required autocomplete="off" placeholder="" class="layui-input" value="30">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">日利息(百分比)</label>
            <div class="layui-input-block">
                <input type="text" name="rate" id="rate" required autocomplete="off" placeholder="" class="layui-input">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">储蓄名称</label>
            <div class="layui-input-block">
                <input type="text" name="intro" id="intro" required autocomplete="off" placeholder="" class="layui-input">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">最小存币数</label>
            <div class="layui-input-block">
                <input type="text" name="save_min" id="save_min"  autocomplete="off" placeholder="" class="layui-input">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">最大存币数</label>
            <div class="layui-input-block">
                <input type="text" name="save_max" id="save_max"  autocomplete="off" placeholder="" class="layui-input">
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
        layui.use(['form','laydate'],function () {
            var form = layui.form
                ,$ = layui.jquery
                ,laydate = layui.laydate
                ,index = parent.layer.getFrameIndex(window.name);
            //监听提交
            form.on('submit(demo1)', function(data){
                var data = data.field;
                
                    $.ajax({
                        url:'{{url('admin/deposit/add_deposit_config')}}'
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
                
                
            });
        });
    </script>

@endsection