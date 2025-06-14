

<?php $__env->startSection('page-head'); ?>
    <style>
        .layui-form-label {
            width:90px;
        }
        .layui-input-block {
            margin-left: 120px;
        }
        .reddot {
            position: absolute;
            right:5%;
            color: red;
        }
    </style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('page-content'); ?>
    <form class="layui-form" action="">
        
        <div class="layui-form-item">
            <label class="layui-form-label"><span class="reddot">*</span>理财币种</label>
            <div class="layui-input-block">
                <select name='currency' id="currency">
                    <option value='1' class='layui-option'>BTC</option>
                    <option value='2' class='layui-option'>ETH</option>
                </select>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label"><span class="reddot">*</span>类型</label>
            <div class="layui-input-block">
                 <select name='type'>
                    <option value='call' class='layui-option'>看涨（call）</option>
                    <option value='put' class='layui-option'>看跌（put）</option>
                </select>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label"><span class="reddot">*</span>年化收益率%</label>
            <div class="layui-input-block">
                <input type="number" name="rate" autocomplete="off" placeholder="" class="layui-input" value="" lay-verify="required">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label"><span class="reddot">*</span>实时价格</label>
            <div class="layui-input-block">
                <input type="text"  disabled="disabled"  class="layui-input" value="" id="nowPrice">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label"><span class="reddot">*</span>挂钩参考价</label>
            <div class="layui-input-block">
                <input type="number" name="exercise_price" autocomplete="off" placeholder="" class="layui-input" value="" lay-verify="required">
            </div>
        </div>
         <div class="layui-form-item">
            <label class="layui-form-label"><span class="reddot">*</span>开始日期</label>
            <div class="layui-input-block">
                <input type="datetime-local" name="start_time" autocomplete="off" placeholder="" class="layui-input" value="" lay-verify="required">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label"><span class="reddot">*</span>结束日期</label>
            <div class="layui-input-block">
                <input type="datetime-local" name="end_time" autocomplete="off" placeholder="" class="layui-input" value="" lay-verify="required">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label"><span class="reddot">*</span>总份额</label>
            <div class="layui-input-block">
                 <input type="number" name="total_number" autocomplete="off" placeholder="" class="layui-input" value="" lay-verify="required">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">限制份数</label>
            <div class="layui-input-block">
                 <input type="number" name="user_limit" autocomplete="off" placeholder="" class="layui-input" value="">
            </div>
        </div>
        <div class="layui-form-item">
            <div class="layui-input-block">
                <button class="layui-btn" lay-submit="" lay-filter="saveDual_submit">立即提交</button>
                <button type="reset" class="layui-btn layui-btn-primary">重置</button>
            </div>
        </div>
    </form>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
    <script>
    // var startTime =$('.start_timeinput').val();
    // var endTime =$('.end_timeinput').val();
    
    //     this.startTime = str_replace("T"," ",this.startTime);
    //     this.endTime = str_replace("T"," ",this.endTime);
        // 表单提交
       layui.use(['form','laydate'],function () {
            var form = layui.form
                ,$ = layui.jquery
                ,laydate = layui.laydate
                ,index = parent.layer.getFrameIndex(window.name);
            //监听提交
            form.on('submit(saveDual_submit)', function(data){
                var data = data.field;
                data.start_time=data.start_time.replace("T"," ")
                data.end_time=data.end_time.replace("T"," ")
                $.ajax({
                    url:'<?php echo e(url('/admin/dual/saveDual')); ?>'
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


        // layui.use(['form','laydate'],function () {
        //     var form = layui.form
        //         ,$ = layui.jquery
        //         ,laydate = layui.laydate
        //         ,index = parent.layer.getFrameIndex(window.name);
        //     //监听提交
        //     form.on('submit(demo1)', function(data){
        //         var data = data.field;
        //         $.ajax({
        //             url:'<?php echo e(url('admin/user/add')); ?>'
        //             ,type:'post'
        //             ,dataType:'json'
        //             ,data : data
        //             ,success:function(res){
        //                 if(res.type=='error'){
        //                     layer.msg(res.message);
        //                 }else{
        //                     parent.layer.close(index);
        //                     parent.window.location.reload();
        //                 }
        //             }
        //         });
        //         return false;
        //     });
        // });
        
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
                    const value = res.message.data[0]
                    $("#nowPrice").val(value.price)
                    
                    
                }
            });
        }
    </script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin._layoutNew', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>