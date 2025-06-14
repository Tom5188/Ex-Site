

<?php $__env->startSection('page-head'); ?>
    <style>
        .layui-form-label {
            width:90px;
        }
        .layui-input-block {
            margin-left: 120px;
        }
    </style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('page-content'); ?>
    <form class="layui-form" action="">
        <div class="layui-form-item">
            <label class="layui-form-label">哈希值</label>
            <div class="layui-input-block">
                <input type="text" name="code" autocomplete="off" style="color:#d2d2d2;" placeholder="" class="layui-input" value="<?php echo e($results->code); ?>" lay-verify="required" disabled="disabled">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">交易币种</label>
            <div class="layui-input-block">
                <input type="text" name="price" autocomplete="off" style="color:#d2d2d2;" placeholder="" class="layui-input" value="<?php echo e($results->currency_name); ?>" lay-verify="required" disabled="disabled">
            </div>
        </div>
        
        <!--<div class="layui-form-item">-->
        <!--    <label class="layui-form-label">名称</label>-->
        <!--    <div class="layui-input-block">-->
        <!--        <input type="text" name="name" autocomplete="off" placeholder="" class="layui-input" value="">-->
        <!--    </div>-->
        <!--</div>-->
        <div class="layui-form-item">
            <label class="layui-form-label">交易类型</label>
            <div class="layui-input-block" >
                <select lay-filter="select_type" id="select_type" lay-verify="required" disabled="disabled">
                    <option value="1" class='layui-option' <?php echo e(($results->pay_type) == 1 ? 'selected' : ''); ?> >一口价</option>
                    <option value="2" class='layui-option' <?php echo e(($results->pay_type) == 2 ? 'selected' : ''); ?> >竞拍</option>
                    <option value="3" class='layui-option' <?php echo e(($results->pay_type) == 3 ? 'selected' : ''); ?> >盲盒</option>
                </select>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">价格</label>
            <div class="layui-input-block">
                <input type="text" name="price" autocomplete="off" placeholder="" class="layui-input" value="<?php echo e($results->price); ?>" lay-verify="required">
            </div>
        </div>
        <div class="layui-form-item" id="auction" lay-filter="auction">
            <label class="layui-form-label">竞拍每次加价</label>
            <div class="layui-input-block">
                <input type="text" name="per_increase" autocomplete="off" placeholder="" class="layui-input" value="<?php echo e($results->per_increase); ?>" lay-verify="required">
            </div>
        </div>
        <!--<div class="layui-form-item" id="auction" lay-filter="auction">-->
        <!--    <label class="layui-form-label">保证金</label>-->
        <!--    <div class="layui-input-block">-->
        <!--        <input type="text" name="margin" autocomplete="off" placeholder="" class="layui-input" value="<?php echo e($results->margin); ?>" lay-verify="required">-->
        <!--    </div>-->
        <!--</div>-->
        
        <div class="layui-form-item">
            <label class="layui-form-label">稀有度</label>
            <div class="layui-input-block">
                <select name='rarity'>
                    <option value="N" <?php echo e(($results->rarity) == 'N' ? 'selected' : ''); ?> >N</option>
                    <option value="R"  <?php echo e(($results->rarity) == 'R' ? 'selected' : ''); ?> >R</option>
                    <option value="SR"  <?php echo e(($results->rarity) == 'SR' ? 'selected' : ''); ?> >SR</option>
                </select>
            </div>
        </div>
        <!--<div class="layui-form-item">-->
        <!--    <div class="layui-input-block" style="min-height:0">-->
        <!--        <span class="" style="padding:5px 10px;background-color:#ffb800;border-radius:3px;color:#FFF">请严格按照时间格式编辑!</span>-->
        <!--    </div>-->
        <!--</div>-->
     <!--   <div class="layui-form-item">-->
     <!--       <label class="layui-form-label">文件上传</label>-->
     <!--       <div class="layui-upload"  style="border:1px solid #e6e6e6;border-radius:4px;padding:10px 0;margin-top:10px;margin-left:120px;"> -->
    	<!--	  <button type="button" class="layui-btn layui-btn-normal" id="cfile" style="margin-left:30px">选择文件</button> -->
    	<!--	  <br/><img src="<?php if(!empty($results->image)): ?><?php echo e($results->image); ?><?php endif; ?>" id="imageupload_img" class="imageupload" style="display: <?php if(!empty($results->image)): ?><?php echo e("block"); ?><?php else: ?><?php echo e("none"); ?><?php endif; ?>;max-width: 200px;height: auto;margin-top: 10px;margin-left:30px;">-->
    	<!--	  <div class="layui-upload-list">-->
    	<!--	    <table class="layui-table">-->
    	<!--	      <thead>-->
    	<!--	        <tr>-->
    	<!--		        <th>文件名</th>-->
    	<!--		        <th>大小</th>-->
    	<!--		        <th>状态</th>-->
    	<!--		        <th>操作</th>-->
    	<!--	       </tr> -->
    	<!--	      </thead>-->
    	<!--	      <input name="image" type="hidden" class="imageupload" value="<?php echo e($results->image); ?>" lay-verify="required">-->
    	<!--	      <tbody id="demoList"></tbody> -->
    	<!--	    </table>-->
    	<!--	  </div>-->
    	<!--	  <button type="button" class="layui-btn" id="uploadBegin" style="margin-left:30px">开始上传</button>-->
    		  <!--<a type="button" class="layui-btn layui-btn-danger" href="../../teacher/download"  style="margin-left:30px">模板下载</a>-->
    		 <!--<div class="layui-upload-list" style="height:25px;"></div>-->
    	<!--	</div> -->
    	<!--</div>-->
        <div class="test-table-reload-btn" style="margin-bottom: 10px;">
            <label class="layui-form-label">开始时间:</label>
            <div class="layui-inline">
                    <input class="layui-input test-laydate-item" name="start_time" value="<?php echo e($results->start_time); ?>" id="start_time" autocomplete="off">
                </div>
            <div class="layui-inline" style="width: 80px;text-align: right">结束时间:</div>
                <div class="layui-inline">
                    <input class="layui-input test-laydate-item" name="end_time" value="<?php echo e($results->end_time); ?>" id="end_time" autocomplete="off">
            </div>
        </div>
        <div class="layui-form-item">
            <div class="layui-input-block">
                <button class="layui-btn" lay-submit="" lay-filter="bindBoxsubmit">立即提交</button>
                <button type="reset" class="layui-btn layui-btn-primary">重置</button>
            </div>
        </div>
    </form>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
    <script>
        // 若选择拍卖显示加价
        var select_val = $('#select_type').val()
        if(select_val == '1'){
            $("#auction").hide();
        }if(select_val == '2'){
            $("#auction").show();
        }if(select_val == '3'){
            $("#auction").hide();
        }
        
//         layui.use(['layer', 'jquery', 'form'], function () {
// 			var layer = layui.layer,
// 					$ = layui.jquery,
// 					form = layui.form;
 
// 			form.on('select(select_type)', function(data){
//                 if(data.value == '1'){
//                     $("#auction").hide();
//                     form.render('select');
//                 }if(data.value == '2'){
//                     $("#auction").show();
//                     form.render('select');
//                 }if(data.value == '3'){
//                     $("#auction").hide();
//                     form.render('select');
//                 }
//             });
            
            
// 		});
        
        layui.use(['element', 'form', 'upload', 'layer', 'laydate'], function () {
            var element = layui.element
                ,layer = layui.layer
                ,form = layui.form
                ,laydate = layui.laydate
                ,$ = layui.$;
                
                //同时绑定多个
            lay('.test-laydate-item').each(function(){
              laydate.render({
                elem: this
                ,trigger: 'click'
                ,type: 'datetime'
              });
            });
        });
        
        layui.use(['form','laydate'],function() {
            var form = layui.form
                ,$ = layui.jquery
                ,laydate = layui.laydate
                ,index = parent.layer.getFrameIndex(window.name);
            form.render();

            //监听提交
            form.on('submit(bindBoxsubmit)', function(data){
                var data = data.field;
                delete data.file;
                // data.start_time=data.start_time.replace("T"," ")
                // data.end_time=data.end_time.replace("T"," ")
                $.ajax({
                    url:'<?php echo e(url('admin/bind_box/editNFT')); ?>'
                    ,type:'post'
                    ,dataType:'json'
                    ,data : data
                    ,success:function(res){
                        if(res.type=='ok'){
                            layer.msg('操作成功！');
                            parent.layer.close(index);
                            parent.window.location.reload();
                        }if(res.type=='error'){
                            layer.msg('操作失败'+res.message);
                        }else{
                            layer.msg('操作失败'+res.message);
                        }
                    }
                });
                return false;
            });
        });
        
        
        
        
    //     layui.use('upload', function(){
    // 		var upload = layui.upload;
    
    // 		//执行实例
    // 		var uploadInst = upload.render({
    // 			elem: '#upload_bindbox' //绑定元素
    // 			,url: '<?php echo e(URL("api/uploadNFT")); ?>?scene=admin' //上传接口
    // 			,done: function(res){
    // 				//上传完毕回调
    // 				if (res.type == "ok"){
    // 					$("#bindbox_upload").val(res.message)
    // 					$("#upload_bindbox_upload").show()
    // 					$("#upload_bindbox_upload").attr("src",res.message)
    // 				} else{
    // 					alert(res.message)
    // 				}
    // 			}
    // 			,error: function(){
    // 				//请求异常回调
    // 			}
    // 		});
    // 	});
    
//         var imageUpload = $('.imageupload').val()
//         layui.use('upload', function() {
// 		var $ = layui.jquery,
// 			upload = layui.upload; //声明上传组件

// 		//文件列表示例
// 		var num = "";
// 		var show = "";
// 		var demoListView = $('#demoList'), //上传表格的详情
// 			uploadListIns = upload.render({ //渲染
// 				elem: '#cfile', //绑定选择文件按钮
// 				url: '<?php echo e(url('api/uploadNFT')); ?>',
// 				accept: 'file', //允许上传文件类型
// 				multiple: false, //是否允许多文件上传开关
// 				auto: false, //是否自动上传，true就是选择完文件之后自动上传
// 				bindAction: '#uploadBegin', //绑定开始上传按钮，如果自动上传则不需要这个按钮
// 				choose: function(obj) {
// 					var files = this.files = obj.pushFile(); //将每次选择的文件追加到文件队列
// 					//读取本地文件
// 					obj.preview(function(index, file, result) {
// 						var tr = $(['<tr id="upload-' + index + '">', '<td id="filename">' + file
// 							.name + '</td>', '<td>' + (file.size / 1014).toFixed(1) +
// 							'kb</td>', '<td>等待上传</td>', '<td>',
// 							'<button class="layui-btn layui-btn-xs layui-btn-danger demo-delete">删除</button>',
// 							'</td>', '</tr>'
// 						].join(''));

// 						//删除
// 						tr.find('.demo-delete').on('click', function() {
// 							delete files[index]; //删除对应的文件
// 							tr.remove();
// 							uploadListIns.config.elem.next()[0].value =
// 							''; //清空 input file 值，以免删除后出现同名文件不可选
// 						});
// 						demoListView.append(tr);
// 					});
// 				},
// 				before: function() { //这里暂时规定一次只能上传一个文件
// 					var filenum = $("tr").length;
// 					var file = $("#filename").text();
// 					var ext = file.slice(file.lastIndexOf(".") + 1).toLowerCase();
// 					if (filenum > 2) {
// 						layer.msg("一次只能上传一个文件");
// 						return false;
// 					}
// 					if (file == null || file == "") {
// 						layer.msg("请选择文件");
// 						return false;
// 					}
// 					// 		else if (("xls" != ext)&&("xlsx" != ext) ) {  
// 					// 		    console.log(1211221)
// 					// 	        layer.msg("所选择文件不规范");  
// 					// 	        return false; 
// 					// 	    }
// 				},
// 				done: function(res, index, upload) {
// 					show = layer.msg('上传中，请稍候', {
// 						icon: 16,
// 						time: true,
// 						shade: 0.8
// 					});
// 					if (res.type == 'ok') { //上传成功
// 					    let imageUpload = res.message;
// 						$('.imageupload').val(imageUpload)
// 						$("#imageupload_img").show()
// 					    $("#imageupload_img").attr("src",res.message)
// 						var tr = demoListView.find('tr#upload-' + index),
// 							tds = tr.children();
// 						tds.eq(2).html('<span style="color: #5FB878;">上传成功</span>');
// 						tds.eq(3).html(''); //清空操作
// 						return delete this.files[index]; //删除文件队列已经上传成功的文件
// 					}
// 					this.error(index, upload, res);
// 					layer.close(show);
// 				},
// 				error: function(index, upload, res) {
// 				    layer.msg(res.message);
// 					var tr = demoListView.find('tr#upload-' + index),
// 						tds = tr.children();
// 					tds.eq(2).html('<span style="color: #FF5722;">上传失败:' + res.msg + '</span>');
// 					tds.eq(3).find('.demo-reload').removeClass('layui-hide'); //显示重传
// 				}
// 			});
// 		//这里在点击上传是添加了一个文件类型的判断
// 		// 	$(window).one("resize",function(){
// 		// 		$(".import").click(function(){//上传按钮
// 		// 			var file=document.getElementById("teacherFile").value; 
// 		// 			var ext = file.slice(file.lastIndexOf(".")+1).toLowerCase();  
// 		// 			if(file==null || file ==""){
// 		// 				layer.msg("请选择文件");  
// 		// 			}
// 		// 			else if (("xls" != ext)&&("xlsx" != ext) ) {  
// 		// 			    console.log(1211221)
// 		// 		        layer.msg("所选择文件不规范");  
// 		// 		        return false;  
// 		// 		    }
// 		// 			else {
// 		// 				layer.msg("选择文件规范");  
// 		// 			}
// 		// 		})

// 		// 	}).resize();
// 	    })
        
                
        
    </script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin._layoutNew', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>