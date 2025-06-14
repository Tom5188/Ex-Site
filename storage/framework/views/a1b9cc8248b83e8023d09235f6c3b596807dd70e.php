<?php $__env->startSection('page-head'); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('page-content'); ?>
    <form class="layui-form" action="">
        <div class="layui-form-item">
            <label class="layui-form-label">账户名称</label>
            <div class="layui-input-block">
                <input type="text" name="real_name" autocomplete="off" class="layui-input" value="<?php echo e($cashInfo->real_name ?? ''); ?>" >
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">银行名称</label>
            <div class="layui-input-block">
                <input type="text" name="bank_name" autocomplete="off" class="layui-input" value="<?php echo e($cashInfo->bank_name ?? ''); ?>" >
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">收款人国家/地区</label>
            <div class="layui-input-block">
                <input type="text" name="bank_dizhi" autocomplete="off" class="layui-input" value="<?php echo e($cashInfo->bank_dizhi ?? ''); ?>">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">Iban</label>
            <div class="layui-input-block">
                <input type="text" name="iban" autocomplete="off" class="layui-input" value="<?php echo e($cashInfo->iban ?? ''); ?>">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">银行编码（BIC/SWIFT)</label>
            <div class="layui-input-block">
                <input type="text" name="bank_code" autocomplete="off" class="layui-input" value="<?php echo e($cashInfo->bank_code ?? ''); ?>">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">银行地址</label>
            <div class="layui-input-block">
                <input type="text" name="bank_address" autocomplete="off" class="layui-input" value="<?php echo e($cashInfo->bank_address ?? ''); ?>">
            </div>
        </div>
        <div class="layui-form-item">
            <div class="layui-input-block">
                <button class="layui-btn" lay-submit lay-filter="form">立即提交</button>
                <button type="reset" class="layui-btn layui-btn-primary">重置</button>
                <button class="layui-btn layui-btn-danger" id="btn-delete">删除</button>
            </div>
        </div>
    </form>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
    layui.use(['element', 'form', 'layer'], function () {
        var element = layui.element
            ,form = layui.form
            ,layer = layui.layer
            ,$ = layui.$
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

        $('#btn-delete').click(function () {
            if (window.confirm('确认要删除吗？此操作不可恢复')) {
                try {
                    $.ajax({
                        url: '/admin/user/cash_info?id=<?php echo e($user->id); ?>'
                        ,type: 'DELETE'
                        ,success: function (res) {
                            layer.msg(res.message, {
                                time: 2000,
                                end: function () {
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
                    })
                } catch (error) {
                    layer.msg(error);
                }
            }
            return false;
        });
    });
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin._layoutNew', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>