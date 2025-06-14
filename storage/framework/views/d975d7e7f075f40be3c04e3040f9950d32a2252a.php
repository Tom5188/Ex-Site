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
                            充值数量：<?php echo e($charge_info->amount); ?>

                        </td>
                        <td>
                            <?php if($charge_info->type == 1 ): ?>
                                充值方式：银行卡
                            <?php endif; ?>
                            <?php if($charge_info->type == 0 ): ?>
                                充值方式：在线充值
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            费率：<?php echo e($charge_info->give); ?>

                        </td>
                         <td>
                            申请时间：<?php echo e($charge_info -> created_at); ?>

                        </td>
                    </tr>
                    <?php if($charge_info->type == 0 ): ?>
                        <tr>
                            <td>
                                类型：<?php echo e($charge_info->sub_type); ?>

                            </td>
                             <td>
                                地址：<?php echo e($charge_info -> address); ?>

                            </td>
                        </tr>
                    <?php endif; ?>
                     <?php if($charge_info->type == 1 ): ?>
                         <tr>
                            <td>
                                账户名称：<?php echo e($charge_info->bank_user_name); ?>

                            </td>
                             <td>
                                IBAN：<?php echo e($charge_info -> iban); ?>

                            </td>
                        </tr>
                         <tr>
                            <td>
                                收款人国家/地区：<?php echo e($charge_info->beneficiary_country); ?>

                            </td>
                             <td>
                                银行编码（BIC/SWIFT)：<?php echo e($charge_info -> bank_code); ?>

                            </td>
                        </tr>
                         <tr>
                            <td>
                                银行名称：<?php echo e($charge_info->bank_name); ?>

                            </td>
                             <td>
                                银行地址：<?php echo e($charge_info -> bank_address); ?>

                            </td>
                        </tr>
                     <?php endif; ?>
                      <tr>
                        <td colspan="2">
                            备注：<?php echo e($charge_info->desc); ?>

                        </td>
                    </tr>
                </tbody>
            </table>
            </div>
        </div>
        
        
    </form>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
    

<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin._layoutNew', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>