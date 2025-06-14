<?php $__env->startSection('page-head'); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('page-content'); ?>
    <form class="layui-form" action="">
        <div class="layui-form-item">
            <label class="layui-form-label">充值信息</label>
            <div class="layui-input-block">
               <table class="layui-table">
                <tbody>
                    <tr>
                        <td>
                            账户名：<?php echo e($charge_info->account_name); ?>

                        </td>
                        <td>
                            币种：<?php echo e($charge_info->currency_name); ?>

                        </td>
                    </tr>
                    <tr>
                        <td>
                            币种类型：基于<?php echo e($charge_info->currency_type); ?>

                        </td>
                        <td>
                            费率：<?php echo e($charge_info->rate); ?>

                        </td>
                    </tr>
                    <tr>
                        <td>
                            提币数量：<?php echo e($charge_info->number); ?>

                        </td>
                        <td>
                            实际提币数量：<?php echo e($wallet_out->real_number); ?>

                        </td>
                    </tr>
                    <tr>
                         <td>
                            提币地址：<?php echo e($wallet_out->address); ?>

                        </td>
                         <?php if($wallet_out->type == 1 ): ?>
                         <td>
                            人民币价格：<?php echo e($wallet_out->real_rmb); ?>元
                        </td>
                         <?php endif; ?>
                    </tr>
                    <?php if($wallet_out->type == 1 ): ?>
                     <tr>
                         <td>
                            真实姓名：<?php echo e($wallet_out->real_name); ?>

                        </td>
                        <td>
                            银行卡账号：<?php echo e($wallet_out->bank_account); ?>

                        </td>
                    </tr>
                    <tr>
                         <td>
                            开户银行：<?php echo e($wallet_out->bank_name); ?>

                        </td>
                        <td>
                            开户省市：<?php echo e($card_info->bank_dizhi); ?>

                        </td>
                    </tr>
                    <?php endif; ?>
                    <?php if($wallet_out->type == 2 ): ?>
                    <?php $__currentLoopData = $card_info_data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                     <tr>
                         <td colspan="2">
                            <?php echo e($key); ?>：<?php echo e($item); ?>

                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php endif; ?>
                    <?php if($wallet_out->status == 1 || $wallet_out->status == 2): ?>
                    <!--<tr>-->
                    <!--    <td colspan="2">-->
                    <!--        <label class="layui-form-label" style="text-align: left; padding-left: 0px;<?php echo e($use_chain_api == 0 ? 'color: #f00' : ''); ?>">交易哈希:</label>-->
                    <!--        <div class="layui-input-inline" style="width: 80%;">-->
                    <!--            <input class="layui-input" type="text" name="txid" <?php if($use_chain_api == 0): ?> lay-verify="required" <?php endif; ?> placeholder="手工提币请输入交易哈希" autocomplete="off" value="<?php echo e($wallet_out->txid ?? ''); ?>" <?php echo e($wallet_out->status == 2 ? 'readonly disabled' : ''); ?>>-->
                    <!--        </div>-->
                    <!--    </td>-->
                    <!--</tr>-->
                    <?php endif; ?>
                    
                    <tr>
                        <td>
                            申请时间：<?php echo e($wallet_out->create_time); ?>

                        </td>
                        <td>
                            当前状态：<?php if($wallet_out->status==1): ?> 提交申请
								     <?php elseif($wallet_out->status==2): ?> 提币成功
								     <?php elseif($wallet_out->status==3): ?> 提币失败
								    <?php else: ?>
                                    <?php endif; ?>
                        </td>
                    </tr>

                </tbody>
            </table>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">反馈信息</label>
            <div class="layui-input-block">
               <textarea name="notes" id="" cols="90" rows="5"><?php echo e($wallet_out->notes); ?></textarea>
            </div>
        </div>
        <?php if($wallet_out->status==1): ?>
        <!--<div class="layui-form-item">-->
        <!--    <label class="layui-form-label">安全验证码</label>-->
        <!--    <div class="layui-input-inline">-->
        <!--        <input type="text" name="verificationcode" placeholder="" autocomplete="off" class="layui-input">-->
        <!--    </div>-->
        <!--    <button type="button" class="layui-btn layui-btn-primary" id="get_code">获取验证码</button>-->
        <!--</div>-->
        <?php endif; ?>
        <input type="hidden" name="id" value="">
        
    </form>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
    

<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin._layoutNew', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>