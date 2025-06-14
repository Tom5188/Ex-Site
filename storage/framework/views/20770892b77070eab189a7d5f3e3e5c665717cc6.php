<?php $__env->startSection('page-head'); ?>
    <style>
        .layui-form-label {
            width:90px;
        }
        .layui-input-block {
            margin-left: 120px;
        }
    </style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('page-content'); ?>
    <form class="layui-form">
        <div class="layui-form-item">
            <label class="layui-form-label">理财名称</label>
            <div class="layui-input-block">
                <input type="text" autocomplete="off" placeholder="" disabled="disabled" class="layui-input" value="<?php echo e($results->name); ?>">
                <input type="hidden" name="id" placeholder="" disabled="disabled" class="layui-input" value="<?php echo e($results->id); ?>">
            </div>
        </div>
         <div class="layui-form-item">
            <label class="layui-form-label">状态(可提前停止用户购买项目)</label>
            <div class="layui-input-block">
                <select name="status" lay-verify="required">
                    <option value="0" <?php echo e(($results->status) == 0 ? 'selected' : ''); ?> >结束</option>
                    <option value="1" <?php echo e(($results->status) == 1 ? 'selected' : ''); ?> >进行</option>
                </select>
            </div>
        </div>
        
        <div class="layui-form-item">
            <label class="layui-form-label">年化收益率%</label>
            <div class="layui-input-block">
                <input type="number" step="any" name="rate" autocomplete="off" placeholder="" class="layui-input" value="<?php echo e($results->rate); ?>" lay-verify="required">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">总份额</label>
            <div class="layui-input-block">
                 <input type="number" name="total_number" autocomplete="off" placeholder="" class="layui-input" value="<?php echo e($results->total_number); ?>" lay-verify="required">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">限制份数</label>
            <div class="layui-input-block">
                 <input type="number" name="user_limit" autocomplete="off" placeholder="" class="layui-input" value="<?php echo e($results->user_limit); ?>">
            </div>
        </div>
        <div class="layui-form-item">
            <div class="layui-input-block">
                <button class="layui-btn" lay-submit lay-filter="saveDual_submit">立即提交</button>
                <button type="reset" class="layui-btn layui-btn-primary">重置</button>
            </div>
        </div>
    </form>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
    <script>
        
        layui.use(['form','laydate'],function () {
            var form = layui.form
                ,$ = layui.jquery
                ,laydate = layui.laydate
                ,index = parent.layer.getFrameIndex(window.name);
            //监听提交
            form.on('submit(saveDual_submit)', function(data){
                var data = data.field;
                // data.start_time=data.start_time.replace("T"," ")
                // data.end_time=data.end_time.replace("T"," ")

                $.ajax({
                    url:'<?php echo e(url('/admin/dual/editDual')); ?>'
                    ,type:'post'
                    ,dataType:'json'
                    ,data : data
                    ,success:function(res){
                        if(res.type=='ok'){
                            layer.msg('操作成功');
                        }if(res.type=='error'){
                            layer.msg(res.message);
                        }else{
                            layer.msg(res.message);
                            parent.layer.close(index);
                            parent.window.location.reload();
                        }
                    }
                });
                return false;
            });
        });
        
        getNowPrice();
        setInterval(()=>{
            getNowPrice()
        },5000)
        
        //获取实时价格
        function getNowPrice(){
            const val = $("#currency option:selected").val()

            $.ajax({
                url:'<?php echo e(url('/admin/dual/getnewprice')); ?>'
                ,type:'get'
                ,dataType:'json'
                ,data : 'currency=' +val
                ,success:function(res){
                    if (res.message.data.length) {
                        const value = res.message.data[0]
                        $("#nowPrice").val(value.price)
                    }
                }
            });
        }
    </script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin._layoutNew', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>