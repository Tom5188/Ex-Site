<?php $__env->startSection('page-head'); ?>
	<link rel="stylesheet" type="text/css" href="<?php echo e(URL("layui/css/layui.css")); ?>" media="all">
	<link rel="stylesheet" type="text/css" href="<?php echo e(URL("admin/common/bootstrap/css/bootstrap.css")); ?>" media="all">
	<link rel="stylesheet" type="text/css" href="<?php echo e(URL("admin/common/global.css")); ?>" media="all">
	<link rel="stylesheet" type="text/css" href="<?php echo e(URL("admin/css/personal.css")); ?>" media="all">
<?php $__env->stopSection(); ?>
<?php $__env->startSection('page-content'); ?>
	<form class="layui-form" method="POST">
		<input type="hidden" name="project_id" id="projectId">
		<?php echo e(csrf_field()); ?>

		<div class="layui-form-item">
			<label class="layui-form-label">标题</label>
			<div class="layui-input-block">
				<input class="layui-input newsName" name="title" id="title" lay-verify="required" placeholder="请输入标题" type="text">
			</div>
		</div>
		<div class="layui-form-item layui-form-text">
			<label class="layui-form-label">图片</label>
			<div class="layui-input-block">
				<button class="layui-btn" type="button" id="upload_test">选择图片</button>
				<br>
				<img src="" style="display:none" id="logoImg">
				<input type="hidden" name="logo" id="logo" >
			</div>
		</div>
		<div class="layui-form-item" style="position:relative;z-index:10000">
			<label for="currency_id" class="layui-form-label">项目币种</label>
			<div class="layui-input-block">
				<select name="currency_id" lay-verify="required" id="currency_id" lay-search>
					<?php $__currentLoopData = $currencies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $currency): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
						<option value="<?php echo e($currency->id); ?>"
								<?php if ($info && $info->currency_id == $currency->id){ ?>
								selected
								<?php } ?>
						> <?php echo e($currency->name); ?></option>
					<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
				</select>
			</div>
		</div>
		<div class="layui-form-item" style="position:relative;z-index:9999">
			<label for="currency_id" class="layui-form-label">支付币种</label>
			<div class="layui-input-block">
				<select name="pay_currency_id" lay-verify="required" id="pay_currency_id" lay-search>
					<?php $__currentLoopData = $currencies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $currency): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
						<option value="<?php echo e($currency->id); ?>"
								<?php if ($info && $info->pay_currency_id == $currency->id){ ?>
								selected
								<?php } ?>
						> <?php echo e($currency->name); ?></option>
					<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
				</select>
			</div>
		</div>
		<div class="layui-inline">
			<label class="layui-form-label">发行总量</label>
			<div class="layui-input-inline">
				<input class="layui-input "  name="amount" type="text"  id="amount">
			</div>
		</div>
		<div class="layui-inline">
			<label class="layui-form-label">发行价格</label>
			<div class="layui-input-inline">
				<input class="layui-input " name="price" type="text"  id="price">
			</div>
		</div>
		<div class="layui-inline">
			<label class="layui-form-label">最低认购金额（支付币种）</label>
			<div class="layui-input-inline">
				<input class="layui-input "  name="min" type="text"  id="min">
			</div>
		</div>
		<div class="layui-inline">
			<label class="layui-form-label">配售数量</label>
			<div class="layui-input-inline">
				<input class="layui-input "  name="sell_amount" type="text"  id="sell_amount">
			</div>
		</div>
		<div class="layui-inline">
			<label class="layui-form-label">抽签总数</label>
			<div class="layui-input-inline">
				<input class="layui-input "  name="total_apply" type="text"  id="total_apply">
			</div>
		</div>
		<div class="layui-inline">
			<label class="layui-form-label">中签率%</label>
			<div class="layui-input-inline">
				<input class="layui-input "  name="win_rate" type="text"  id="win_rate">
			</div>
		</div>
		<div class="layui-inline">
			<label class="layui-form-label">开始时间</label>
			<div class="layui-input-inline">
				<input class="layui-input " id="start_at" name="start_at" type="text" >
			</div>
		</div>
		<div class="layui-inline">
			<label class="layui-form-label">结束时间</label>
			<div class="layui-input-inline">
				<input class="layui-input " id="end_at"  name="end_at" type="text">
			</div>
		</div>
		<div class="layui-inline">
			<label class="layui-form-label">配售时间</label>
			<div class="layui-input-inline">
				<input class="layui-input " id="sell_begin" name="sell_begin" type="text" >
			</div>
		</div>
		<div class="layui-inline">
			<label class="layui-form-label">官网链接</label>
			<div class="layui-input-inline">
				<input class="layui-input "  name="link" type="text"  id="link">
			</div>
		</div>
		<div class="layui-inline">
			<label class="layui-form-label">白皮书链接</label>
			<div class="layui-input-inline">
				<input class="layui-input "  name="white_book" type="text"  id="white_book">
			</div>
		</div>
		<div class="layui-inline">
			<label class="layui-form-label">发布状态</label>
			<div class="layui-input-inline">
				<select name="status" class="" id="status" lay-filter="status" lay-verify="required">

					<option value="1">已上架</option>
					<option value="2">已下架</option>
				</select>
			</div>
		</div>
		<div class="layui-form-item">
			<label class="layui-form-label">内容摘要</label>
			<div class="layui-input-block">
				<textarea placeholder="请输入内容摘要" class="layui-textarea" name="summary" id="summary"></textarea>
			</div>
		</div>

		<div class="layui-form-item">
			<label class="layui-form-label">文章内容</label>
			<div class="layui-input-block">
				<script id="news_content" name="content" type="text/plain" style="width:100%; height:300px;"></script>
			</div>
		</div>
		<div class="layui-form-item">
			<div class="layui-input-block">
				<button class="layui-btn" lay-submit="" lay-filter="submit">立即提交</button>
				<button type="reset" class="layui-btn layui-btn-primary">重置</button>
			</div>
		</div>
	</form>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('scripts'); ?>
	<script type="text/javascript" src="<?php echo e(URL('vendor/ueditor/1.4.3/ueditor.config.js')); ?>"></script>
	<script type="text/javascript" src="<?php echo e(URL('vendor/ueditor/1.4.3/ueditor.all.js')); ?>"> </script>
	<script type="text/javascript" src="<?php echo e(URL('vendor/ueditor/1.4.3/lang/zh-cn/zh-cn.js')); ?>"></script>
	<script type="text/javascript" src="<?php echo e(URL("/admin/js/projectFormSubmit.js?v=").time()); ?>"></script>
	<script>

		let url=window.location.href;
		let id='';

		if(url.indexOf("=")!=-1){
			id=url.substr(url.indexOf("=")+1);
			$("#projectId").val(id);
			$.ajax({
				url:"/admin/currency/project/detail",
				data:{
					project_id:id,
				},
				success:function(res){
					let data=res.message;
					$("#title").val(data.title);
					if(data.logo){
						$("#logoImg").attr('src',data.logo);
						$("#logo").val(data.logo);
						$("#logoImg").show();
					}else{
						$("#logoImg").hide();
						$("#upload_test").hide();
					}
					// for(let i=0;i<$("#currency_id option").length;i++){
					// 	if($("#currency_id option")[i].value==data.currency_id){
					// 		//   $("#currency_id option")[i].setAttribute('selected',true);
					// 		$("#currency_id option")[i].selected=i;
					// 	}
					// }
					// for(let i=0;i<$("#pay_currency_id option").length;i++){
					// 	if($("#pay_currency_id option")[i].value==data.pay_currency_id){
					// 		//   $("#pay_currency_id option")[i].setAttribute('selected',true);
					// 		$("#pay_currency_id option")[i].selected=i;
					// 	}
					// }
					// for(let i=0;i<$("#status option").length;i++){
					// 	if($("#status option")[i].value==data.status){
					// 		//   $("#status option")[i].setAttribute('selected',true);
					// 		$("#status option")[i].selected=i;
					// 	}
					// }
				// 	form.select();
					$("#amount").val(data.amount);
					$("#min").val(data.min);
					$("#price").val(data.price);
					$("#start_at").val(data.start_at);
					$("#end_at").val(data.end_at);
					$("#sell_begin").val(data.sell_begin);
					$("#link").val(data.link);
					$("#summary").val(data.summary);
					$("#news_content").val(data.content);
					$("#white_book").val(data.white_book);
					$("#title").val(data.title);
					$("#sell_amount").val(data.sell_amount);
					$("#total_apply").val(data.total_apply);
					$("#win_rate").val(data.win_rate);
				}
			})
		}

		layui.use('upload', function(){
			var upload = layui.upload;
			//执行实例
			var uploadInst = upload.render({
				elem: '#upload_test' //绑定元素
				,url: '<?php echo e(URL("api/upload")); ?>?scene=admin' //上传接口
				,done: function(res){
					//上传完毕回调
					if (res.type == "ok"){
						$("#logo").val(res.message)
						$("#logoImg").show()
						$("#logoImg").attr("src",res.message)
					} else{
						alert(res.message)
					}
				}
				,error: function(){
					//请求异常回调
				}
			});

		});
	</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin._layoutNew', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>