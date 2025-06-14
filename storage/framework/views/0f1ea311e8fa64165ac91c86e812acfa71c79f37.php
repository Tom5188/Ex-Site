<?php $__env->startSection('page-head'); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('page-content'); ?>
    <form class="layui-form" action="">
        <input type="hidden" name="id" value="<?php echo e($level->id); ?>">
        <div class="layui-form-item">
            <label class="layui-form-label">级别名称</label>
            <div class="layui-input-block">
                <input type="text" autocomplete="off" name="name" class="layui-input" value="<?php echo e($level->name); ?>" >
            </div>
        </div>

        <div class="layui-form-item layui-form-text">
            <label class="layui-form-label">级别徽章</label>
            <div class="layui-input-block">
                <button class="layui-btn" type="button" id="img_cover_btn">选择图片</button>
                <br>
                <img src="<?php echo e($level->pic ?? ''); ?>" id="img_cover" width="100" class="cover" style="display: <?php if(!empty($level->pic)): ?><?php echo e("block"); ?><?php else: ?><?php echo e("none"); ?><?php endif; ?>;max-width: 200px;height: auto;margin-top: 5px;">
                <input type="hidden" name="pic" id="cover" value="<?php echo e($level->pic ?? ''); ?>">
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">升级金额</label>
            <div class="layui-input-block">
                <input type="text" autocomplete="off" name="amount" class="layui-input" value="<?php echo e($level->amount); ?>" >
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">赠送比例</label>
            <div class="layui-input-block">
                <input type="text" autocomplete="off" name="give" class="layui-input" value="<?php echo e($level->give); ?>" >
                <div class="layui-form-mid layui-word-aux">百分比 该级别用户充值赠送对应比例的金额</div>
            </div>

        </div>


        <div class="layui-form-item">
            <div class="layui-input-block">
                <button class="layui-btn" lay-submit lay-filter="form">立即提交</button>
            </div>
        </div>
    </form>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
    layui.use(['element', 'form', 'layer','upload'], function () {
        var element = layui.element
            ,form = layui.form
            ,layer = layui.layer
            ,$ = layui.$
        var upload = layui.upload;

        //执行实例
        var uploadInst1 = upload.render({
            elem: '#img_cover_btn' //绑定元素
            ,url: '<?php echo e(URL("api/upload")); ?>?scene=admin' //上传接口
            ,done: function(res) {
                console.log(res);
                //上传完毕回调
                if (res.type == "ok"){
                    $("#cover").val(res.message)
                    $("#img_cover").show()
                    $("#img_cover").attr("src",res.message)
                } else{
                    alert(res.message)
                }
            }
            ,error: function(){
                //请求异常回调
            }
        });


        form.on('submit(form)', function (data) {
            $.ajax({
                url: ''
                ,type: 'POST'
                ,data: data.field
                ,success: function (res) {
                    layer.msg(res.message, {
                        time: 2000
                        ,end: function () {
                            if (res.type == 'ok') {
                                var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
                                parent.layer.close(index); //再执行关闭 
                                parent.layui.table.reload('userlist');       
                            }
                        }
                    });
                }
                ,error: function (res) {
                    layer.msg('网络错误');
                }
            });
            return false;
        });
    });
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin._layoutNew', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>