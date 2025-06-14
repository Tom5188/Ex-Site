<?php $__env->startSection('page-head'); ?>
<style>
    .layui-form-label {
        width: 150px;
    }
    .layui-input-block {
        margin-left: 180px;
    }
    .layui-form-select dl { z-index: 9999; }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('page-content'); ?>
    <form class="layui-form" action="">
        
        <div class="layui-form-item">
            <label class="layui-form-label">收件人</label>
            <div class="layui-input-block">
                <select name="userIds" lay-verify="required" lay-filter="required" lay-search="">
                    <option value=""></option>
                    <option value="0">所有用户</option>
                   <?php $__currentLoopData = $user_list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e(@$v['id']); ?>" <?php echo e(($result->parent_id ?? 0) == $v['id'] ? 'selected' : ''); ?> ><?php echo e($v['id']); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
        </div>
        
        <div class="layui-form-item">
            <label class="layui-form-label">站内信标题</label>
            <div class="layui-input-block">
                <input type="text" name="title" autocomplete="off" lay-filter="required" placeholder="" class="layui-input" value="">
            </div>
        </div>
        
        <div class="layui-form-item">
            <label class="layui-form-label">站内信简介</label>
            <div class="layui-input-block">
                <textarea placeholder="请输入内容摘要" class="layui-textarea" name="abstract"></textarea>
            </div>
        </div>
        
        <div class="layui-form-item">
            <label class="layui-form-label">站内信内容</label>
            <div class="layui-input-block">
                <script id="news_content" name="content" type="text/plain" style="width:100%; height:300px;"></script>
            </div>
        </div>
        <div class="layui-form-item">
            <div class="layui-input-block">
                <button class="layui-btn" lay-submit="" lay-filter="demo1">保存并发送</button>
                <button lay-submit="" class="layui-btn layui-btn-primary" lay-filter="demo2">保存</button>
            </div>
        </div>
    </form>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script type="text/javascript" src="<?php echo e(URL('vendor/ueditor/1.4.3/ueditor.config.js')); ?>"></script>
<script type="text/javascript" src="<?php echo e(URL('vendor/ueditor/1.4.3/ueditor.all.js')); ?>"> </script>
<script type="text/javascript" src="<?php echo e(URL('vendor/ueditor/1.4.3/lang/zh-cn/zh-cn.js')); ?>"></script>
<script type="text/javascript" src="<?php echo e(URL("/admin/js/newsFormSubmit.js?v=").time()); ?>"></script>
    <script>
        layui.use(['form','laydate'],function () {
            var form = layui.form
                ,$ = layui.jquery
                ,laydate = layui.laydate
                ,index = parent.layer.getFrameIndex(window.name);
            //监听提交
            form.on('submit(demo1)', function(data){
                var data = data.field;
                $.ajax({
                    url:'<?php echo e(url('admin/message/message_add')); ?>'
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
            form.on('submit(demo2)', function(data){
                var data = data.field;
                $.ajax({
                    url:'<?php echo e(url('admin/message/message_post_add')); ?>'
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

<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin._layoutNew', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>