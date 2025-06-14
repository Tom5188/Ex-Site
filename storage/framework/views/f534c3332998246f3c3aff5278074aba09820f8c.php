<?php $__env->startSection('page-head'); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('page-content'); ?>
<style>
    .layui-card{
        overflow: hidden;
    }
    .charts-block{
        width: 100%;
        height: 200px;
        display: flex;
        justify-content: center;
        flex-direction: column;
        align-items: center;
    }
    .charts-block .charts-wrap{
        width: 30px;
        height: 235px;
        overflow: hidden;
        display: flex;
        justify-content: center;
    }
    .charts-block .charts-box{
        width: 30px;
        height: 100%;
        background-color: #ffcece; 
        /*background-image: linear-gradient(#e66465, #aa9fbe);*/
        border-radius: 4px 4px 0 0;
    }
    .charts-block h3{
        font-size: 12px;
        color: #999;
        /*font-weight: bold;*/
        padding-bottom: 10px;
    }
    .charts-block .charts-num{
        padding: 10px 0;
        font-size: 14px;
        color: red;
    }
</style>
  <div class="layui-content-wrapper">
    <div class="layui-fluid">
      <div class="layui-row layui-col-space15">
        <div class="layui-col-md12">
          <div class="layui-row layui-col-space15">

            <div class="layui-col-md12">
                
              <div class="layui-card" style="margin-left: 10px">
                <div class="layui-card-header">信息统计</div>
                <div class="layui-card-body">

                  <div class="layui-carousel layadmin-carousel layadmin-backlog">
                    <div>
                      <ul class="layui-row layui-col-space10">

                        <li class="layui-col-xs4">
                          <a href="javascript:;" class="layadmin-backlog-body" style="text-align: center">
                            <h3>今日用户</h3>
                            <p><cite><?php echo e(@$data['today_user']); ?></cite></p>
                          </a>
                        </li>
                        <li class="layui-col-xs4">
                          <a href="javascript:;" class="layadmin-backlog-body" style="text-align: center">
                            <h3>总用户</h3>
                            <p><cite><?php echo e(@$data['all_user']); ?></cite></p>
                          </a>
                        </li>

                        <li class="layui-col-xs4">
                          <a href="javascript:;" class="layadmin-backlog-body" style="text-align: center">
                            <h3>今日提现数量</h3>
                            <p><cite><?php echo e(@$data['today_withdraw_count']); ?></cite></p>
                          </a>
                        </li>
                        <li class="layui-col-xs4">
                          <a href="javascript:;" class="layadmin-backlog-body" style="text-align: center">
                            <h3>今日提现金额</h3>
                            <p><cite><?php echo e(@$data['today_withdraw_amount']); ?></cite></p>
                          </a>
                        </li>

                        <li class="layui-col-xs4">
                          <a href="javascript:;" class="layadmin-backlog-body" style="text-align: center">
                            <h3>今日充值数量</h3>
                            <p><cite><?php echo e(@$data['today_charge_count']); ?></cite></p>
                          </a>
                        </li>
                        <li class="layui-col-xs4">
                          <a href="javascript:;" class="layadmin-backlog-body" style="text-align: center">
                            <h3>今日充值金额</h3>
                            <p><cite><?php echo e(@$data['today_charge_amount']); ?></cite></p>
                          </a>
                        </li>

                      </ul>
                    </div>
                  </div>
                </div>
              </div>
              
               <div class="layui-card" style="margin-left: 10px; margin-top: 30px;">
                <div class="layui-card-body">

                  <div class="layui-carousel layadmin-carousel layadmin-backlog">
                    <div>
                      <ul class="layui-row layui-col-space10">

                        <li class="layui-col-xs2">
                          <div class="charts-block">
                            <h3>今日用户</h3>
                            <div class="charts-wrap">
                                <div class="charts-box" style="height: <?php echo e(@$data['today_user'] / (@$data['all_user'] + @$data['today_user'] + @$data['today_withdraw_count'] + @$data['today_withdraw_amount'] + @$data['today_charge_count'] + @$data['today_charge_amount']) * 200); ?>px">
                            </div>
                            </div>
                            <div class="charts-num"><?php echo e(@$data['today_user']); ?></div>
                          </div>
                        </li>
                        
                        <li class="layui-col-xs2">
                          <div class="charts-block">
                            <h3>总用户</h3>
                            <div class="charts-wrap">
                                <div class="charts-box" style="height: <?php echo e(@$data['all_user'] / (@$data['all_user'] + @$data['today_user'] + @$data['today_withdraw_count'] + @$data['today_withdraw_amount'] + @$data['today_charge_count'] + @$data['today_charge_amount']) * 200); ?>px">
                            </div>
                            </div>
                            <div class="charts-num"><?php echo e(@$data['all_user']); ?></div>
                          </div>
                        </li>

                        <li class="layui-col-xs2">
                          <div class="charts-block">
                            <h3>今日提现数量</h3>
                            <div class="charts-wrap">
                                <div class="charts-box" style="height: <?php echo e(@$data['today_withdraw_count'] / (@$data['all_user'] + @$data['today_user'] + @$data['today_withdraw_count'] + @$data['today_withdraw_amount'] + @$data['today_charge_count'] + @$data['today_charge_amount']) * 200); ?>px"></div>
                            </div>
                            <div class="charts-num"><?php echo e(@$data['today_withdraw_count']); ?></div>
                          </div>
                        </li>
                        
                        <li class="layui-col-xs2">
                          <div class="charts-block">
                            <h3>今日提现金额</h3>
                            <div class="charts-wrap">
                                 <div class="charts-box" style="height: <?php echo e(@$data['today_withdraw_amount'] / (@$data['all_user'] + @$data['today_user'] + @$data['today_withdraw_count'] + @$data['today_withdraw_amount'] + @$data['today_charge_count'] + @$data['today_charge_amount']) * 200); ?>px"></div>
                            </div>
                            <div class="charts-num"><?php echo e(@$data['today_withdraw_amount']); ?></div>
                          </div>
                        </li>

                        <li class="layui-col-xs2">
                          <div class="charts-block">
                            <h3>今日充值数量</h3>
                            <div class="charts-wrap">
                                <div class="charts-box" style="height: <?php echo e(@$data['today_charge_count'] / (@$data['all_user'] + @$data['today_user'] + @$data['today_withdraw_count'] + @$data['today_withdraw_amount'] + @$data['today_charge_count'] + @$data['today_charge_amount']) * 200); ?>px"></div>
                            </div>
                            <div class="charts-num"><?php echo e(@$data['today_charge_count']); ?></div>
                          </div>
                        </li>
                        
                        <li class="layui-col-xs2">
                          <div class="charts-block">
                            <h3>今日充值金额</h3>
                            <div class="charts-wrap">
                                <div class="charts-box" style="height: <?php echo e(@$data['today_charge_amount'] / (@$data['all_user'] + @$data['today_user'] + @$data['today_withdraw_count'] + @$data['today_withdraw_amount'] + @$data['today_charge_count'] + @$data['today_charge_amount']) * 200); ?>px"></div>
                            </div>
                            <div class="charts-num"><?php echo e(@$data['today_charge_amount']); ?></div>
                          </div>
                        </li>

                      </ul>
                    </div>
                  </div>
                </div>
              </div>
              
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('manages.layadmin', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>