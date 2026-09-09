<meta charset="utf-8" />
        <link rel="stylesheet" href="../kindeditor/themes/default/default.css" />
        <link rel="stylesheet" href="../kindeditor/plugins/code/prettify.css" />
        <script charset="utf-8" src="../kindeditor/kindeditor.js?v=20251217"></script>
        <script charset="utf-8" src="../kindeditor/lang/zh_CN.js"></script>
        <script charset="utf-8" src="../kindeditor/plugins/code/prettify.js"></script>
        <script>
var kindeditorSeted=false;
if(!kindeditorSeted){
	function upload(file,editor1){
	
	    var formData = new FormData();	
	    if(file.name=="image.png")
		formData.append("imgFile",file,"img"+(new Date().getTime())+".png");//name,value
	    else
		formData.append("imgFile",file);//name,value
	    //用jquery Ajax 上传二进制数据
	    $.ajax({
		url : '/kindeditor/php/upload_json.php?dir=image',
		type : 'POST',
		data : formData,
		// 告诉jQuery不要去处理发送的数据
		processData : false,
		// 告诉jQuery不要去设置Content-Type请求头
		contentType : false,
		dataType:"json",
		beforeSend:function(){
		    //console.log("正在进行，请稍候");
		},
		success : function(responseStr) {
		    //上传完之后，生成图片标签回显图片，假定服务器返回url。
		    var src = responseStr.url;
		    var imgTag = "<img src='"+src+"' width='486px' border='0'/>";
		    if(src.endsWith('.mp3')) 
			    imgTag="<audio controls src='"+src+"'></audio>";
		    if(src.endsWith('.pdf')) 
			    imgTag="<iframe src='"+src+"'  width=\"100%\" height=\"800px\"  ></iframe>";
		    if(src.endsWith('.mp4')) 
			    imgTag="<video controls  width=\"68%\" ><source src='"+src+"' type='video/mp4'></video>";
		    //console.info(imgTag);
		    //kindeditor提供了一个在焦点位置插入HTML的函数，调用此函数即可。
		    editor1.insertHtml(imgTag);


		},
		error : function(responseStr) {
		    console.log("error:"+responseStr);
		}
	    });
	}


        // 原写法 $(document).ready(setTimeout(...,100)) 会立即执行 setTimeout 并把定时器ID
        // 传给 jQuery.ready, 实际等于"脚本执行后100ms"碰运气初始化; 首次加载(冷缓存)时
        // 常早于页面就绪, 编辑框变纯文本框。改为 DOM 就绪事件驱动 + 目标 textarea 缺失重试。
        var keTries = 0;
        function keInitEditor(){
                KindEditor.ready(function(K) {
                        if (!document.querySelector('textarea[class="kindeditor"]')) {
                                if (++keTries < 50) setTimeout(keInitEditor, 200);
                                return;
                        }
                        let editor1 = K.create('textarea[class="kindeditor"]', {
                                width : '100%',
                                cssPath : '../kindeditor/plugins/code/prettify.css',
                                uploadJson : '../kindeditor/php/upload_json.php',
                                fileManagerJson : '../kindeditor/php/file_manager_json.php',
                                allowFileManager : false,
                                allowImageRemote: true,
                                filterMode:false,
				pasteType:1,    // 1 纯文本粘贴 2 HTML粘贴
                                cssData: 'body { font-family:"Consolas";font-size: 18px;line-height:150% }  ',
<?php if(isset($OJ_MARKDOWN)&&$OJ_MARKDOWN)
                                echo "designMode:false,";
?>

                                afterCreate : function() {
                                        var self = this;
                                        K.ctrl(document, 13, function() {
                                                self.sync();
                                        });
                                        K.ctrl(self.edit.doc, 13, function() {
                                                self.sync();
                                        });
					        var editerDoc = this.edit.doc;//得到编辑器的文档对象
						//监听粘贴事件, 包括右键粘贴和ctrl+v
						$(editerDoc).bind('paste', null, function (e) {
						    var ele = e.originalEvent.clipboardData.items;
						    for (var i = 0; i < ele.length; ++i) {
							//判断文件类型
							if ( ele[i].kind == 'file' && (ele[i].type.indexOf('image/') !== -1 || ele[i].type.endsWith("pdf") ) ) {
							    var file = ele[i].getAsFile();//得到二进制数据
							    console.log(file);
							    upload(file,self);
							    //创建表单对象，建立name=value的表单数据。
							    if(ele.length==1||ele[i-1].type=="text/html") return false;
							}else{
								console.log(ele[i].type);
							}

						    }
						}).bind("drop",null,function(e){
							e.originalEvent.preventDefault();
							e.originalEvent.stopPropagation();
							let dt=e.originalEvent.dataTransfer;
							if(dt.files != undefined ){
							    for(const file of dt.files){
							    	console.log(file);
								upload(file,self);
							    }
							}
						});
                                }
                                ,
				afterBlur: function() {
					var self = this;
					self.sync();
				}
				,
                                afterChange: function() {
                                        var self = this;
                                        self.sync();
                                        if( typeof sync === "function")         sync();
                                }
                        });
                        prettyPrint();
                });
        }
        if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', keInitEditor);
        } else {
                keInitEditor();
        }
         kindeditorSeted=true;
}

        </script>

