

<?php $__env->startSection('page-head'); ?>
    <style>
        .layui-form-label {
            width:90px;
        }
        .layui-input-block {
            margin-left: 120px;
        }
        .layui-fluid .layui-table-cell  {
            line-height: 20px;
            padding: 5px 5px ;
            position: relative;
            box-sizing: border-box;
            text-align: center;
            overflow: visible;
            height:auto;
            overflow:visible;
            text-overflow:inherit;
        	white-space:normal;
        }
        .reddot {
            position: absolute;
            right:5%;
            color: red;
        }
        .layui-textarea{
            height: 80px;
            min-height: 80px;
        }
    </style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('page-content'); ?>
    <form class="layui-form" action="">
       
        <?php if(!empty($currency)): ?>
            <div class="layui-form-item">
            <label class="layui-form-label"><span class="reddot">*</span>交易币种</label>
            <div class="layui-input-block">
                <select name='currency'>
                <?php $__currentLoopData = $currency; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option class='layui-option' value="<?php echo e($value['id']); ?>" ><?php echo e($value['name']); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            </div>
        <?php endif; ?>
                
        <div class="layui-form-item">
            <label class="layui-form-label"><span class="reddot">*</span>名称</label>
            <div class="layui-input-block">
                <input type="text" name="name" autocomplete="off" placeholder="" class="layui-input" value="" lay-verify="required">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label"><span class="reddot">*</span>交易类型</label>
            <div class="layui-input-block" >
                <select name='pay_type' id="select_type" lay-filter="select_type">
                    <option value='1' class='layui-option'>一口价</option>
                    <option value='2' class='layui-option'>竞拍</option>
                    <option value='3' class='layui-option'>盲盒</option>
                </select>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label"><span class="reddot">*</span>稀有度</label>
            <div class="layui-input-block">
                <select name='rarity' id="rarity" lay-filter="rarity">
                    <option value='N' class='layui-option'>N</option>
                    <option value='R' class='layui-option'>R</option>
                    <option value='SR' class='layui-option'>SR</option>
                </select>
            </div>
        </div>
        <!--<div class="layui-form-item" id="bind_select">-->
        <!--    <label class="layui-form-label"><span class="reddot">*</span>该稀有度对应的盲盒</label>-->
        <!--    <div class="layui-input-block">-->
        <!--        <select name=''>-->
        <!--            <option value=''>请选择</option>-->
        <!--        </select>-->
        <!--    </div>-->
        <!--</div>-->
        <div class="layui-form-item" id="bind_select">
            <label class="layui-form-label"><span class="reddot">*</span>该稀有度对应的盲盒</label>
            <div class="layui-input-block">
                <select name='rarity_house_id' id="bind_select_val">
                    <!--<option value=''>请选择</option>-->
                    <!--<option class='layui-option' value="" ></option>-->
                </select>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label"><span class="reddot">*</span>价格</label>
            <div class="layui-input-block">
                <input type="text" name="price" autocomplete="off" placeholder="" class="layui-input" value="" lay-verify="required">
            </div>
        </div>
        <div class="layui-form-item" id="auction" lay-filter="auction">
            <label class="layui-form-label"><span class="reddot">*</span>竞拍每次加价</label>
            <div class="layui-input-block">
                <input type="text" id="auction_val" name="per_increase" autocomplete="off" placeholder="" class="layui-input" value="">
            </div>
        </div>
        <div class="layui-form-item" id="margin_price" lay-filter="auction">
            <label class="layui-form-label"><span class="reddot">*</span>保证金</label>
            <div class="layui-input-block">
                <input type="text" id="margin_price_val" name="margin" autocomplete="off" placeholder="" class="layui-input" value="">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label"><span class="reddot">*</span>作者ID</label>
            <div class="layui-input-block">
                <input type="text" name="author" autocomplete="off" placeholder="" class="layui-input" value="" lay-verify="required">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label"><span class="reddot">*</span>文件类型</label>
            <div class="layui-input-block">
                 <select name='type'>
                    <option value='1' class='layui-option'>图片</option>
                    <option value='4' class='layui-option'>视频</option>
                </select>
            </div>
        </div>
        
        <div class="layui-form-item">
            <label class="layui-form-label"><span class="reddot">*</span>文件上传</label>
            <div class="layui-upload"  style="border:1px solid #e6e6e6;border-radius:4px;padding:10px 0;margin-top:10px;margin-left:120px;"> 
                <button type="button" class="layui-btn layui-btn-normal" id="cfile" style="margin-left:30px">选择文件</button> <br/>
                    <div><img src="" id="imageupload_img"  style="display: none;cursor:pointer;max-width: 200px;height: auto;margin-top: 10px;margin-left:30px;" onclick="showBigImage(this)" /></div>
                    <div><video src="" id="imageupload_video"  style="display: none;cursor:pointer;max-width: 200px;height: auto;margin-top: 10px;margin-left:30px;" onclick="showBigVideo(this)" /></div>
                    <div class="layui-upload-list">
                        <table class="layui-table">
                            <thead>
                                <tr>
                                    <th>文件名</th>
                                    <th>大小</th>
                                    <th>状态</th>
                                    <th>操作</th>
                                </tr> 
                            </thead>
                            <input name="image" type="hidden" id="imageupload_val" value="" lay-verify="required">
                            <tbody id="demoList"></tbody> 
                        </table>
                    </div>
                <button type="button" class="layui-btn" id="uploadBegin" style="margin-left:30px">开始上传</button>
            </div> 
        </div>
  <!--      <div class="layui-form-item layui-form-text">-->
		<!--	<label class="layui-form-label">文件上传</label>-->
		<!--	<div class="layui-input-block">-->
		<!--		<button class="layui-btn" type="button" id="upload_bindbox">选择文件</button>-->
		<!--		<br>-->
		<!--		<img src="<?php if(!empty($news->bindbox_upload)): ?><?php echo e($news->bindbox_upload); ?><?php endif; ?>" id="upload_bindbox_upload" class="bindbox_upload" style="display: <?php if(!empty($news->bindbox_upload)): ?><?php echo e("block"); ?><?php else: ?><?php echo e("none"); ?><?php endif; ?>;max-width: 200px;height: auto;margin-top: 5px;">-->
		<!--		<input type="hidden" name="image" id="bindbox_upload" value="<?php if(!empty($news->bindbox_upload)): ?><?php echo e($news->bindbox_upload); ?><?php endif; ?>">-->
		<!--	</div>-->
		<!--</div>-->
		
        <!--<div class="layui-form-item">-->
        <!--    <label class="layui-form-label">数量</label>-->
        <!--    <div class="layui-input-block">-->
        <!--        <input type="text" name="text" autocomplete="off" placeholder="" class="layui-input" value="" lay-verify="required">-->
        <!--    </div>-->
        <!--</div>-->
        
		<!--<div class="layui-form-item">-->
  <!--          <label class="layui-form-label">是否显示</label>-->
  <!--          <div class="layui-input-block">-->
  <!--              <select name='type'>-->
  <!--                  <option value='1' class='layui-option'>可用</option>-->
  <!--                  <option value='0' class='layui-option'>不可用</option>-->
  <!--              </select>-->
  <!--          </div>-->
  <!--      </div>-->
		<div class="test-table-reload-btn" style="margin-bottom: 10px;">
            <label class="layui-form-label"><span class="reddot">*</span>开始时间</label>
            <div class="layui-inline">
                    <input class="layui-input test-laydate-item" name="start_time" placeholder="年 - 月 - 日 &nbsp; -- : -- : --" id="start_time" autocomplete="off" lay-verify="required">
                </div>
            <div class="layui-inline" style="width: 80px;text-align: right">结束时间</div>
                <div class="layui-inline">
                    <input class="layui-input test-laydate-item" name="end_time" placeholder="年 - 月 - 日 &nbsp; -- : -- : --" id="end_time" autocomplete="off" lay-verify="required">
            </div>
        </div>
        
        <div class="layui-form-item">
            <label class="layui-form-label">关于艺术家</label>
            <div class="layui-input-block">
                <textarea type="text" name="author_description" autocomplete="off" placeholder="" class="layui-textarea" value="" rows="10" cols="100"></textarea>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">资产详情</label>
            <div class="layui-input-block">
                <textarea type="text" name="description" autocomplete="off" placeholder="" class="layui-textarea" value="" rows="10" cols="100"></textarea>
            </div>
        </div>
        <div class="layui-form-item">
            <div class="layui-input-block">
                <button class="layui-btn" lay-submit="bindBoxsubmit" lay-filter="bindBoxsubmit">立即提交</button>
                <button type="reset" class="layui-btn layui-btn-primary">重置</button>
            </div>
        </div>
    </form>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
    <script>
        // 若选择拍卖显示加价和保证金
        $("#auction").hide();
        $("#margin_price").hide();
        $("#bind_select").hide();
        
        layui.use(['layer', 'jquery', 'form'], function () {
			var layer = layui.layer,
					$ = layui.jquery,
					form = layui.form;
 
			form.on('select(select_type)', function(data){
                if(data.value == '1'){
                    $("#auction").hide();
                    $("#auction_val").val("");
                    
                    $("#margin_price").hide();
                    $("#margin_price_val").val("");
                    
                    $("#bind_select").hide();
                    $("#bind_select_val").val("");
                    form.render('select');
                }if(data.value == '2'){
                    $("#auction").show();
                    
                    $("#margin_price").show();
                    
                    $("#bind_select").hide();
                    $("#bind_select_val").val("");
                    form.render('select');
                }if(data.value == '3'){
                    getRarity_house(form);
                    
                    $("#auction").hide();
                    $("#auction_val").val("");
                    
                    $("#margin_price").hide();
                    $("#margin_price_val").val("");
                    
                    $("#bind_select").show();
                    form.render('select');
                    form.on('select(rarity)', function(data){
                        getRarity_house(form);
                    });
                }
            });
            
            
		});
        
        layui.use(['form','laydate'],function () {
            var form = layui.form
                ,$ = layui.jquery
                ,laydate = layui.laydate
                ,index = parent.layer.getFrameIndex(window.name);
            //监听提交
            form.on('submit(bindBoxsubmit)', function(data){
                var data = data.field;
                delete data.file;
                // data.start_time=data.start_time.replace("T"," ")
                // data.end_time=data.end_time.replace("T"," ")
                $.ajax({
                    url:'<?php echo e(url('admin/bind_box/addNFT')); ?>'
                    ,type:'post'
                    ,dataType:'json'
                    ,data : data
                    ,success:function(res){
                        if(res.type=='ok'){
                            layer.msg('操作成功！');
                        }if(res.type=='error'){
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
        
        //获取盲盒种类
        function getRarity_house(form){
            var val = $("#rarity option:selected").val()
            $.ajax({
                url:'<?php echo e(url('/admin/bind_box/getRarityHouse')); ?>'
                ,type:'post'
                ,dataType:'json'
                ,data: { rarity: val}
                ,success:function(res){
                    var bind_select_val = document.getElementById('bind_select_val')
                    var str = ''
                    res.rarity_house.forEach(function(item) {
                        str += "<option class='layui-option' value=' " + item.id + "' >"+ '名称：' + item.name + "</option>"    
                    })
                    bind_select_val.innerHTML = "<option value=''>请选择</option>" + str
                    console.log('vvvvv', bind_select_val, res.rarity_house)
                    form.render('select');
            
                    // const value = res.message.data[0]
                    // $("#rarity_house").val(value.price)
                }
            });
        }
        
        function showBigImage(e) {
            parent.layer.open({
                type: 1,
                title: false,
                closeBtn: 0,
                shadeClose: true, 
                area: [$(e).width + 'px', $(e).height + 'px'], 
                content: "<img style='padding:12px;max-width:1400px;max-height:780px' src=" + $(e).attr('src') + " />"
            });
        }
        function showBigVideo(e) {
            parent.layer.open({
                type: 1,
                title: '视频预览',
                closeBtn: 0,
                shadeClose: true, 
                offset: 'auto',
                area: 'auto',
                content: "<video controls='controls' style='padding:12px;max-width:1400px;max-height:780px' src=" + $(e).attr('src') + " />"
            });
        }
        
        
        
        var imageUpload = $('#uploadBegin').val()
        layui.use('upload', function() {
		var $ = layui.jquery,
			upload = layui.upload; //声明上传组件

		//文件列表示例
		var num = "";
		var show = "";
		var demoListView = $('#demoList'), //上传表格的详情
			uploadListIns = upload.render({ //渲染
				elem: '#cfile', //绑定选择文件按钮
				url: '<?php echo e(url('api/uploadNFT')); ?>',
				accept: 'file', //允许上传文件类型
				multiple: false, //是否允许多文件上传开关
				auto: false, //是否自动上传，true就是选择完文件之后自动上传
				bindAction: '#uploadBegin', //绑定开始上传按钮，如果自动上传则不需要这个按钮
				choose: function(obj) {
					var files = this.files = obj.pushFile(); //将每次选择的文件追加到文件队列
					//读取本地文件
					obj.preview(function(index, file, result) {
						var tr = $(['<tr id="upload-' + index + '">', '<td id="filename">' + file
							.name + '</td>', '<td>' + (file.size / 1014).toFixed(1) +
							'kb</td>', '<td>等待上传</td>', '<td>',
							'<button class="layui-btn layui-btn-xs layui-btn-danger demo-delete">删除</button>',
							'</td>', '</tr>'
						].join(''));

						//删除
						tr.find('.demo-delete').on('click', function() {
							delete files[index]; //删除对应的文件
							tr.remove();
							uploadListIns.config.elem.next()[0].value =
							''; //清空 input file 值，以免删除后出现同名文件不可选
						});
						demoListView.append(tr);
						
						var filenum = $("tr").length;
    					 if(filenum >= 2){
    					    $("tr").first().remove()
    					}
						
					});
				},
				before: function() { //这里暂时规定一次只能上传一个文件
				    
					var filenum = $("tr").length;
					 console.log(123123,filenum)
					var file = $("#filename").text();
					var ext = file.slice(file.lastIndexOf(".") + 1).toLowerCase();
					if (filenum > 2) {
						layer.msg("一次只能上传一个文件");
						return false;
					}
					if (file == null || file == "") {
						layer.msg("请选择文件");
						return false;
					}
					
					
					// 		else if (("xls" != ext)&&("xlsx" != ext) ) {  
					// 		    console.log(1211221)
					// 	        layer.msg("所选择文件不规范");  
					// 	        return false; 
					// 	    }
				},
				done: function(res, index, upload) {
					var loading = parent.layer.load(1, {
                        shade: 0.2,
                        time: 500
                    });
					if (res.type == 'ok') { //上传成功
					    let imageUpload = res.message;
					   // console.log(1111111,imageUpload)
						
						var reg = /\.(png|PNG|jpg|JPG|gif|GIF|jpeg|JPEG|webp|WEBP)$/;
                        var isImgVideo = reg.test(imageUpload);
						if(isImgVideo){
						    $('#imageupload_val').val(imageUpload)
						    $("#imageupload_video").hide();
						    $("#imageupload_img").show();
					        $("#imageupload_img").attr("src",res.message)
						}else{
						    $('#imageupload_val').val(imageUpload)
						    $("#imageupload_img").hide();
					        $("#imageupload_video").show();
					        $("#imageupload_video").attr("src",res.message)
					    }
						
						var tr = demoListView.find('tr#upload-' + index),
							tds = tr.children();
						tds.eq(2).html('<span style="color: #5FB878;">上传成功</span>');
						tds.eq(3).html(''); //清空操作
						return delete this.files[index]; //删除文件队列已经上传成功的文件
					}
					this.error(index, upload, res);
					layer.close(loading);
				},
				error: function(index, upload, res) {
				    layer.msg(res.message);
					var tr = demoListView.find('tr#upload-' + index),
						tds = tr.children();
					tds.eq(2).html('<span style="color: #FF5722;">上传失败:' + res.msg + '</span>');
					tds.eq(3).find('.demo-reload').removeClass('layui-hide'); //显示重传
				}
			});
		//这里在点击上传是添加了一个文件类型的判断
		// 	$(window).one("resize",function(){
		// 		$(".import").click(function(){//上传按钮
		// 			var file=document.getElementById("teacherFile").value; 
		// 			var ext = file.slice(file.lastIndexOf(".")+1).toLowerCase();  
		// 			if(file==null || file ==""){
		// 				layer.msg("请选择文件");  
		// 			}
		// 			else if (("xls" != ext)&&("xlsx" != ext) ) {  
		// 			    console.log(1211221)
		// 		        layer.msg("所选择文件不规范");  
		// 		        return false;  
		// 		    }
		// 			else {
		// 				layer.msg("选择文件规范");  
		// 			}
		// 		})

		// 	}).resize();
	})
                
        
    </script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('admin._layoutNew', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>