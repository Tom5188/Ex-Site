<?php $__env->startSection('page-head'); ?>
    <style>
        li[hidden] {
            display: none;
        }
        .layui-form-label{
            width: 180px;
        }
        .layui-input-block{
            margin-left: 210px;
        }
    </style>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('page-content'); ?>

    <div class="larry-personal-body clearfix">
        <form class="layui-form col-lg-5">
            <div class="layui-tab">
                <ul class="layui-tab-title">
                    <li class="layui-this">通知设置</li>
                    <li>基础设置</li>
                    <li>杠杆设置</li>
                    <li hidden>奖励设置</li>
                    <li hidden>工作室</li>
                    <li hidden>通证规则</li>
                    <li>期权</li>
                    <li>联系我们</li>
                    <li>收款银行卡</li>
                </ul>
                <div class="layui-tab-content">
                    <!--通知设置开始-->
                    <div class="layui-tab-item layui-show">
                        <div id="setting">
                            <div>
                                <div class="layui-form-item ecology">
                                    <div class="layui-inline">
                                        <label class="layui-form-label">货币划转手续费(0-100)</label>
                                        <div class="layui-input-inline">
                                            <input class="layui-input" lay-verify="required" placeholder="" name="transfer_fee"
                                                   type="text" onkeyup=""
                                                   value="<?php if(isset($setting['transfer_fee'])): ?><?php echo e($setting['transfer_fee'] ?? ''); ?><?php endif; ?>">
                                        </div>
                                    </div>
                                    <div class="layui-inline">
                                        <label class="layui-form-label">锁仓挖矿取消订单手续费%</label>
                                        <div class="layui-input-inline">
                                            <input class="layui-input" lay-verify="1" placeholder="" name="cancel_deposit_fee"
                                                   type="text" onkeyup=""
                                                   value="<?php if(isset($setting['cancel_deposit_fee'])): ?><?php echo e($setting['cancel_deposit_fee'] ?? ''); ?><?php endif; ?>">
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <div id="email">
                            <div>
                                <div class="layui-form-item ecology">
                                    <div class="layui-inline">
                                        <label class="layui-form-label">邮箱账号</label>
                                        <div class="layui-input-inline">
                                            <input class="layui-input" lay-verify="1" placeholder="" name="phpMailer_username"
                                                   type="text" onkeyup=""
                                                   value="<?php if(isset($setting['phpMailer_username'])): ?><?php echo e($setting['phpMailer_username'] ?? ''); ?><?php endif; ?>">
                                        </div>
                                    </div>
                                    <div class="layui-inline">
                                        <label class="layui-form-label">Token密码</label>
                                        <div class="layui-input-inline">
                                            <input class="layui-input" lay-verify="required" placeholder="请输入最大值"
                                                   name="phpMailer_password" onkeyup="" type="password"
                                                   value="<?php if(isset($setting['phpMailer_password'])): ?><?php echo e($setting['phpMailer_password'] ?? ''); ?><?php endif; ?>">
                                        </div>
                                    </div>
                                    <div class="layui-inline">
                                        <label class="layui-form-label">端口</label>
                                        <div class="layui-input-inline">
                                            <input class="layui-input" lay-verify="required" placeholder="请输入比例"
                                                   name="phpMailer_port" type="text"
                                                   value="<?php if(isset($setting['phpMailer_port'])): ?><?php echo e($setting['phpMailer_port'] ?? ''); ?><?php endif; ?>"><span
                                                    style="position: absolute;right: 5px;top: 12px;"></span>
                                        </div>
                                    </div>
                                    <div class="layui-inline">
                                        <label class="layui-form-label">Host</label>
                                        <div class="layui-input-inline">
                                            <input class="layui-input" lay-verify="required" placeholder="请输入最小值"
                                                   name="phpMailer_host" type="text"
                                                   value="<?php if(isset($setting['phpMailer_host'])): ?><?php echo e($setting['phpMailer_host'] ?? ''); ?><?php endif; ?>">
                                        </div>
                                    </div>
                                    <div class="layui-inline">
                                        <label class="layui-form-label">发件人</label>
                                        <div class="layui-input-inline">
                                            <input class="layui-input" lay-verify="1" placeholder="" name="submail_from_name"
                                                   type="text" onkeyup=""
                                                   value="<?php if(isset($setting['submail_from_name'])): ?><?php echo e($setting['submail_from_name'] ?? ''); ?><?php endif; ?>">
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <div id="sms">
                            <div>
                                <div class="layui-form-item ecology"  >
                                    <div class="layui-inline">
                                        <label class="layui-form-label">短信宝</label>
                                        <div class="layui-input-inline">
                                            <input class="layui-input"  placeholder="用户名"
                                                   name="smsBao_username" type="text"
                                                   value="<?php echo e($setting['smsBao_username'] ?? ''); ?>"><span
                                                    style="position: absolute;right: 5px;top: 12px;"></span>
                                        </div>
                                    </div>
                                    <div class="layui-inline">
                                        <label class="layui-form-label">密码</label>
                                        <div class="layui-input-inline">
                                            <input class="layui-input" type="password"  name="password"
                                                   value="<?php echo e($setting['password']  ?? ''); ?>" placeholder="" >
                                        </div>
                                    </div>
                                    <div class="layui-inline">
                                        <label class="layui-form-label">短信签名</label>
                                        <div class="layui-input-inline">
                                            <input class="layui-input"  name="sms_signature"
                                                   value="<?php echo e($setting['sms_signature'] ?? ''); ?>" placeholder="">
                                        </div>
                                    </div>
                                </div>

                                <div class="layui-form-item ecology" style="display:none;">
                                    <div class="layui-inline">
                                        <label class="layui-form-label">赛邮（国内appid）</label>
                                        <div class="layui-input-inline">
                                            <input class="layui-input" lay-verify="required" placeholder=""
                                                   name="submail_appid" type="text"
                                                   value="<?php echo e($setting['submail_appid'] ?? ''); ?>"><span
                                                    style="position: absolute;right: 5px;top: 12px;"></span>
                                        </div>
                                    </div>
                                    <div class="layui-inline">
                                        <label class="layui-form-label">赛邮（国内appkey）</label>
                                        <div class="layui-input-inline">
                                            <input class="layui-input" type="text" lay-verify="required" name="submail_appkey"
                                                   value="<?php echo e($setting['submail_appkey']  ?? ''); ?>" placeholder="" >
                                        </div>
                                    </div>
                                    <div class="layui-inline">
                                        <label class="layui-form-label">赛邮（国内模板）</label>
                                        <div class="layui-input-inline">
                                            <input class="layui-input" type="text" lay-verify="required" name="submail_template"
                                                   value="<?php echo e($setting['submail_template']  ?? ''); ?>" placeholder="" >
                                        </div>
                                    </div>
                                    <div class="layui-inline">
                                        <label class="layui-form-label">赛邮（国外appid）</label>
                                        <div class="layui-input-inline">
                                            <input class="layui-input"  name="submail_overseas_appid"
                                                   value="<?php echo e($setting['submail_overseas_appid'] ?? ''); ?>" placeholder="">
                                        </div>
                                    </div>
                                    <div class="layui-inline">
                                        <label class="layui-form-label">赛邮（国外appkey）</label>
                                        <div class="layui-input-inline">
                                            <input class="layui-input"  name="submail_overseas_appkey"
                                                   value="<?php echo e($setting['submail_overseas_appkey'] ?? ''); ?>" placeholder="">
                                        </div>
                                    </div>
                                    <div class="layui-inline">
                                        <label class="layui-form-label">赛邮（国外模板）</label>
                                        <div class="layui-input-inline">
                                            <input class="layui-input"  name="submail_overseas_template"
                                                   value="<?php echo e($setting['submail_overseas_template'] ?? ''); ?>" placeholder="">
                                        </div>
                                    </div>
                                </div>


                            </div>
                        </div>
                        <div class="layui-form-item">
                            <div class="layui-input-block">
                                <button class="layui-btn" lay-submit lay-filter="website_submit">立即提交</button>
                                <button type="reset" class="layui-btn layui-btn-primary">重置</button>
                            </div>
                        </div>
                    </div>
                    <!--基础设置开始-->
                    <div class="layui-tab-item">
                        <div class="layui-form-item">
                            <label class="layui-form-label">网站名称</label>
                            <div class="layui-input-block">
                                <input type="text" name="site_name" autocomplete="off" class="layui-input"
                                       value="<?php if(isset($setting['site_name'])): ?><?php echo e($setting['site_name'] ?? ''); ?><?php endif; ?>">
                            </div>
                        </div>

                        <div class="layui-form-item">
                            <label class="layui-form-label">网站Logo</label>
                            <div class="layui-input-block upload-box">
                                <button class="layui-btn upload_test" type="button">选择图片</button>
                                <button class="layui-btn layui-btn-normal clear_upload" type="button" style="<?php if(empty($setting['site_logo'])): ?><?php echo e('display:none'); ?><?php endif; ?>">清除图片</button>
                                <br>
                                <img src="<?php if(!empty($setting['site_logo'])): ?><?php echo e($setting['site_logo']); ?><?php endif; ?>" class="thumbnail" style="display: <?php if(!empty($setting['site_logo'])): ?><?php echo e("block"); ?><?php else: ?><?php echo e("none"); ?><?php endif; ?>;max-width: 200px;height: auto;margin-top: 5px;">
                                <input type="hidden" name="site_logo" class="thumb-input" value="<?php if(!empty($setting['site_logo'])): ?><?php echo e($setting['site_logo']); ?><?php endif; ?>">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">网站Logo(PC)</label>
                            <div class="layui-input-block upload-box">
                                <button class="layui-btn upload_test" type="button">选择图片</button>
                                <button class="layui-btn layui-btn-normal clear_upload" type="button" style="<?php if(empty($setting['site_pc_logo'])): ?><?php echo e('display:none'); ?><?php endif; ?>">清除图片</button>
                                <br>
                                <img src="<?php if(!empty($setting['site_pc_logo'])): ?><?php echo e($setting['site_pc_logo']); ?><?php endif; ?>" class="thumbnail" style="display: <?php if(!empty($setting['site_pc_logo'])): ?><?php echo e("block"); ?><?php else: ?><?php echo e("none"); ?><?php endif; ?>;max-width: 200px;height: auto;margin-top: 5px;">
                                <input type="hidden" name="site_pc_logo" class="thumb-input" value="<?php if(!empty($setting['site_pc_logo'])): ?><?php echo e($setting['site_pc_logo']); ?><?php endif; ?>">
                            </div>
                        </div>

                        <div class="layui-form-item">
                            <label class="layui-form-label">用户默认头像</label>
                            <div class="layui-input-block upload-box">
                                <button class="layui-btn upload_test" type="button">选择图片</button>
                                <button class="layui-btn layui-btn-normal clear_upload" type="button" style="<?php if(empty($setting['user_default_avatar'])): ?><?php echo e('display:none'); ?><?php endif; ?>">清除图片</button>
                                <br>
                                <img src="<?php if(!empty($setting['user_default_avatar'])): ?><?php echo e($setting['user_default_avatar']); ?><?php endif; ?>" class="thumbnail" style="display: <?php if(!empty($setting['site_logo'])): ?><?php echo e("block"); ?><?php else: ?><?php echo e("none"); ?><?php endif; ?>;max-width: 200px;height: auto;margin-top: 5px;">
                                <input class="thumb-input" type="hidden" name="user_default_avatar" value="<?php if(!empty($setting['user_default_avatar'])): ?><?php echo e($setting['user_default_avatar']); ?><?php endif; ?>">
                            </div>
                        </div>

                        <div class="layui-form-item">
                            <label class="layui-form-label">APK下载图标</label>
                            <div class="layui-input-block upload-box">
                                <button class="layui-btn upload_test" type="button">选择图片</button>
                                <button class="layui-btn layui-btn-normal clear_upload" type="button" style="<?php if(empty($setting['down_logo'])): ?><?php echo e('display:none'); ?><?php endif; ?>">清除图片</button>
                                <br>
                                <img src="<?php if(!empty($setting['down_logo'])): ?><?php echo e($setting['down_logo']); ?><?php endif; ?>" class="thumbnail" style="display: <?php if(!empty($setting['down_logo'])): ?><?php echo e("block"); ?><?php else: ?><?php echo e("none"); ?><?php endif; ?>;max-width: 200px;height: auto;margin-top: 5px;">
                                <input type="hidden" name="down_logo" class="thumb-input" value="<?php if(!empty($setting['down_logo'])): ?><?php echo e($setting['down_logo']); ?><?php endif; ?>">
                            </div>
                        </div>

                        <div class="layui-form-item">
                            <label class="layui-form-label">APK下载地址</label>
                            <div class="layui-input-block">
                                <input type="text" name="apk_download_url" autocomplete="off" class="layui-input"
                                       value="<?php if(isset($setting['apk_download_url'])): ?><?php echo e($setting['apk_download_url'] ?? ''); ?><?php endif; ?>">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">IOS下载地址</label>
                            <div class="layui-input-block">
                                <input type="text" name="ios_apk_download_url" autocomplete="off" class="layui-input"
                                       value="<?php if(isset($setting['ios_apk_download_url'])): ?><?php echo e($setting['ios_apk_download_url'] ?? ''); ?><?php endif; ?>">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">图片服务器地址</label>
                            <div class="layui-input-block">
                                <input type="text" name="image_server_url" autocomplete="off" class="layui-input"
                                       value="<?php if(isset($setting['image_server_url'])): ?><?php echo e($setting['image_server_url'] ?? ''); ?><?php endif; ?>">
                            </div>
                        </div>
                         <div class="layui-form-item">
                            <label class="layui-form-label">代理端邀请域名</label>
                            <div class="layui-input-block">
                                <input type="text" name="moblie_h5_url" autocomplete="off" class="layui-input"
                                       value="<?php if(isset($setting['moblie_h5_url'])): ?><?php echo e($setting['moblie_h5_url'] ?? ''); ?><?php endif; ?>">
                            </div>
                        </div>
                        <!--<div class="layui-form-item">-->
                        <!--    <label class="layui-form-label">在线客服地址</label>-->
                        <!--    <div class="layui-input-block">-->
                        <!--        <input type="text" name="support_url" autocomplete="off" class="layui-input"-->
                        <!--               value="<?php if(isset($setting['support_url'])): ?><?php echo e($setting['support_url'] ?? ''); ?><?php endif; ?>">-->
                        <!--    </div>-->
                        <!--</div>-->
                        <div class="layui-form-item">
                            <label class="layui-form-label">浏览器跳转</label>
                            <div class="layui-input-block">
                                <input type="text" name="open_url" autocomplete="off" class="layui-input"
                                       value="<?php if(isset($setting['open_url'])): ?><?php echo e($setting['open_url'] ?? ''); ?><?php endif; ?>">
                            </div>
                        </div>

                        <div class="layui-form-item">
                            <label class="layui-form-label">邀请码是否必填</label>
                            <div class="layui-input-block">
                                <div class="layui-input-inline">
                                    <input type="radio" name="sharar_radio" value="1" title="是" <?php if(isset($setting['sharar_radio'])): ?> <?php echo e($setting['sharar_radio'] == 1 ? 'checked' : ''); ?> <?php endif; ?> >
                                    <input type="radio" name="sharar_radio" value="0" title="否" <?php if(isset($setting['sharar_radio'])): ?> <?php echo e($setting['sharar_radio'] == 0 ? 'checked' : ''); ?> <?php else: ?> checked <?php endif; ?> >
                                </div>
                                <div class="layui-form-mid layui-word-aux">注册时验证码是否必填</div>
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">提款验证是否开启</label>
                            <div class="layui-input-block">
                                <div class="layui-input-inline">
                                    <input type="radio" name="tk_radio" value="1" title="是" <?php if(isset($setting['tk_radio'])): ?> <?php echo e($setting['tk_radio'] == 1 ? 'checked' : ''); ?> <?php endif; ?> >
                                    <input type="radio" name="tk_radio" value="0" title="否" <?php if(isset($setting['tk_radio'])): ?> <?php echo e($setting['tk_radio'] == 0 ? 'checked' : ''); ?> <?php else: ?> checked <?php endif; ?> >
                                </div>
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">自动验证码是否开启</label>
                            <div class="layui-input-block">
                                <div class="layui-input-inline">
                                    <input type="radio" name="yzm_radio" value="1" title="是" <?php if(isset($setting['yzm_radio'])): ?> <?php echo e($setting['yzm_radio'] == 1 ? 'checked' : ''); ?> <?php endif; ?> >
                                    <input type="radio" name="yzm_radio" value="0" title="否" <?php if(isset($setting['yzm_radio'])): ?> <?php echo e($setting['yzm_radio'] == 0 ? 'checked' : ''); ?> <?php else: ?> checked <?php endif; ?> >
                                </div>
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">是否开启手机注册、登陆</label>
                            <div class="layui-input-block">
                                <div class="layui-input-inline">
                                    <input type="radio" name="mobile_register" value="1" title="是" <?php if(isset($setting['mobile_register'])): ?> <?php echo e($setting['mobile_register'] == 1 ? 'checked' : ''); ?> <?php endif; ?> >
                                    <input type="radio" name="mobile_register" value="0" title="否" <?php if(isset($setting['mobile_register'])): ?> <?php echo e($setting['mobile_register'] == 0 ? 'checked' : ''); ?> <?php else: ?> checked <?php endif; ?> >
                                </div>
                            </div>
                        </div>

                        <div class="layui-form-item">
                            <label class="layui-form-label">信用分提现限制</label>
                            <div class="layui-input-block">
                                <input type="number" name="score_withdraw_limit" autocomplete="off" class="layui-input"
                                       value="<?php if(isset($setting['score_withdraw_limit'])): ?><?php echo e($setting['score_withdraw_limit'] ?? ''); ?><?php endif; ?>">
                                <div class="layui-form-mid layui-word-aux">低于该积分 用户将限制提现</div>
                            </div>
                        </div>

                        <!--<div class="layui-form-item">-->
                        <!--    <label class="layui-form-label">在线客服</label>-->
                        <!--    <div class="layui-input-block">-->
                        <!--        <input type="text" name="kf" autocomplete="off" class="layui-input"-->
                        <!--               value="<?php if(isset($setting['kf'])): ?><?php echo e($setting['kf'] ?? ''); ?><?php endif; ?>">-->
                        <!--    </div>-->
                        <!--</div>-->



                        <!--<div class="layui-form-item">-->
                        <!--    <label class="layui-form-label">充值银行卡姓名</label>-->
                        <!--    <div class="layui-input-block">-->
                        <!--        <input type="text" name="recharge_bank_name" autocomplete="off" class="layui-input"-->
                        <!--               value="<?php if(isset($setting['recharge_bank_name'])): ?><?php echo e($setting['recharge_bank_name'] ?? ''); ?><?php endif; ?>">-->
                        <!--    </div>-->
                        <!--</div>-->
                        <!--<div class="layui-form-item">-->
                        <!--    <label class="layui-form-label">充值银行卡账号</label>-->
                        <!--    <div class="layui-input-block">-->
                        <!--        <input type="text" name="recharge_bank_account" autocomplete="off" class="layui-input"-->
                        <!--               value="<?php if(isset($setting['recharge_bank_account'])): ?><?php echo e($setting['recharge_bank_account'] ?? ''); ?><?php endif; ?>">-->
                        <!--    </div>-->
                        <!--</div>-->
                        <!--<div class="layui-form-item">-->
                        <!--    <label class="layui-form-label">充值银行卡开户银行</label>-->
                        <!--    <div class="layui-input-block">-->
                        <!--        <input type="text" name="recharge_open_bank" autocomplete="off" class="layui-input"-->
                        <!--               value="<?php if(isset($setting['recharge_open_bank'])): ?><?php echo e($setting['recharge_open_bank'] ?? ''); ?><?php endif; ?>">-->
                        <!--    </div>-->
                        <!--</div>-->
                        <div class="layui-form-item">
                            <label class="layui-form-label">币币交易手续费系数</label>
                            <div class="layui-input-block">
                                <input type="text" name="COIN_TRADE_FEE" autocomplete="off" class="layui-input"
                                       value="<?php if(isset($setting['COIN_TRADE_FEE'])): ?><?php echo e($setting['COIN_TRADE_FEE']); ?><?php endif; ?>">
                                <span style="position: absolute;right: 5px;top: 12px;">%</span>
                            </div>
                        </div>
                        <!--<div class="layui-form-item">-->
                        <!--    <label class="layui-form-label">法币交易一天最大取消次数</label>-->
                        <!--    <div class="layui-input-block">-->
                        <!--        <input type="text" name="userLegalDealCancel" autocomplete="off" class="layui-input"-->
                        <!--               value="<?php if(isset($setting['userLegalDealCancel'])): ?><?php echo e($setting['userLegalDealCancel'] ?? ''); ?><?php endif; ?>">-->
                        <!--    </div>-->
                        <!--</div>-->
                        <!--<div class="layui-form-item">-->
                        <!--    <label class="layui-form-label">法币交易待付款时间分钟数</label>-->
                        <!--    <div class="layui-input-block">-->
                        <!--        <input type="text" name="userLegalDealCancel_time" autocomplete="off" class="layui-input"-->
                        <!--               value="<?php if(isset($setting['userLegalDealCancel_time'])): ?><?php echo e($setting['userLegalDealCancel_time'] ?? ''); ?><?php endif; ?>">-->
                        <!--    </div>-->
                        <!--</div>-->

                        <div class="layui-form-item">
                            <label class="layui-form-label">版本号</label>
                            <div class="layui-input-block">
                                <input type="text" name="version" autocomplete="off" class="layui-input"
                                       value="<?php if(isset($setting['version'])): ?><?php echo e($setting['version'] ?? ''); ?><?php endif; ?>">
                            </div>
                        </div>
                        <!--<div class="layui-form-item">-->
                        <!--    <label class="layui-form-label">USDT兑换RMB汇率</label>-->
                        <!--    <div class="layui-input-block">-->
                        <!--        <input type="text" name="USDTRate" autocomplete="off" class="layui-input"-->
                        <!--            value="<?php if(isset($setting['USDTRate'])): ?><?php echo e($setting['USDTRate']); ?><?php endif; ?>">-->
                        <!--    </div>-->
                        <!--</div>-->
                        <!--<div class="layui-form-item">-->
                        <!--    <label class="layui-form-label">AUTU 所需解冻金额(U)</label>-->
                        <!--    <div class="layui-input-block">-->
                        <!--        <input type="text" name="AUTU_USDT_NUM" autocomplete="off" class="layui-input"-->
                        <!--            value="<?php if(isset($setting['AUTU_USDT_NUM'])): ?><?php echo e($setting['AUTU_USDT_NUM']); ?><?php endif; ?>">-->
                        <!--    </div>-->
                        <!--</div>-->
                        <!--<div class="layui-form-item">-->
                        <!--    <label class="layui-form-label">AUTU 每次解冻金额</label>-->
                        <!--    <div class="layui-input-block">-->
                        <!--        <input type="text" name="AUTU_UNLOCK_NUM" autocomplete="off" class="layui-input"-->
                        <!--            value="<?php if(isset($setting['AUTU_UNLOCK_NUM'])): ?><?php echo e($setting['AUTU_UNLOCK_NUM']); ?><?php endif; ?>">-->
                        <!--    </div>-->
                        <!--</div>-->
                        <!--<div class="layui-form-item">-->
                        <!--    <label class="layui-form-label">AUTU 解冻时长(天)</label>-->
                        <!--    <div class="layui-input-block">-->
                        <!--        <input type="text" name="AUTU_UNLOCK_DAY" autocomplete="off" class="layui-input"-->
                        <!--            value="<?php if(isset($setting['AUTU_UNLOCK_DAY'])): ?><?php echo e($setting['AUTU_UNLOCK_DAY']); ?><?php endif; ?>">-->
                        <!--    </div>-->
                        <!--</div>-->
                        <!--<div class="layui-form-item">-->
                        <!--    <label class="layui-form-label">是否开启充提币功能</label>-->
                        <!--    <div class="layui-input-block">-->
                        <!--        <div class="layui-input-inline">-->
                        <!--            <input type="radio" name="is_open_CTbi" value="1" title="打开" <?php if(isset($setting['is_open_CTbi'])): ?> <?php echo e($setting['is_open_CTbi'] == 1 ? 'checked' : ''); ?> <?php endif; ?> >-->
                        <!--            <input type="radio" name="is_open_CTbi" value="0" title="关闭" <?php if(isset($setting['is_open_CTbi'])): ?> <?php echo e($setting['is_open_CTbi'] == 0 ? 'checked' : ''); ?> <?php else: ?> checked <?php endif; ?> >-->
                        <!--        </div>-->
                        <!--    </div>-->
                        <!--</div>-->
                        <!--<div class="layui-form-item">-->
                        <!--    <label class="layui-form-label">提币时使用链上接口</label>-->
                        <!--    <div class="layui-input-block">-->
                        <!--        <div class="layui-input-inline">-->
                        <!--            <input type="radio" name="use_chain_api" value="1" title="打开" <?php if(isset($setting['use_chain_api'])): ?> <?php echo e($setting['use_chain_api'] == 1 ? 'checked' : ''); ?> <?php endif; ?> >-->
                        <!--            <input type="radio" name="use_chain_api" value="0" title="关闭" <?php if(isset($setting['use_chain_api'])): ?> <?php echo e($setting['use_chain_api'] == 0 ? 'checked' : ''); ?> <?php else: ?> checked <?php endif; ?> >-->
                        <!--        </div>-->
                        <!--    </div>-->
                        <!--</div>-->
                        <!--<div class="layui-form-item">-->
                        <!--    <label class="layui-form-label">持险生币比例</label>-->
                        <!--    <div class="layui-input-block" style="width: 227px">-->
                        <!--        <input type="text" name="insurance_money_rate " autocomplete="off" class="layui-input"-->
                        <!--            value="<?php if(isset($setting['insurance_money_rate'])): ?><?php echo e($setting['insurance_money_rate']); ?><?php endif; ?>"><span style="position: absolute;right: 5px;top: 12px;">%</span>-->
                        <!--    </div>-->
                        <!--</div>-->
                        <div class="layui-form-item">
                            <div class="layui-input-block">
                                <button class="layui-btn" lay-submit lay-filter="website_submit">立即提交</button>
                                <button type="reset" class="layui-btn layui-btn-primary">重置</button>
                            </div>
                        </div>
                    </div>
                    <!--杠杆设置开始-->
                    <div class="layui-tab-item">
                        <div class="layui-form-item">
                            <label class="layui-form-label">交易手数倍数</label>
                            <div class="layui-input-block">
                                <div class="layui-input-inline">
                                    <button class="layui-btn layui-btn-sm" type="button" id="handle_multi_set">点击设置</button>
                                </div>
                                <div class="layui-form-mid layui-word-aux">设置交易的手数和倍数</div>
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">止盈止亏功能</label>
                            <div class="layui-input-block">
                                <div class="layui-input-inline">
                                    <input type="radio" name="user_set_stopprice" value="1" title="打开" <?php if(isset($setting['user_set_stopprice'])): ?> <?php echo e($setting['user_set_stopprice'] == 1 ? 'checked' : ''); ?> <?php endif; ?> >
                                    <input type="radio" name="user_set_stopprice" value="0" title="关闭" <?php if(isset($setting['user_set_stopprice'])): ?> <?php echo e($setting['user_set_stopprice'] == 0 ? 'checked' : ''); ?> <?php else: ?> checked <?php endif; ?> >
                                </div>
                                <div class="layui-form-mid layui-word-aux">打开用户将可以针对交易设置止盈止亏价</div>
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">杠杆交易委托功能</label>
                            <div class="layui-input-block">
                                <div class="layui-input-inline">
                                    <input type="radio" name="open_lever_entrust" value="1" title="打开" <?php if(isset($setting['open_lever_entrust'])): ?> <?php echo e($setting['open_lever_entrust'] == 1 ? 'checked' : ''); ?> <?php endif; ?> >
                                    <input type="radio" name="open_lever_entrust" value="0" title="关闭" <?php if(isset($setting['open_lever_entrust'])): ?> <?php echo e($setting['open_lever_entrust'] == 0 ? 'checked' : ''); ?> <?php else: ?> checked <?php endif; ?> >
                                </div>
                                <div class="layui-form-mid layui-word-aux">打开后前台可以进行杠杆交易委托,即限价交易</div>
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">爆仓风险率指标</label>
                            <div class="layui-input-block">
                                <div class="layui-input-inline">
                                    <input type="text" name="lever_burst_hazard_rate" class="layui-input" value="<?php echo e($setting['lever_burst_hazard_rate'] ?? 0); ?>" placeholder="杠杆交易风险率达到或低于设定值时爆仓">
                                </div>
                                <div class="layui-form-mid layui-word-aux">%</div>
                                <div class="layui-form-mid layui-word-aux">用户的风险率达到或低于该值时触发爆仓</div>
                            </div>
                        </div>
                        <div class="layui-form-item" hidden>
                            <label class="layui-form-label">赠送虚拟账户</label>
                            <div class="layui-input-block">
                                <input type="text" name="give_virtual_account" autocomplete="off" class="layui-input"
                                       value="<?php if(isset($setting['give_virtual_account'])): ?><?php echo e($setting['give_virtual_account']); ?><?php endif; ?>">
                            </div>
                        </div>
                        <div class="layui-form-item" hidden>
                            <label class="layui-form-label">头寸</label>
                            <div class="layui-input-block">
                                <input type="text" name="lever_position" autocomplete="off" class="layui-input" value="<?php if(isset($setting['lever_position'])): ?><?php echo e($setting['lever_position']); ?><?php endif; ?>">
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <div class="layui-input-block">
                                <button class="layui-btn" lay-submit lay-filter="website_submit">立即提交</button>
                                <button type="reset" class="layui-btn layui-btn-primary">重置</button>
                            </div>
                        </div>
                    </div>
                    <!--通证设置开始-->
                    <div class="layui-tab-item">
                        <div class="layui-form-item">
                            <label class="layui-form-label">历史盈亏释放比例</label>
                            <div class="layui-input-block">
                                <div class="layui-input-inline">
                                    <input type="text" name="profit_loss_release" class="layui-input" value="<?php echo e($setting['profit_loss_release'] ?? 0); ?>" placeholder="通证兑换USDT比例设置">
                                </div>
                                <div class="layui-form-mid layui-word-aux">千分比例</div>
                                <div class="layui-form-mid layui-word-aux">历史盈亏释放比例</div>
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">通证兑换USDT比例</label>
                            <div class="layui-input-block">
                                <div class="layui-input-inline">
                                    <input type="text" name="candy_tousdt" class="layui-input" value="<?php echo e($setting['candy_tousdt'] ?? 0); ?>" placeholder="通证兑换USDT比例设置">
                                </div>
                                <div class="layui-form-mid layui-word-aux">%</div>
                                <div class="layui-form-mid layui-word-aux">1通证能兑换多少USDT</div>
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">手续费结算比例</label>
                            <div class="layui-input-block">
                                <?php echo $__env->make('admin.setting.lever_tradefee_settle', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
                            </div>
                        </div>

                        <div class="layui-form-item" hidden>
                            <label class="layui-form-label">手续费结算笔数要求</label>
                            <div class="layui-input-block">
                                <div class="layui-input-inline">
                                    <input type="text" name="lever_fee_need_trades" class="layui-input" value="<?php echo e($setting['lever_fee_need_trades'] ?? ''); ?>" placeholder="各级手续费结算比例">
                                </div>
                                <div class="layui-form-mid layui-word-aux"></div>
                                <div class="layui-form-mid layui-word-aux">拿手续费的用户自身最低交易笔数,符合条件才能拿到奖励,用英文逗号分隔,例如:1,2,3</div>
                            </div>
                        </div>
                        <div class="layui-form-item" hidden>
                            <label class="layui-form-label">手续费结算各级比例</label>
                            <div class="layui-input-block">
                                <div class="layui-input-inline">
                                    <input type="text" name="lever_fee_reward_ratio" class="layui-input" value="<?php echo e($setting['lever_fee_reward_ratio'] ?? ''); ?>" placeholder="各级手续费结算比例">
                                </div>
                                <div class="layui-form-mid layui-word-aux">%</div>
                                <div class="layui-form-mid layui-word-aux">每级比例用英文逗号分隔,例如:8,2,5</div>
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <div class="layui-input-block">
                                <button class="layui-btn" lay-submit lay-filter="website_submit">立即提交</button>
                                <button type="reset" class="layui-btn layui-btn-primary">重置</button>
                            </div>
                        </div>
                    </div>

                    <!--工作室设置开始-->
                    <div class="layui-tab-item">
                        <div class="layui-form-item">
                            <label class="layui-form-label">升级条件:</label>
                            <div class="layui-inline">
                                <label class="layui-form-label" style="padding: 9px 4px; width: unset;">直推实名</label>
                                <div class="layui-input-inline" style="width: 80px">
                                    <input class="layui-input" name="upgrade_atelier_must_has_son" type="text" value="<?php echo e($setting['upgrade_atelier_must_has_son'] ?? 0); ?>" lay-verify placeholder="" >
                                </div>
                                <div class="layui-form-mid">人, </div>
                                <label class="layui-form-label" style="padding: 9px 4px; width: unset;">团队实名</label>
                                <div class="layui-input-inline" style="width: 80px">
                                    <input class="layui-input" name="upgrade_atelier_must_team_son" type="text" value="<?php echo e($setting['upgrade_atelier_must_team_son'] ?? 0); ?>" lay-verify placeholder="" >
                                </div>
                                <div class="layui-form-mid">人, </div>
                                <label class="layui-form-label" style="padding: 9px 4px; width: unset;">团队充值</label>
                                <div class="layui-input-inline" style="width: 80px">
                                    <input class="layui-input" name="upgrade_atelier_must_team_recharge" type="text" value="<?php echo e($setting['upgrade_atelier_must_team_recharge'] ?? 0); ?>" lay-verify placeholder="">
                                </div>
                                <div class="layui-form-mid">USDT</div>
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">奖励条件:</label>
                            <div class="layui-inline">
                                <label class="layui-form-label" style="padding: 9px 4px; width: unset;">每日已开仓交易不低于</label>
                                <div class="layui-input-inline" style="width: 80px">
                                    <input class="layui-input" name="atelier_reward_day_must_trade" type="text" value="<?php echo e($setting['atelier_reward_day_must_trade'] ?? 0); ?>" lay-verify placeholder="" >
                                </div>
                                <div class="layui-form-mid">笔</div>
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">奖励规则:</label>
                            <div class="layui-inline">
                                <label class="layui-form-label" style="padding: 9px 4px; width: unset;">奖励伞下用户交易手续费</label>
                                <div class="layui-input-inline" style="width: 80px">
                                    <input class="layui-input" name="atelier_reward_ratio" type="text" value="<?php echo e($setting['atelier_reward_ratio'] ?? 0); ?>" lay-verify placeholder="" >
                                </div>
                                <div class="layui-form-mid">%的通证, </div>
                                <label class="layui-form-label" style="padding: 9px 4px; width: unset;">日封顶</label>
                                <div class="layui-input-inline" style="width: 80px">
                                    <input class="layui-input" name="atelier_reward_day_limit" type="text" value="<?php echo e($setting['atelier_reward_day_limit'] ?? 0); ?>" lay-verify placeholder="" >
                                </div>
                                <div class="layui-form-mid"></div>
                            </div>


                        </div>
                        <div class="layui-form-item">
                            <div class="layui-input-block">
                                <button class="layui-btn" lay-submit lay-filter="website_submit">立即提交</button>
                                <button type="reset" class="layui-btn layui-btn-primary">重置</button>
                            </div>
                        </div>
                    </div>
                    <!--通证奖励规则设置开始-->
                    <div class="layui-tab-item">
                        <div class="layui-form-item">
                            <label class="layui-form-label">是否开启通证转账功能</label>
                            <div class="layui-input-block">
                                <div class="layui-input-inline">
                                    <input type="radio" name="is_open_transfer_candy" value="1" title="打开" <?php if(isset($setting['is_open_transfer_candy'])): ?> <?php echo e($setting['is_open_transfer_candy'] == 1 ? 'checked' : ''); ?> <?php endif; ?> >
                                    <input type="radio" name="is_open_transfer_candy" value="0" title="关闭" <?php if(isset($setting['is_open_transfer_candy'])): ?> <?php echo e($setting['is_open_transfer_candy'] == 0 ? 'checked' : ''); ?> <?php else: ?> checked <?php endif; ?> >
                                </div>
                            </div>
                        </div>

                        <div>
                            <div>
                                <div class="layui-form-item ecology">
                                    <div class="layui-inline">
                                        <label class="layui-form-label">通证转账收取卖家手续费百分比</label>
                                        <div class="layui-input-inline">
                                            <input class="layui-input" lay-verify="1" placeholder="" name="transfer_candy_rate" type="text" onkeyup="" value="<?php if(isset($setting['transfer_candy_rate'])): ?><?php echo e($setting['transfer_candy_rate'] ?? ''); ?><?php endif; ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="layui-form-item ecology">
                                </div>
                            </div>
                        </div>

                        <div>
                            <div>
                                <div class="layui-form-item ecology">
                                    <div class="layui-inline">
                                        <label class="layui-form-label">通证转账最小值</label>
                                        <div class="layui-input-inline">
                                            <input class="layui-input" lay-verify="1" placeholder="" name="transfer_candy_min" type="text" onkeyup="" value="<?php if(isset($setting['transfer_candy_min'])): ?><?php echo e($setting['transfer_candy_min'] ?? ''); ?><?php endif; ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="layui-form-item ecology">
                                </div>
                            </div>
                        </div>

                        <div>
                            <div>
                                <div class="layui-form-item ecology">
                                    <div class="layui-inline">
                                        <label class="layui-form-label">通证转账最大值</label>
                                        <div class="layui-input-inline">
                                            <input class="layui-input" lay-verify="1" placeholder="" name="transfer_candy_max" type="text" onkeyup="" value="<?php if(isset($setting['transfer_candy_max'])): ?><?php echo e($setting['transfer_candy_max'] ?? ''); ?><?php endif; ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="layui-form-item ecology">
                                </div>
                            </div>
                        </div>

                        <div>
                            <div>
                                <div class="layui-form-item ecology">
                                    <div class="layui-inline">
                                        <label class="layui-form-label">实名认证送通证数量</label>
                                        <div class="layui-input-inline">
                                            <input class="layui-input" lay-verify="1" placeholder="" name="real_name_candy" type="text" onkeyup="" value="<?php if(isset($setting['real_name_candy'])): ?><?php echo e($setting['real_name_candy'] ?? ''); ?><?php endif; ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="layui-form-item ecology">
                                </div>
                            </div>
                        </div>

                        <div>
                            <div>
                                <div class="layui-form-item ecology">
                                    <div class="layui-inline">
                                        <label class="layui-form-label">直推</label>
                                        <div class="layui-input-inline" style="width: 50px;">
                                            <input class="layui-input" lay-verify="required" placeholder="直推" name="zhitui2_number" type="text" value="<?php if(isset($setting['zhitui2_number'])): ?><?php echo e($setting['zhitui2_number'] ?? ''); ?><?php endif; ?>"><span style="position: absolute;right: 5px;top: 12px;"></span>
                                        </div>
                                        <label class="layui-form-label" style="width: 50px;">人实名,</label>
                                    </div>
                                    <div class="layui-inline">
                                        <label class="layui-form-label" style="width: 44px;">送通证</label>
                                        <div class="layui-input-inline">
                                            <input style="width: 100px;" class="layui-input" lay-verify="required" placeholder="" name="zhitui2_candy"
                                                   value="<?php if(isset($setting['zhitui2_candy'])): ?><?php echo e($setting['zhitui2_candy']  ?? ''); ?><?php endif; ?>">
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div>
                            <div>
                                <div class="layui-form-item ecology">
                                    <div class="layui-inline">
                                        <label class="layui-form-label">直推</label>
                                        <div class="layui-input-inline" style="width: 50px;">
                                            <input class="layui-input" lay-verify="required" placeholder="直推"
                                                   name="zhitui3_number" type="text"
                                                   value="<?php if(isset($setting['zhitui3_number'])): ?><?php echo e($setting['zhitui3_number'] ?? ''); ?><?php endif; ?>"><span
                                                    style="position: absolute;right: 5px;top: 12px;"></span>
                                        </div>
                                        <label class="layui-form-label" style="width: 110px;">人实名,实名团队</label>
                                        <div class="layui-input-inline" style="width: 80px;">
                                            <input class="layui-input" lay-verify="required" placeholder="实名团队"
                                                   name="zhitui3_real_teamnumber" type="text"
                                                   value="<?php if(isset($setting['zhitui3_real_teamnumber'])): ?><?php echo e($setting['zhitui3_real_teamnumber'] ?? ''); ?><?php endif; ?>"><span
                                                    style="position: absolute;right: 5px;top: 12px;"></span>
                                        </div>
                                        <label class="layui-form-label" style="width: 110px;">人,充值美金达到</label>
                                        <div class="layui-input-inline" style="width: 80px;">
                                            <input class="layui-input" lay-verify="required" placeholder="充值美金"
                                                   name="zhitui3_top_upnumber" type="text"
                                                   value="<?php if(isset($setting['zhitui3_top_upnumber'])): ?><?php echo e($setting['zhitui3_top_upnumber'] ?? ''); ?><?php endif; ?>"><span
                                                    style="position: absolute;right: 5px;top: 12px;"></span>
                                        </div>
                                    </div>
                                    <div class="layui-inline">
                                        <label class="layui-form-label" style="width: 44px;">送通证</label>
                                        <div class="layui-input-inline">
                                            <input style="width: 100px;" class="layui-input" lay-verify="required" placeholder="" name="zhitui3_candy"
                                                   value="<?php if(isset($setting['zhitui3_candy'])): ?><?php echo e($setting['zhitui3_candy']  ?? ''); ?><?php endif; ?>">
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div>
                            <div>
                                <div class="layui-form-item ecology">
                                    <div class="layui-inline">
                                        <label class="layui-form-label">直推</label>
                                        <div class="layui-input-inline" style="width: 50px;">
                                            <input class="layui-input" lay-verify="required" placeholder="直推"
                                                   name="zhitui4_number" type="text"
                                                   value="<?php if(isset($setting['zhitui4_number'])): ?><?php echo e($setting['zhitui4_number'] ?? ''); ?><?php endif; ?>"><span
                                                    style="position: absolute;right: 5px;top: 12px;"></span>
                                        </div>
                                        <label class="layui-form-label" style="width: 110px;">人实名,实名团队</label>
                                        <div class="layui-input-inline" style="width: 80px;">
                                            <input class="layui-input" lay-verify="required" placeholder="实名团队"
                                                   name="zhitui4_real_teamnumber" type="text"
                                                   value="<?php if(isset($setting['zhitui4_real_teamnumber'])): ?><?php echo e($setting['zhitui4_real_teamnumber'] ?? ''); ?><?php endif; ?>"><span
                                                    style="position: absolute;right: 5px;top: 12px;"></span>
                                        </div>
                                        <label class="layui-form-label" style="width: 110px;">人,充值美金达到</label>
                                        <div class="layui-input-inline" style="width: 80px;">
                                            <input class="layui-input" lay-verify="required" placeholder="充值美金"
                                                   name="zhitui4_top_upnumber" type="text"
                                                   value="<?php if(isset($setting['zhitui4_top_upnumber'])): ?><?php echo e($setting['zhitui4_top_upnumber'] ?? ''); ?><?php endif; ?>"><span
                                                    style="position: absolute;right: 5px;top: 12px;"></span>
                                        </div>
                                    </div>
                                    <div class="layui-inline">
                                        <label class="layui-form-label" style="width: 44px;">送通证</label>
                                        <div class="layui-input-inline">
                                            <input style="width: 100px;" class="layui-input" lay-verify="required" placeholder="" name="zhitui4_candy"
                                                   value="<?php if(isset($setting['zhitui4_candy'])): ?><?php echo e($setting['zhitui4_candy']  ?? ''); ?><?php endif; ?>">
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div>
                            <div>
                                <div class="layui-form-item ecology">
                                    <div class="layui-inline">
                                        <label class="layui-form-label">直推</label>
                                        <div class="layui-input-inline" style="width: 50px;">
                                            <input class="layui-input" lay-verify="required" placeholder="直推"
                                                   name="zhitui5_number" type="text"
                                                   value="<?php if(isset($setting['zhitui5_number'])): ?><?php echo e($setting['zhitui5_number'] ?? ''); ?><?php endif; ?>"><span
                                                    style="position: absolute;right: 5px;top: 12px;"></span>
                                        </div>
                                        <label class="layui-form-label" style="width: 110px;">人实名,实名团队</label>
                                        <div class="layui-input-inline" style="width: 80px;">
                                            <input class="layui-input" lay-verify="required" placeholder="实名团队"
                                                   name="zhitui5_real_teamnumber" type="text"
                                                   value="<?php if(isset($setting['zhitui5_real_teamnumber'])): ?><?php echo e($setting['zhitui5_real_teamnumber'] ?? ''); ?><?php endif; ?>"><span
                                                    style="position: absolute;right: 5px;top: 12px;"></span>
                                        </div>
                                        <label class="layui-form-label" style="width: 110px;">人,充值美金达到</label>
                                        <div class="layui-input-inline" style="width: 80px;">
                                            <input class="layui-input" lay-verify="required" placeholder="充值美金"
                                                   name="zhitui5_top_upnumber" type="text"
                                                   value="<?php if(isset($setting['zhitui5_top_upnumber'])): ?><?php echo e($setting['zhitui5_top_upnumber'] ?? ''); ?><?php endif; ?>"><span
                                                    style="position: absolute;right: 5px;top: 12px;"></span>
                                        </div>
                                    </div>
                                    <div class="layui-inline">
                                        <label class="layui-form-label" style="width: 44px;">送通证</label>
                                        <div class="layui-input-inline">
                                            <input style="width: 100px;" class="layui-input" lay-verify="required" placeholder="" name="zhitui5_candy"
                                                   value="<?php if(isset($setting['zhitui5_candy'])): ?><?php echo e($setting['zhitui5_candy']  ?? ''); ?><?php endif; ?>">
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div>
                            <div>
                                <div class="layui-form-item ecology">
                                    <div class="layui-inline">
                                        <label class="layui-form-label">直推</label>
                                        <div class="layui-input-inline" style="width: 50px;">
                                            <input class="layui-input" lay-verify="required" placeholder="直推"
                                                   name="zhitui6_number" type="text"
                                                   value="<?php if(isset($setting['zhitui6_number'])): ?><?php echo e($setting['zhitui6_number'] ?? ''); ?><?php endif; ?>"><span
                                                    style="position: absolute;right: 5px;top: 12px;"></span>
                                        </div>
                                        <label class="layui-form-label" style="width: 110px;">人实名,实名团队</label>
                                        <div class="layui-input-inline" style="width: 80px;">
                                            <input class="layui-input" lay-verify="required" placeholder="实名团队"
                                                   name="zhitui6_real_teamnumber" type="text"
                                                   value="<?php if(isset($setting['zhitui6_real_teamnumber'])): ?><?php echo e($setting['zhitui6_real_teamnumber'] ?? ''); ?><?php endif; ?>"><span
                                                    style="position: absolute;right: 5px;top: 12px;"></span>
                                        </div>
                                        <label class="layui-form-label" style="width: 110px;">人,充值美金达到</label>
                                        <div class="layui-input-inline" style="width: 80px;">
                                            <input class="layui-input" lay-verify="required" placeholder="充值美金"
                                                   name="zhitui6_top_upnumber" type="text"
                                                   value="<?php if(isset($setting['zhitui6_top_upnumber'])): ?><?php echo e($setting['zhitui6_top_upnumber'] ?? ''); ?><?php endif; ?>"><span
                                                    style="position: absolute;right: 5px;top: 12px;"></span>
                                        </div>
                                    </div>
                                    <div class="layui-inline">
                                        <label class="layui-form-label" style="width: 44px;">送通证</label>
                                        <div class="layui-input-inline">
                                            <input style="width: 100px;" class="layui-input" lay-verify="required" placeholder="" name="zhitui6_candy"
                                                   value="<?php if(isset($setting['zhitui6_candy'])): ?><?php echo e($setting['zhitui6_candy']  ?? ''); ?><?php endif; ?>">
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                        <div class="layui-form-item">
                            <div class="layui-input-block">
                                <button class="layui-btn" lay-submit lay-filter="website_submit">立即提交</button>
                                <button type="reset" class="layui-btn layui-btn-primary">重置</button>
                            </div>
                        </div>
                    </div>
                    <!--合约设置开始-->
                    <div class="layui-tab-item">
                        <div class="layui-form-item">
                            <label class="layui-form-label">合约参数</label>
                            <div class="layui-input-block">
                                <div class="layui-inline btn-group layui-btn-group">
                                    <button class="layui-btn layui-btn-primary cateManage" type="button" id="add_seconds"><i class="layui-icon layui-icon-log"></i>秒数设置</button>
                                    <button class="layui-btn layui-btn-primary" type="button" id="add_number"><i class="layui-icon layui-icon-about"></i>数量设置</button>
                                    <!--<button class="layui-btn layui-btn-primary" type="button" id="user"> <i class="layui-icon layui-icon-username"></i> 用户管理</button>-->
                                    <!--<button class="layui-btn layui-btn-primary" type="button" id="currency_risk"> <i class="layui-icon layui-icon-dollar"></i>币种输赢</button>-->
                                    <!--<button class="layui-btn layui-btn-primary" type="button" id="second_trade"> <i class="layui-icon layui-icon-form"></i>期权交易</button>-->
                                </div>
                            </div>
                        </div>
                        <!--<div class="layui-form-item">-->
                        <!--    <label class="layui-form-label">输赢类型</label>-->
                        <!--    <div class="layui-input-block">-->
                        <!--        <select name="risk_mode" lay-verify="required" lay-filter="risk_mode">-->
                        <!--            <option value=""></option>-->
                        <!--            <option value="0" <?php echo e(($setting['risk_mode']?? 0) == 0 ? 'selected' : ''); ?> >无</option>-->
                        <!--            <option value="1" <?php echo e(($setting['risk_mode']?? 0) == 1 ? 'selected' : ''); ?> >用户点控</option>-->
                        <!--            <option value="2" <?php echo e(($setting['risk_mode']?? 0) == 2 ? 'selected' : ''); ?> >全局群控</option>-->
                        <!--            <option value="3" <?php echo e(($setting['risk_mode']?? 0) == 3 ? 'selected' : ''); ?> >金额控</option>-->
                        <!--            <option value="4" <?php echo e(($setting['risk_mode']?? 0) == 4 ? 'selected' : ''); ?> >交易单控</option>-->
                        <!--            <option value="5" <?php echo e(($setting['risk_mode']?? 0) == 5 ? 'selected' : ''); ?> >币种群控</option>-->
                        <!--        </select>-->
                        <!--    </div>-->
                        <!--</div>-->
                        <!--<div class="layui-form-item">-->
                        <!--    <label class="layui-form-label">输赢提前影响秒数</label>-->
                        <!--    <div class="layui-input-block">-->
                        <!--        <input type="text" name="risk_end_ago_max" required  lay-verify="required" placeholder="平仓前多少秒开始被输赢影响" autocomplete="off" class="layui-input" value="<?php echo e($setting['risk_end_ago_max'] ?? 0); ?>">-->
                        <!--    </div>-->
                        <!--</div>-->

                        <!--<div class="layui-form-item">-->
                        <!--    <label class="layui-form-label">概率控</label>-->
                        <!--    <div class="layui-input-inline">-->
                        <!--        <input type="radio" name="risk_probability_switch" value="0" title="关闭" <?php echo e(($setting['risk_probability_switch'] ?? 0) == 0 ? 'checked' : ''); ?> >-->
                        <!--        <input type="radio" name="risk_probability_switch" value="1" title="开启" <?php echo e(($setting['risk_probability_switch'] ?? 0) == 1 ? 'checked' : ''); ?> >-->
                        <!--    </div>-->
                        <!--    <div class="layui-form-mid layui-word-aux">作为一种补充,不受输赢总开关影响</div>-->
                        <!--</div>-->

                        <!--<div class="layui-form-item layui-form-text" id="risk_profit_probability">-->
                        <!--    <label class="layui-form-label">盈利概率</label>-->
                        <!--    <div class="layui-input-inline">-->
                        <!--        <input class="layui-input" type="text" name="risk_profit_probability" placeholder="" value="<?php echo e($setting['risk_profit_probability'] ?? ''); ?>">-->
                        <!--    </div>-->
                        <!--    <div class="layui-form-mid layui-word-aux">%</div>-->
                        <!--    <div class="layui-form-mid layui-word-aux">不能超过100%</div>-->
                        <!--</div>-->

                        <!--<div class="layui-form-item" <?php echo e(($setting['risk_mode'] ?? 0) == 2 ? '' : 'hidden'); ?> id="risk_group_result">-->
                        <!--    <label class="layui-form-label">群控结果</label>-->
                        <!--    <div class="layui-input-block">-->
                        <!--        <input type="radio" name="risk_group_result" value="1" title="盈利" <?php echo e(($setting['risk_group_result'] ?? 1) == 1 ? 'checked' : ''); ?> >-->
                        <!--        <input type="radio" name="risk_group_result" value="-1" title="亏损" <?php echo e(($setting['risk_group_result'] ?? -1) == -1 ? 'checked' : ''); ?>>-->
                        <!--    </div>-->
                        <!--</div>-->

                        <!--<div class="layui-form-item layui-form-text" <?php echo e(($setting['risk_mode'] ?? 0) == 3 ? '' : 'hidden'); ?>  id="risk_money_profit_probability">-->
                        <!--    <label class="layui-form-label">金额概率设置</label>-->
                        <!--    <div class="layui-input-block">-->
                        <!--        <textarea class="layui-textarea" name="risk_money_profit_probability" placeholder="格式:最小值-最大值:盈利概率,用|线分隔"><?php echo e($setting['risk_money_profit_probability'] ?? ''); ?></textarea>-->
                        <!--    </div>-->
                        <!--</div>-->

                        <!--<div class="layui-form-item">-->
                        <!--    <label class="layui-form-label">交易保险</label>-->
                        <!--    <div class="layui-input-block">-->
                        <!--        <div class="layui-inline btn-group layui-btn-group">-->
                        <!--            <button class="layui-btn layui-btn-primary" type="button" id="insurance_setup"><i class="layui-icon layui-icon-password"></i>规则设置</button>-->
                        <!--        </div>-->
                        <!--    </div>-->
                        <!--</div>-->
                        <!--<div class="layui-form-item">-->
                        <!--    <label class="layui-form-label">受保时间</label>-->
                        <!--    <div class="layui-input-block">-->
                        <!--        <div class="layui-input-inline" style="width:100px;">-->
                        <!--            <input type="text" class="layui-input layui-date" name="insurance_start" value="<?php echo e($setting['insurance_start']); ?>" placeholder="开始时间" >-->
                        <!--        </div>-->
                        <!--        <div class="layui-form-mid layui-word-aux">至</div>-->
                        <!--        <div class="layui-input-inline" style="width:100px;">-->
                        <!--            <input type="text" class="layui-input layui-date" name="insurance_end" value="<?php echo e($setting['insurance_end']); ?>" placeholder="结束时间">-->
                        <!--        </div>-->
                        <!--    </div>-->
                        <!--</div>-->
                        <div class="layui-form-item">
                            <div class="layui-input-block">
                                <button class="layui-btn" lay-submit lay-filter="website_submit">立即提交</button>
                                <button type="reset" class="layui-btn layui-btn-primary">重置</button>
                            </div>
                        </div>
                    </div>

                    <!--表单设置开始-->
                    <div class="layui-tab-item">
                        <div class="layui-form-item">
                            <label class="layui-form-label">在线客服</label>
                            <div class="layui-input-block">
                                <div class="layui-input-inline">
                                    <input type="radio" name="zxkf_radio" value="1" title="开启" <?php if(isset($setting['zxkf_radio'])): ?> <?php echo e($setting['zxkf_radio'] == 1 ? 'checked' : ''); ?> <?php endif; ?> >
                                    <input type="radio" name="zxkf_radio" value="0" title="关闭" <?php if(isset($setting['zxkf_radio'])): ?> <?php echo e($setting['zxkf_radio'] == 0 ? 'checked' : ''); ?> <?php else: ?> checked <?php endif; ?> >
                                </div>
                                <div class="layui-input-block">
                                    <input type="text" name="zxkf_url" placeholder="请输入链接" autocomplete="off" class="layui-input"
                                           value="<?php if(isset($setting['zxkf_url'])): ?><?php echo e($setting['zxkf_url'] ?? ''); ?><?php endif; ?>">
                                </div>
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">Telegram</label>
                            <div class="layui-input-block">
                                <div class="layui-input-inline">
                                    <input type="radio" name="telegram_radio" value="1" title="开启" <?php if(isset($setting['telegram_radio'])): ?> <?php echo e($setting['telegram_radio'] == 1 ? 'checked' : ''); ?> <?php endif; ?> >
                                    <input type="radio" name="telegram_radio" value="0" title="关闭" <?php if(isset($setting['telegram_radio'])): ?> <?php echo e($setting['telegram_radio'] == 0 ? 'checked' : ''); ?> <?php else: ?> checked <?php endif; ?> >
                                </div>
                                <div class="layui-input-block">
                                    <input type="text" name="telegram_url" placeholder="请输入链接" autocomplete="off" class="layui-input"
                                           value="<?php if(isset($setting['telegram_url'])): ?><?php echo e($setting['telegram_url'] ?? ''); ?><?php endif; ?>">
                                </div>
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">Skype</label>
                            <div class="layui-input-block">
                                <div class="layui-input-inline">
                                    <input type="radio" name="skype_radio" value="1" title="开启" <?php if(isset($setting['skype_radio'])): ?> <?php echo e($setting['skype_radio'] == 1 ? 'checked' : ''); ?> <?php endif; ?> >
                                    <input type="radio" name="skype_radio" value="0" title="关闭" <?php if(isset($setting['skype_radio'])): ?> <?php echo e($setting['skype_radio'] == 0 ? 'checked' : ''); ?> <?php else: ?> checked <?php endif; ?> >
                                </div>
                                <div class="layui-input-block">
                                    <input type="text" name="skype_url" placeholder="请输入链接" autocomplete="off" class="layui-input"
                                           value="<?php if(isset($setting['skype_url'])): ?><?php echo e($setting['skype_url'] ?? ''); ?><?php endif; ?>">
                                </div>
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">WhatsApp</label>
                            <div class="layui-input-block">
                                <div class="layui-input-inline">
                                    <input type="radio" name="whatsApp_radio" value="1" title="开启" <?php if(isset($setting['whatsApp_radio'])): ?> <?php echo e($setting['whatsApp_radio'] == 1 ? 'checked' : ''); ?> <?php endif; ?> >
                                    <input type="radio" name="whatsApp_radio" value="0" title="关闭" <?php if(isset($setting['whatsApp_radio'])): ?> <?php echo e($setting['whatsApp_radio'] == 0 ? 'checked' : ''); ?> <?php else: ?> checked <?php endif; ?> >
                                </div>
                                <div class="layui-input-block">
                                    <input type="text" name="whatsApp_url" placeholder="请输入链接" autocomplete="off" class="layui-input"
                                           value="<?php if(isset($setting['whatsApp_url'])): ?><?php echo e($setting['whatsApp_url'] ?? ''); ?><?php endif; ?>">
                                </div>
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">Line</label>
                            <div class="layui-input-block">
                                <div class="layui-input-inline">
                                    <input type="radio" name="line_radio" value="1" title="开启" <?php if(isset($setting['line_radio'])): ?> <?php echo e($setting['line_radio'] == 1 ? 'checked' : ''); ?> <?php endif; ?> >
                                    <input type="radio" name="line_radio" value="0" title="关闭" <?php if(isset($setting['line_radio'])): ?> <?php echo e($setting['line_radio'] == 0 ? 'checked' : ''); ?> <?php else: ?> checked <?php endif; ?> >
                                </div>
                                <div class="layui-input-block">
                                    <input type="text" name="line_url" placeholder="请输入链接" autocomplete="off" class="layui-input"
                                           value="<?php if(isset($setting['line_url'])): ?><?php echo e($setting['line_url'] ?? ''); ?><?php endif; ?>">
                                </div>
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">借款专员</label>
                            <div class="layui-input-block">
                                <div class="layui-input-inline">
                                    <input type="radio" name="jie_radio" value="1" title="开启" <?php if(isset($setting['jie_radio'])): ?> <?php echo e($setting['jie_radio'] == 1 ? 'checked' : ''); ?> <?php endif; ?> >
                                    <input type="radio" name="jie_radio" value="0" title="关闭" <?php if(isset($setting['jie_radio'])): ?> <?php echo e($setting['jie_radio'] == 0 ? 'checked' : ''); ?> <?php else: ?> checked <?php endif; ?> >
                                </div>
                                <div class="layui-input-block">
                                    <input type="text" name="jie_url" placeholder="请输入链接" autocomplete="off" class="layui-input"
                                           value="<?php if(isset($setting['jie_url'])): ?><?php echo e($setting['jie_url'] ?? ''); ?><?php endif; ?>">
                                </div>
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <label class="layui-form-label">还款专员</label>
                            <div class="layui-input-block">
                                <div class="layui-input-inline">
                                    <input type="radio" name="hk_radio" value="1" title="开启" <?php if(isset($setting['hk_radio'])): ?> <?php echo e($setting['hk_radio'] == 1 ? 'checked' : ''); ?> <?php endif; ?> >
                                    <input type="radio" name="hk_radio" value="0" title="关闭" <?php if(isset($setting['hk_radio'])): ?> <?php echo e($setting['hk_radio'] == 0 ? 'checked' : ''); ?> <?php else: ?> checked <?php endif; ?> >
                                </div>
                                <div class="layui-input-block">
                                    <input type="text" name="hk_url" placeholder="请输入链接" autocomplete="off" class="layui-input"
                                           value="<?php if(isset($setting['hk_url'])): ?><?php echo e($setting['hk_url'] ?? ''); ?><?php endif; ?>">
                                </div>
                            </div>
                        </div>
                        <div class="layui-form-item">
                            <div class="layui-input-block">
                                <button class="layui-btn" lay-submit lay-filter="website_submit">立即提交</button>
                                <button type="reset" class="layui-btn layui-btn-primary">重置</button>
                            </div>
                        </div>
                    </div>
                   
                    <div class="layui-tab-item">
                         <div class="layui-form-item">
                            <label class="layui-form-label">通道的开关</label>
                            <div class="layui-input-block">
                                <div class="layui-input-inline">
                                    <input type="radio" name="bank_flag" value="1" title="打开" <?php if(isset($setting['bank_flag'])): ?> <?php echo e($setting['bank_flag'] == 1 ? 'checked' : ''); ?> <?php endif; ?> >
                                    <input type="radio" name="bank_flag" value="0" title="关闭" <?php if(isset($setting['bank_flag'])): ?> <?php echo e($setting['bank_flag'] == 0 ? 'checked' : ''); ?> <?php else: ?> checked <?php endif; ?> >
                                </div>
                                <!--<div class="layui-form-mid layui-word-aux">打开用户将可以针对交易设置止盈止亏价</div>-->
                            </div>
                            <div class="layui-form-item">
                            <div class="layui-input-block">
                                <button class="layui-btn" lay-submit lay-filter="website_submit">立即提交</button>
                                <button type="reset" class="layui-btn layui-btn-primary">重置</button>
                            </div>
                        </div>
                        </div>
                        <a class="layui-btn layui-btn-normal layui-btn-radius"  id="yhk">添加</a>
                        
                        <table id="bankCard" lay-filter="bankCard" ></table>
                    </div>
                </div>
            </div>

        </form>
    </div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('scripts'); ?>
<script type="text/html" id="barDemo">
    <a class="layui-btn layui-btn-warm layui-btn-xs" lay-event="edit">编辑</a>
    <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="delete">删除</a>
</script>
    <script type="text/javascript">
        layui.use(['element', 'form','table', 'upload', 'layer', 'laydate'], function () {
            var upload = layui.upload
                ,element = layui.element
                ,layer = layui.layer
                ,form = layui.form
                ,table = layui.table
                ,laydate = layui.laydate
                ,$ = layui.$;
                $('#yhk').click(function(){layer_show('添加银行卡', '/admin/setting/bank_add');});
                var uploadInst = upload.render({
                    elem: '.upload_test' //绑定元素
                    ,url: '<?php echo e(URL("api/upload")); ?>?scene=admin' //上传接口
                    ,done: function(res){
                        //上传完毕回调
                        if (res.type == "ok"){
                            var pbox = $(this.item).closest('.upload-box');
                            pbox.find(".thumb-input").val(res.message);
                            pbox.find('.clear_upload').show();
                            pbox.find(".thumbnail").show();
                            pbox.find(".thumbnail").attr("src",res.message)
                        } else{
                            alert(res.message)
                        }
                    }
                    ,error: function(){
                        //请求异常回调
                    }
                });
                var user_table = table.render({
                    elem: '#bankCard'
                    ,toolbar: true
                    ,url: '/admin/setting/bankCard'
                    ,page: true
                    ,limit: 100
                    ,limits: [20, 50, 100, 200, 500, 1000]
                    ,height: 'full-60'
                    ,cols: [[
                        {field: '', type: 'checkbox'}
                        ,{field: 'currency_name', title: '币种', minWidth: 100}
                        ,{field: 'pay_way_name', title: '支付方式', minWidth: 100}
                        ,{field: 'commissions', title: '手续费', minWidth: 100}
                        ,{field: 'account_name', title: '账户名称', minWidth: 100}
                        ,{field: 'iban', title: 'IBAN', minWidth: 100}
                        ,{field: 'beneficiary_country', title: '收款人国家/地区', minWidth: 100}
                        ,{field: 'bank_code', title: '银行编码', minWidth: 100}
                        ,{field: 'bank_name', title: '银行名称', minWidth: 100}
                        ,{field: 'bank_address', title: '银行卡地址', minWidth: 300}
                        ,{fixed: 'right', title: '操作', minWidth:130, align: 'center', toolbar: '#barDemo'}
                    ]]
                });
                 table.on('tool(bankCard)', function (obj) {
                    var data = obj.data;
                    var layEvent = obj.event;
                    var tr = obj.tr;
                    if (layEvent === 'delete') { //删除
                        layer.confirm('真的要删除吗？', function (index) {
                            //向服务端发送删除指令
                            $.ajax({
                                url: "/admin/setting/delBankCard",
                                type: 'post',
                                dataType: 'json',
                                data: {id: data.id},
                                success: function (res) {
                                    if (res.type == 'ok') {
                                        obj.del(); //删除对应行（tr）的DOM结构，并更新缓存
                                        layer.close(index);
                                    } else {
                                        layer.close(index);
                                        layer.alert(res.message);
                                    }
                                }
                            });
                        });
                    } 
                    
                    if(layEvent === 'edit') { //编辑
                        layer_show('编辑银行卡','/admin/setting/editBankCard?id=' + data.id);
                    }
                 });
            
            $('.clear_upload').click(function() {
                var pbox = $(this).closest('.upload-box');
                pbox.find(".thumb-input").val('');
                pbox.find('.clear_upload').hide();
                pbox.find(".thumbnail").hide();
                pbox.find(".thumbnail").attr("src",'');
            });
            $('#handle_multi_set').click(function () {
                layer.open({
                    type: 2
                    ,title: '杠杆交易手数和倍数设置'
                    ,content: '/admin/levermultiple/index'
                    ,area: ['600px', '430px']
                    ,id: 99
                });
            });
            $('#add_seconds').click(function() {
                layer.open({
                    type: 2
                    ,title: '秒数设置'
                    ,content: '/admin/micro_seconds_index'
                    ,area: ['95%', '95%']
                    ,id: 100
                });
            });
            $('#add_number').click(function() {
                layer.open({
                    type: 2
                    ,title: '数量设置'
                    ,content: '/admin/micro_number_index'
                    ,area: ['600px', '430px']
                    ,id: 101
                });
            });
            $('#user').click(function () {
                layer.open({
                    type: 2
                    ,title: '用户管理'
                    ,content: '/admin/user/user_index'
                    ,area: ['80%', '80%']
                    ,id: 102
                });
            });
            $('#currency_risk').click(function () {
                parent.layer.open({
                    type: 2
                    ,title: '币种输赢'
                    ,content: '/admin/currency/micro_match'
                    ,area: ['640px', '500px']
                    ,id: 103
                    ,maxmin: true
                    ,zIndex: parent.layer.zIndex
                });
            });
            $('#second_trade').click(function () {

                parent.layer.open({
                    type: 2
                    ,title: '期权交易'
                    ,content: '/admin/micro_order'
                    ,area: ['80%', '80%']
                    ,id: 103
                    ,maxmin: true
                    ,zIndex: parent.layer.zIndex
                });
            });
            form.on('submit(website_submit)', function (data) {
                var data = data.field;
                delete data['file'];
                $.ajax({
                    url: '/admin/setting/postadd',
                    type: 'post',
                    dataType: 'json',
                    data: data,
                    success: function (res) {
                        layer.msg(res.message);
                    }
                });
                return false;
            });
            form.on('select(risk_mode)', function (data) {
                if (data.value == 2) {
                    $('#risk_group_result').removeAttr('hidden');
                } else {
                    $('#risk_group_result').attr('hidden', true);
                }
                if (data.value == 3) {
                    $('#risk_money_profit_probability').removeAttr('hidden');
                } else {
                    $('#risk_money_profit_probability').attr('hidden', true);
                }
            });
            $('.layui-date').each(function (index, element) {
                //console.log(element)
                laydate.render({
                    elem: element
                    ,type: 'time'
                    ,format: 'HH:mm'
                });
            });

            $('#insurance_setup').click(function () {
                parent.layer.open({
                    type: 2
                    ,title: '保险规则'
                    ,content: '/admin/insurance_rules_index'
                    ,area: ['800px', '500px']
                    ,id: 103
                    ,maxmin: true
                    ,zIndex: parent.layer.zIndex
                });
            });

            var template = `
                <tr>
                    <td>
                        <div class="layui-inline">
                            <div class="layui-input-inline" style="width: 90px;">
                                <input class="layui-input" name="generation[]" value="" required lay-verify="required">
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="layui-inline">
                            <div class="layui-input-inline" style="width: 90px;">
                                <input class="layui-input" name="reward_ratio[]" value="" required lay-verify="required">
                            </div>
                            <div class="layui-form-mid">
                                <span>%</span></div>
                            </div>
                        </td>
                        <td>
                            <div class="layui-input-inline" style="width: 90px;">
                                <input class="layui-input" name="need_has_trades[]" value="" required lay-verify="required">
                            </div>
                        </td>
                        <td>
                            <div class="layui-input-inline">
                            <button class="layui-btn layui-btn-sm layui-btn-danger" type="button" lay-event="del">删除</button>
                            </div>
                    </td>
                </tr>`;
            $('#addLeverTradeOption').click(function () {
                $('#leverTradeFeeOption').append(template);
            });
            $('#leverTradeFeeOption').on('click', 'button[lay-event]', function () {
                var that = this
                    ,event = $(this).attr('lay-event')
                if (event == 'del') {
                    layer.confirm('真的确定要删除吗?' , {
                        title: '删除确认'
                        ,icon: 3
                    }, function (index) {
                        $(that).parent().parent().parent().remove();
                        layer.close(index);
                    });
                }
            });
        });
    </script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin._layoutNew', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>