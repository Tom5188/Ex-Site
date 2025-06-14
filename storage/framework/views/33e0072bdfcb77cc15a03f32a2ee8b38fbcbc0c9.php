<?php $__env->startSection('title', '内容管理系统'); ?>

<?php $__env->startSection('page-head'); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('page-content'); ?>
  <div id="LAY_app">
    <div class="layui-layout layui-layout-admin">
      <div class="layui-header">
        <ul class="layui-nav layui-layout-left">
          <li class="layui-nav-item layadmin-flexible" lay-unselect>
            <a href="javascript:;" layadmin-event="flexible" title="侧边伸缩">
              <i class="layui-icon layui-icon-shrink-right" id="LAY_app_flexible"></i>
            </a>
          </li>

          <li class="layui-nav-item" lay-unselect>
            <a href="javascript:;" layadmin-event="refresh" title="刷新">
              <i class="layui-icon layui-icon-refresh-3"></i>
            </a>
          </li>
        </ul>

        <ul class="layui-nav layui-layout-right" lay-filter="layadmin-layout-right" style="padding-right: 20px">


          <li class="layui-nav-item layui-hide-xs" lay-unselect>
            <a href="javascript:;" layadmin-event="theme">
              <i class="layui-icon layui-icon-theme"></i>
            </a>
          </li>
          <li class="layui-nav-item layui-hide-xs" lay-unselect>
            <a href="javascript:;" layadmin-event="note">
              <i class="layui-icon layui-icon-note"></i>
            </a>
          </li>
          <li class="layui-nav-item layui-hide-xs" lay-unselect>
            <a href="javascript:;" layadmin-event="fullscreen">
              <i class="layui-icon layui-icon-screen-full"></i>
            </a>
          </li>
          <li class="layui-nav-item" lay-unselect >
            <a href="javascript:;">
              <cite><?php echo e(@$username); ?></cite>
            </a>
            <dl class="layui-nav-child">



              <dd layadmin-event="logout" style="text-align: center;"><a>退出</a></dd>
            </dl>
          </li>
        </ul>
      </div>

      <div class="layui-side layui-side-menu">
        <div class="layui-side-scroll">
          <div class="layui-logo">
            <span>内容管理系统</span>
          </div>
          <ul class="layui-nav layui-nav-tree" lay-shrink="all" id="LAY-system-side-menu" lay-filter="layadmin-system-side-menu">

            <?php $__currentLoopData = $menus; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li data-name="<?php echo e(@$vo['module']); ?>" class="layui-nav-item">
                  <a href="javascript:;" lay-tips="<?php echo e(@$vo['module']); ?>" lay-direction="2">
                    <i class="layui-icon <?php echo e(@$vo['icon']); ?>"></i>
                    <cite><?php echo e(@$vo['name']); ?></cite>
                  </a>
                  <?php if(isset($vo['children'])): ?>
                    <dl class="layui-nav-child">
                      <?php $__currentLoopData = $vo['children']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                          <dd data-name="console">
                            <a lay-href="/<?php echo e(@$v['action']); ?>"><?php echo e(@$v['name']); ?></a>
                          </dd>
                      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </dl>
                  <?php endif; ?>
                </li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

          </ul>
        </div>
      </div>

      <div class="layadmin-pagetabs" id="LAY_app_tabs">
        <div class="layui-icon layadmin-tabs-control layui-icon-prev" layadmin-event="leftPage"></div>
        <div class="layui-icon layadmin-tabs-control layui-icon-next" layadmin-event="rightPage"></div>
        <div class="layui-icon layadmin-tabs-control layui-icon-down">
          <ul class="layui-nav layadmin-tabs-select" lay-filter="layadmin-pagetabs-nav">
            <li class="layui-nav-item" lay-unselect>
              <a href="javascript:;"></a>
              <dl class="layui-nav-child layui-anim-fadein">
                <dd layadmin-event="closeThisTabs"><a href="javascript:;">关闭当前标签页</a></dd>
                <dd layadmin-event="closeOtherTabs"><a href="javascript:;">关闭其它标签页</a></dd>
                <dd layadmin-event="closeAllTabs"><a href="javascript:;">关闭全部标签页</a></dd>
              </dl>
            </li>
          </ul>
        </div>
        <div class="layui-tab" lay-unauto lay-allowClose="true" lay-filter="layadmin-layout-tabs">
          <ul class="layui-tab-title" id="LAY_app_tabsheader">
            <li lay-id="" lay-attr="" class="layui-this"><i class="layui-icon layui-icon-home"></i></li>
          </ul>
        </div>
      </div>
      <div class="layui-body" id="LAY_app_body">
        <div class="layadmin-tabsbody-item layui-show">
          <iframe src="/manages/console" frameborder="0" class="layadmin-iframe"></iframe>
        </div>
      </div>

      <div class="layadmin-body-shade" layadmin-event="shade"></div>
    </div>
  </div>
  <?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>

  <script>
  layui.use('layer', function () {
    var layer = layui.layer;

    var bbjy = '<?php echo e(URL("static/audio/bbjy.mp3")); ?>';
    var hyjy = '<?php echo e(URL("static/audio/hyjy.mp3")); ?>';
    var qqjy = '<?php echo e(URL("static/audio/qqjy.mp3")); ?>';
    var sblc = '<?php echo e(URL("static/audio/sblc.mp3")); ?>';
    var xbsg = '<?php echo e(URL("static/audio/xbsg.mp3")); ?>';
    var cz = '<?php echo e(URL("static/audio/cz.mp3")); ?>';
    var sfrz = '<?php echo e(URL("static/audio/sfrz.mp3")); ?>';
    var tksq = '<?php echo e(URL("static/audio/tksq.mp3")); ?>';
    var hyzc = '<?php echo e(URL("static/audio/hyzc.mp3")); ?>';
    var zywk = '<?php echo e(URL("static/audio/zywk.mp3")); ?>';
    var nft = '<?php echo e(URL("static/audio/nft.mp3")); ?>';
    var pay_success = '<?php echo e(URL("static/audio/pay_success.mp3")); ?>';

    var tips_url = '<?php echo e(url('admin/tips/tips')); ?>';
    $.ajax({
      url:tips_url + '?type=3',
      type:'GET',
      data:{},
      dataType:'JSON',
      success:function(res){
        if (res.code == 100){
          layer.msg("您有新的订单,请及时处理", {time: 3000, shift: 5,offset:"tc"});
          var audio = new Audio(qqjy);
          audio.play();
        }
      }
    });
    $.ajax({
      url:tips_url + '?type=6',
      type:'GET',
      data:{},
      dataType:'JSON',
      success:function(res){
        if (res.code == 100){
          layer.msg("您有新的充值订单,请及时处理", {time: 3000, shift: 5,offset:"tc"});
          var audio = new Audio(cz);
          audio.play();
        }
      }
    });
    $.ajax({
      url:tips_url + '?type=7',
      type:'GET',
      data:{},
      dataType:'JSON',
      success:function(res){
        if (res.code == 100){
          layer.msg("您有新的身份认证,请及时处理", {time: 3000, shift: 5,offset:"tc"});
          var audio = new Audio(sfrz);
          audio.play();
        }
      }
    });
    $.ajax({
      url:tips_url + '?type=8',
      type:'GET',
      data:{},
      dataType:'JSON',
      success:function(res){
        if (res.code == 100){
          layer.msg("您有新的提现申请,请及时处理", {time: 3000, shift: 5,offset:"tc"});
          var audio = new Audio(tksq);
          audio.play();
        }
      }
    });
    $.ajax({
      url:tips_url + '?type=99',
      type:'GET',
      data:{},
      dataType:'JSON',
      success:function(res){
        if (res.code == 100){
          layer.msg("支付通道有新订单成功,请及时处理", {time: 3000, shift: 5,offset:"tc"});
          var audio = new Audio(pay_success);
          audio.play();
        }
      }
    });
    // 订单轮询
    setInterval(function () {
        
      setTimeout(function (){
        $.ajax({
          url:tips_url + '?type=3',
          type:'GET',
          data:{},
          dataType:'JSON',
          success:function(res){
            if (res.code == 100){
              layer.msg("您有新的订单,请及时处理", {time: 3000, shift: 5,offset:"tc"});
              var audio = new Audio(qqjy);
              audio.play();
            }
          }
        });
      }, 15000);
      
      setTimeout(function (){
        $.ajax({
          url:tips_url + '?type=6',
          type:'GET',
          data:{},
          dataType:'JSON',
          success:function(res){
            if (res.code == 100){
              layer.msg("您有新的充值订单,请及时处理", {time: 3000, shift: 5,offset:"tc"});
              var audio = new Audio(cz);
              audio.play();
            }
          }
        });
      }, 15000);
      
      setTimeout(function (){
        $.ajax({
          url:tips_url + '?type=7',
          type:'GET',
          data:{},
          dataType:'JSON',
          success:function(res){
            if (res.code == 100){
              layer.msg("您有新的身份认证,请及时处理", {time: 3000, shift: 5,offset:"tc"});
              var audio = new Audio(sfrz);
              audio.play();
            }
          }
        });
      }, 15000);
      
      setTimeout(function(){
          $.ajax({
            url:tips_url + '?type=8',
            type:'GET',
            data:{},
            dataType:'JSON',
            success:function(res){
              if (res.code == 100){
                layer.msg("您有新的提现申请,请及时处理", {time: 3000, shift: 5,offset:"tc"});
                var audio = new Audio(tksq);
                audio.play();
              }
            }
          });
      }, 15000);
      
      setTimeout(function (){
        $.ajax({
          url:tips_url + '?type=99',
          type:'GET',
          data:{},
          dataType:'JSON',
          success:function(res){
            if (res.code == 100){
              layer.msg("支付通道有新订单成功,请及时处理", {time: 3000, shift: 5,offset:"tc"});
              var audio = new Audio(pay_success);
              audio.play();
            }
          }
        });
      }, 15000);
    },30000);
  });
  </script>

<?php $__env->stopSection(); ?>




<?php echo $__env->make('manages.layadmin', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>