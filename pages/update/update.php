<?php
	function my_dir($dir) {
	    $files = array();
	    if(@$handle = opendir($dir)) { //注意这里要加一个@，不然会有warning错误提示：）
	        while(($file = readdir($handle)) !== false) {
	            if(!preg_match('/^\..*/',$file)) { //排除隐藏目录；
	                if(is_dir($dir."/".$file)) { //如果是子文件夹，就进行递归
	                    $files[$file] = my_dir($dir."/".$file);
	                } else { //不然就将文件的名字存入数组；
	                    $files[] = $file;
	                }
	 
	            }
	        }
	        closedir($handle);
	        return $files;
	    }
	}
	$files = my_dir('../../files/教学/视频');
	foreach ($files as $key => $value) {
		echo $key;
	}
	echo '<pre>';
	print_r($files);
	echo '</pre>';

	echo '
	<div class="scroll_end"></div>
	<div id="left_layer" style="position:fixed; top:100px; right:50px;">
  	<img src="../../images/find.jpg"><br>
  	<a href="javascript:;" onclick="javascript:document.getElementById(',"\'",'left_layer',"\'",').style.display=',"\'",none,"\'",';">关闭</a>
	</div>';
	
?>