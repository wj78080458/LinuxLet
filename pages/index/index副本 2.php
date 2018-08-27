<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>小程序学习平台</title>
    <link href="index.css" rel="stylesheet" type="text/css"/>
    <script type="text/javascript" src="jquery.js" ></script>
    <script>
    	$(function(){
	        var tags = 0;

			var dataa = sessionStorage.getItem('openId');
			if(dataa != null){
				openSonNav(dataa);
			}

	        $(".navSpan").click(function(){
	            var thisTags = $(this).data('tags');

	            //判断是否已经打开一个子导航栏
	            if(isSonNavOpen()){
	                //判断是否和当前点击的是同一个
	                if(tags == thisTags){        //是同一个，关闭该子导航 
	                    closeSonNav(tags);
	                }
	                else{                                 //不是同一个, 关闭旧的并打开一个新的
	                    closeSonNav(tags);
	                    openSonNav(thisTags);
	                    
	                }
	            }
	            else{ //当前没有子导航打开，打开一个新的
	                openSonNav(thisTags);
	            }

	        });
	        
	        $(".menu_titl").click(function(){
	        	var thisTags = $(this).data('tag');
	        });

	        //判断是否已经打开一个子导航栏
	        function isSonNavOpen(){
	            if(tags != 0){       //已经打开
	                return true;
	            }
	            return false;
	        }

	        //打开一个子导航
	        function openSonNav(thisTags){
	            var sonNavsDiv = $("div[data-tags-div="+ thisTags +"]");

	            //显示子导航栏
	            sonNavsDiv.css("display", "block");
	            tags = thisTags;
	            sessionStorage.setItem('openId', tags);
	            indicatorId = "indicate" + thisTags;
	           	 indicator = document.getElementById(indicatorId);
	             indicator.style.transform = "rotate(90deg)"; 
	            // document.getElementById('left_layer').style.display='none';
	        }

	        //关闭一个子导航
	        function closeSonNav(thisTags){
	            var sonNavsDiv = $("div [data-tags-div="+ thisTags +"]");

	            sonNavsDiv.css("display", "none");  
	            //关闭后把标记清0， 否则打开一个后，一直是已经打开状态
	            tags = 0;
	             indicatorId = "indicate" + thisTags;
	             indicator = document.getElementById(indicatorId);
	             indicator.style.transform = "rotate(0deg)"; 
	        }
	        
	        //设置cookie
	        function setCookie(name, value){
	        	var Days = 30;
				var exp = new Date();
				exp.setTime(exp.getTime() + Days*24*60*60*1000);
				document.cookie = name + "="+ escape (value) + ";expires=" + exp.toGMTString();
	        }
	        //获取cookie
	        function getCookie(name){
	        	
				var arr, reg=RegExp("(^| )"+name+"=([^;]*)(;|$)");
				if(arr=document.cookie.match(reg))
					return unescape(arr[2]);
				else
					return null;
					
	        }
    });
	</script>
     
     
    <script type="text/javascript">
		var itemHeight = 40;
		var dividerHeight = 1;

		function openMenu(obj) {
			
			menuTitleId = obj.id;
			menuId = "menu" + menuTitleId.substring(10);
			indicatorId = "indicator" + menuTitleId.substring(10);

			menu = document.getElementById(menuId);
			indicator = document.getElementById(indicatorId);
			height = menu.style.height;

			if (height == "0px" || height == "") {
				childAmount = menu.getElementsByTagName('div').length;
			    dividerAmount = menu.getElementsByTagName('li').length;
			    height = childAmount * itemHeight + dividerAmount * dividerHeight;
			    menu.style.height = height + "px";
			    indicator.style.transform = "rotate(180deg)";
			} else {
			    menu.style.height = "0px";
			    indicator.style.transform = "rotate(0deg)"; 
			}
		}
		function onClose(obj){
    		document.getElementById('left_layer').style.display='none';

    	}
	</script>
</head>
<body>
	
	<?php
		error_reporting(E_ERROR); 
		//ini_set("display_errors", "On");
		//error_reporting(E_ALL);

		class ItemData{
			var $keyword;
			var $abstract;
			var $data;	
			var $chapter;
			var $title;
		}
		class ListData{
			var $data;
			var $itemArrLen;
			var $itemArr;	
		}
		class SubData{
			var $data;
			var $listArrLen;
			var $listArr;	
		}
		class FoldData{
			var $data;
			var $subArrLen;
			var $subArr;
		}
		
		class MatchRes{
			var $level;
			var $foldId;
			var $subId;
			var $listId;
			var $itemId;
			var $keyword;
			var $abstract;
			var $category;
		}
	
	?>
	
	<?PHP
		/*开始解析xml数据*/
		header("Content-type:text/html; Charset=utf-8");
		$url = "../../files/database/fold.xml";
		 
		//  加载XML内容
		$content = file_get_contents($url);
		$content = get_utf8_string($content);
		$dom = DOMDocument::loadXML($content);
		 
		 //全局数组，保存目录结构
		$foldArr = array();
		$chapterFlag = array();
		//提取xml数据
		get_all_data($dom);
		
		
		
		/**************************************************************************************************/		 
		function get_utf8_string($content) {    //  将一些字符转化成utf8格式
		    $encoding = mb_detect_encoding($content, array('ASCII','UTF-8','GB2312','GBK','BIG5'));
		    return  mb_convert_encoding($content, 'utf-8', $encoding);
		}
		
		function get_item_data($tag){
			$itemData = new ItemData;
			$itemData->data = $tag->getAttribute("data");
			$itemData->keyword = $tag->getElementsByTagName("keyword")->item(0)->nodeValue;
			$itemData->abstract = $tag->getElementsByTagName("abstract")->item(0)->nodeValue;
			$itemData->chapter = $tag->getAttribute("chapter");
			$itemData->title = $tag->getAttribute("title");
			return $itemData;
		}
		
		function get_list_data($tag){
			$listData = new ListData;
			$listData->data = $tag->getAttribute("data");
			$items = $tag->getElementsByTagName("item");
			$itemArr = array();
			for($i=0; $i<$items->length; $i++){
				$temp = get_item_data($items->item($i));
				array_push($itemArr, $temp);
			}
			$listData->itemArr = $itemArr;
			$listData->itemArrLen = $items->length;
			return $listData;
		}
		
		function get_sub_data($tag){
			$subData = new SubData;
			$subData->data = $tag->getAttribute("data");
			$lists = $tag->getElementsByTagName("list");
			$listArr = array();
			for($i=0; $i<$lists->length; $i++){
				$temp = get_list_data($lists->item($i));
				array_push($listArr, $temp);
			}
			$subData->listArr = $listArr;
			$subData->listArrLen = $lists->length;
			return $subData;
		}
		
		function get_fold_data($tag){
			$foldData = new FoldData;
			$foldData->data = $tag->getAttribute("data");
			$subs = $tag->getElementsByTagName("subject");
			$subArr = array();
			for($i=0; $i<$subs->length; $i++){
				$temp = get_sub_data($subs->item($i));
				array_push($subArr, $temp);
			}
			$foldData->subArr = $subArr;
			$foldData->subArrLen = $subs->length;
			return $foldData;
		}
		
		function get_all_data($tag){
			$data = $tag->getElementsByTagName("fold");
			global $foldArr;
			for($i=0; $i<$data->length; $i++){
				$foldData = get_fold_data($data->item($i));
				array_push($foldArr, $foldData);
			}
		}
		
		
		/**************************************************************************************************/
		//得到视频的章节数目
		function get_chapter($id){
			global $foldArr, $chapterFlag;
			
			array_push($chapterFlag, 0);
			//计算得到章节分界点
			for($i=0; $i<$foldArr[0]->subArr[0]->listArr[$id]->itemArrLen-1; $i++){
				if($foldArr[0]->subArr[0]->listArr[$id]->itemArr[$i]->chapter != $foldArr[0]->subArr[0]->listArr[$id]->itemArr[$i+1]->chapter){
					array_push($chapterFlag, $i+1);
				}
			}
			array_push($chapterFlag, $foldArr[0]->subArr[0]->listArr[$id]->itemArrLen);
			$chapter = array();
			for($i=1; $i<count($chapterFlag); $i++){
				array_push($chapter, $chapterFlag[$i]-$chapterFlag[$i-1]);
			}
			return $chapter;
		}
	?>
	
	<?php
		$lessonImg = ["../../images/lesson_program.jpg","../../images/lesson_game.jpg","../../images/lesson_css.jpg","../../images/lesson_js.jpg"];
		//页面参数，页面id
		$foldId = $_GET['foldId'];
		$subId = $_GET['subId'];
		$listId = $_GET['listId'];
		$itemId = $_GET['itemId'];
		
		$prevListId = $listId;
		$nextListId = $listId;
		$prevItemId = $itemId;
		$nextItemId = $itemId;
		
		//页面参数，搜索内容和id
		$searchCon = $_GET['searchCon'];
		$searchId = $_GET['searchId'];
		
		//页面类型
		$pageType;
		get_page_type();
		
		//名字
		$firstName = $foldArr[$foldId]->data;
		$secondName = $foldArr[$foldId]->subArr[$subId]->data;
		$thirdName = $foldArr[$foldId]->subArr[$subId]->listArr[$listId]->data;
		$forthName = $foldArr[$foldId]->subArr[$subId]->listArr[$listId]->itemArr[$itemId]->data;
		
		$matchArr = array();
		$matchArrCategory = array();
		
		//判断页面的类型
		function get_page_type(){
			global $foldId, $listId, $searchCon, $searchId, $pageType;
			if($searchId == null){
				if($foldId == null){
					$pageType = 0;
				}else{
					if($foldId == 0){
						if($listId ==  null)
							$pageType = 1;
						else
							$pageType = 2;
					}else{
						if($listId == null)
							$pageType = 4;
						else
							$pageType = 5;
					}
				}
			}else{
				if($searchCon == null)
					$pageType = 0;	//搜索框没有内容，回到主页
				else
					$pageType = 6;	//搜索框有内容，显示搜索结果
			}
		}
		
		/**************************************************************************************************/
		//计算上一页和下一页
		function getPageNumber(){
			global 	$foldId,$subId,$listId,$itemId,$foldArr;
			global $prevListId,$prevItemId;
			global $nextListId,$nextItemId;
			if($itemId>0 && $itemId<$foldArr[$foldId]->subArr[$subId]->listArr[$listId]->itemArrLen-1){
				$prevItemId = $itemId-1;
				$nextItemId = $itemId+1;	
			}else if($itemId==0 && $foldArr[$foldId]->subArr[$subId]->listArr[$listId]->itemArrLen>1){
				$nextItemId = $itemId+1;
				getPrevListId();
				$prevItemId = $foldArr[$foldId]->subArr[$subId]->listArr[$prevListId]->itemArrLen-1;
				if($listId == 0)
					$prevItemId=0;
			}else if($itemId==$foldArr[$foldId]->subArr[$subId]->listArr[$listId]->itemArrLen-1 && $itemId>0){
				$prevItemId = $itemId-1;
				getNextListId();
				$nextItemId = 0;
				if($listId == $foldArr[$foldId]->subArr[$subId]->listArrLen-1)
					$nextItemId = $itemId;
			}else if($foldArr[$foldId]->subArr[$subId]->listArr[$listId]->itemArrLen==1){
				getPrevListId();
				getNextListId();
				$prevItemId = $foldArr[$foldId]->subArr[$subId]->listArr[$prevListId]->itemArrLen-1;
				$nextItemId = 0;
			}
		}
		function getPrevListId(){
			global $listId,$prevListId;
			if($listId>0){
				$prevListId = $listId - 1;
			}else if($listId==0){
				$prevListId = 0;
			}
		}
		function getNextListId(){
			global $foldId,$subId,$listId,$foldArr;
			global $nextListId;
			if($listId<$foldArr[$foldId]->subArr[$subId]->listArrLen-1){
				$nextListId = $listId + 1;
			}else if($listId==$foldArr[$foldId]->subArr[$subId]->listArrLen-1){
				$nextListId = $listId;
			}
		}
		
		/**************************************************************************************************/
		//搜索
		function search_data($searchId){
			global $matchArr, $matchArrCategory, $foldArr, $searchCon;
			for($i=0; $i<$foldArr[$searchId]->subArrLen; $i++){
				for($j=0; $j<$foldArr[$searchId]->subArr[$i]->listArrLen; $j++){
					for($k=0; $k<$foldArr[$searchId]->subArr[$i]->listArr[$j]->itemArrLen; $k++){
						$keyword = $foldArr[$searchId]->subArr[$i]->listArr[$j]->itemArr[$k]->keyword;
						$keyArr = explode(" ", $keyword);
						$matchRes = new MatchRes;
						$cnt = 0;
						$category=0;
						for($m=0; $m<count($keyArr); $m++){
							if(stristr($searchCon, $keyArr[$m])!=false){
								$cnt++;
								// if($m==0 || $m==1){
								// 	$cnt++;
								// }	
								if($m==1 || $m==2 || $m==3 || $m==4){
									$cnt+=1;
								}
								if($m==0){$category=1;}							
							}
						}
						if($cnt > 0){
							$matchRes->foldId = $searchId;
							$matchRes->subId = $i;
							$matchRes->listId = $j;
							$matchRes->itemId = $k;
							$matchRes->level = $cnt;
							$matchRes->category = $category;
							$matchRes->keyword = $keyword;
							$matchRes->abstract = $foldArr[$searchId]->subArr[$i]->listArr[$j]->itemArr[$k]->abstract;
							if($matchRes->category == 0)
								array_push($matchArr, $matchRes);
							else
								array_push($matchArrCategory, $matchRes);
						}
					}
				}
			}
		}
		
		//排序,按照搜索匹配的优先级，进行降序排列
		function sort_data(){
			global $matchArr;
			$len = count($matchArr);
			for($i=0; $i<$len-1; $i++){
				for($j=$i+1; $j<$len; $j++){
					if($matchArr[i]->level < $matchArr[$j]->level){
						$temp = $matchArr[$i];
						$matchArr[$i] = $matchArr[$j];
						$matchArr[$j] = $temp;
					}
				}
			}
			global $matchArrCategory;
			$len = count($matchArrCategory);
			for($i=0; $i<$len-1; $i++){
				for($j=$i+1; $j<$len; $j++){
					if($matchArrCategory[i]->level < $matchArrCategory[$j]->level){
						$temp = $matchArrCategory[$i];
						$matchArrCategory[$i] = $matchArrCategory[$j];
						$matchArrCategory[$j] = $temp;
					}
				}
			}
		}
	
	?>
	
	
    <!-- left 侧边栏 start -->
    <div id="left" class="leftt">
    	<?php
    	for($i=0; $i<count($foldArr); $i++){
    		//输出一级目录
    		echo 
    		'<span class="navSpan" data-tags=',$i+1,'>',
    		'<div class="navLink">','<div class="indicate" id="indicate',$i+1,'">></div>',$foldArr[$i]->data,'</div>',
    		'<div class="sonNavsDiv" data-tags-div=',$i+1,'>';
    		
    		//输出二级目录	
    		for($j=0; $j<$foldArr[$i]->subArrLen; $j++)	{
    			echo '<a href="index.php?foldId=',$i,'&subId=',$j,'"><span class="sonNavSpan">',$foldArr[$i]->subArr[$j]->data,'</span></a>';
    		}
    		
    		echo '</div></span>';
    	}
    	?>
    </div>
    <!-- left 侧边栏 end -->
    
    
    
    <!-- right 内容区 end -->
    <div  id="right">
    	<?php
    		if($pageType == 0){
    			//主页， 展示logo，全局搜索
    			echo '
    			<image class="indexLogoImg" src="../../images/main_logo.jpg"/>
    			<div class="indexFindDiv">
    			<form action="index.php" method="GET">
    			<input class="indexFindInput" type="text" name="searchCon" placeholder="搜一下"/>
    			<input class="indexFindImg" type = "image" name="searchId" value="-1" src="../../images/search_global.jpg" onclick ="document.foormName.submit()">
   				</form></div>
   				';
   				
    		}else{
    			
    			
    			//其他页面上方都显示搜索工具
    			echo '
    			<div class="topFindDiv">
    			<a href="index.php"><image class="topFindLogo" src="../../images/main_logo.jpg"/></a>
    			<form class="topFindForm" action="index.php" method="GET">
    			<input class="topFindInput" type="text" name="searchCon" placeholder="点击右侧可以分类搜索哦"/>
    			<input class="topFindImg" type="image" name="searchId" value="1" title="搜索手册" alt="搜索手册" src="../../images/search_datasheet.jpg" onclick ="document.foormName.submit()"/>
   				<input class="topFindImg" type="image" name="searchId" value="2" title="搜索案例" alt="搜索手册" src="../../images/search_case.jpg" onclick ="document.foormName.submit()"/>
   				<input class="topFindImg" type="image" name="searchId" value="3" title="搜索问答区" alt="搜索手册" src="../../images/search_qa.jpg" onclick ="document.foormName.submit()"/>
   				</form>';
   				
   				//实例提供代码下载功能
   				if($foldId==2 && $itemId!=null){
   					echo '<a target="_blank" href="../../files/',$firstName,'/',$secondName,'/',$thirdName,'/',$forthName,'/',$forthName,'.zip','"><image class="downImg" alt="下载代码" title="下载代码" src="../../images/download.jpg"/></a>';
   				}
   				
   				echo '</div><div class="otherConDiv">';
   				
   				if($pageType != 6){
   					echo '<div class="titleDiv">','当前位置：',$firstName,'->',$secondName;
   					if($thirdName != null){
   						echo '->',$thirdName;
   						if($forthName != null){
   							echo '->',$forthName;
   						}
   					}
   					if($pageType == 5){
   						getPageNumber();
   						echo '<div class="pnDiv"><a href="index.php?&foldId=',$foldId,'&subId=',$subId,'&listId=',$prevListId,'&itemId=',$prevItemId,'">上一章</a><a href="index.php?&foldId=',$foldId,'&subId=',$subId,'&listId=',$nextListId,'&itemId=',$nextItemId,'">下一章</a></div>';
   					}
   					echo '</div>';
   				}
   				
   				if($pageType != 5){
   					echo '<div class="mainConDiv">';
   					if($foldArr[$foldId]->subArr[$subId]->listArr[0]->itemArrLen==0 && $pageType!=6){		
   						echo '很抱歉，内容正在更新，即将跳转...';				
   						echo '<script> function goIndex(){location.href="index.php";} window.setTimeout("goIndex()", 2000);</script>';
   					}
   					
   				}
    		
    			//其他页面
	    		if($pageType == 1){
	    			//教学页面，使用table显示教学内容，课程分类
	    			$len = $foldArr[$foldId]->subArr[$subId]->listArrLen;
	    			$row = floor($len/3);
	    			$over = $len%3;
	    			
	    			

	    			echo '<table style="width:100%;">';
	    			for($i=0; $i<$row; $i++){
	    				echo '<tr style="width:100%">';
	    				for($j=0; $j<3; $j++){
	    					echo 
	    					'<td style="width:25%"><a style="text-decoration:none;" href="index.php?foldId=',$foldId,'&subId=',$subId,'&listId=',$i*3+$j,
	    					'"><div class="eduDiv"><image class="eduImg" src="',$lessonImg[$i*3+$j],'"/>',$foldArr[$foldId]->subArr[$subId]->listArr[$i*3+$j]->data,'</div></a></td>';	
	    				}
	    				echo '</tr>';
	    			}
	    			echo '<tr style="width:100%;">';
	    			for($i=0; $i<$over; $i++){
	    				echo 
	    				'<td style="width:25%"><a style="text-decoration:none;" href="index.php?foldId=',$foldId,'&subId=',$subId,'&listId=',$len-$i-1,
	    				'"><div class="eduDiv"><image class="eduImg" src="',$lessonImg[$len-$i-1],'"/>',$foldArr[$foldId]->subArr[$subId]->listArr[$len-$i-1]->data,'</div></a></td>';	
	    			} 		
	    			echo '</tr>'	;
	    			echo '</table>';
	    			
	    			
	    			
	    		}else if($pageType == 2){
	    			//当前目录下没有内容，等待下次更新
	    			if($foldArr[$foldId]->subArr[$subId]->listArr[$listId]->itemArrLen==0){
	    				echo '等待更新';
	    			}else{
		    			//显示视频列表
		    			if($subId == 0){
		    				$chapter = get_chapter($listId);
		    				//章
		    				for($i=0; $i<count($chapter); $i++){
		    					$temp = $chapterFlag[$i+1]-1;
		    					echo '
			    				<div id="menu_title',$i,'" class="menu_title" onclick="openMenu(this)">',$foldArr[$foldId]->subArr[$subId]->listArr[$listId]->itemArr[$temp]->chapter,'&nbsp&nbsp&nbsp&nbsp','<text style="font-size:17px;">',$foldArr[$foldId]->subArr[$subId]->listArr[$listId]->itemArr[$temp]->title,'</text>
							    <div class="indicator" id="indicator',$i,'">^</div></div>
							    <div class="menu" id="menu',$i,'">';
							    //节
							    for($j=0; $j<$chapter[$i]; $j++){
							    	$tempLen = 0;
							    	for($k=0; $k<$i; $k++){$tempLen+=$chapter[$k];}
							    	
							    	$tempForth = $foldArr[$foldId]->subArr[$subId]->listArr[$listId]->itemArr[$tempLen+$j]->data;
							    	echo '
							        <div class="item"><a target="_blank" href="../../files/',$firstName,'/',$secondName,'/',$thirdName,'/',$tempForth,'">',
							        $foldArr[$foldId]->subArr[$subId]->listArr[$listId]->itemArr[$tempLen+$j]->data,'</a></div>';
						        	if($j<$chapter[$i]-1){
						        		echo '<li class="item_divider"></li>';
						    		}
						    		
						    	}

						    	echo '</div>';
							    if($i<count($chapter)-1){
							    	echo '<li class="menu_divider"></li>';
								}
							}
		    			}else{
			    			//教学子页面，使用table显示教学内容,章节列表
			    			$len = $foldArr[$foldId]->subArr[$subId]->listArr[$listId]->itemArrLen;
			    			$row = floor($len/3);
			    			$over = $len%3;
			    			
			    			echo '<table style="width:100%">';
			    			for($i=0; $i<$row; $i++){
			    				echo '<tr style="width:100%">';
			    				for($j=0; $j<3; $j++){
			    					$tempForth = $foldArr[$foldId]->subArr[$subId]->listArr[$listId]->itemArr[$i*3+$j]->data;
			    					echo 
			    					'<td style="width:25%"><a target="_blank" href="../../files/',$firstName,'/',$secondName,'/',$thirdName,'/',$tempForth,
			    					'"><div class="eduDiv"><image class="eduImg" src="../../images/lesson.jpg"/>',$foldArr[$foldId]->subArr[$subId]->listArr[$listId]->itemArr[$i*3+$j]->data,'</a></td>';	
			    				}
			    				echo '</tr>';
			    			}
			    			echo '<tr style="width:100%">';
			    			for($i=0; $i<$over; $i++){
			    				$tempForth = $foldArr[$foldId]->subArr[$subId]->listArr[$listId]->itemArr[$len-$i-1]->data;
			    				echo 
			    				'<td style="width:25%"><a target="_blank" href="../../files/',$firstName,'/',$secondName,'/',$thirdName,'/',$tempForth,
			    				'"><div class="eduDiv"><image class="eduImg" src="../../images/lesson.jpg"/>',$foldArr[$foldId]->subArr[$subId]->listArr[$listId]->itemArr[$len-$i-1]->data,'</a></td>';	
			    			} 	
			    			echo '</tr>';		
			    			echo '</table>';
		    			}
	    			
	    			}
	    		}else if($pageType == 3){
	    			
	    		}else if($pageType == 4){
	    			
	    			
	    			//table显示list数据
	    			/*
	    			echo '<table frame="box" rules="all">';
	    			for($i=0; $i<$foldArr[$foldId]->subArr[$subId]->listArrLen; $i++){
	    				//计算第一列跨行
	    				$rowSpan = $foldArr[$foldId]->subArr[$subId]->listArr[$i]->itemArrLen;
	    				$rowSpanName = $foldArr[$foldId]->subArr[$subId]->listArr[$i]->data;
	    				//第一行
	    				echo '<tr><td rowspan=',$rowSpan,'>',$rowSpanName,'</td><td><a href="index.php?&foldId=',$foldId,'&subId=',$subId,'&listId=',$i,'&itemId=0','">',
	    				$foldArr[$foldId]->subArr[$subId]->listArr[$i]->itemArr[0]->data,'</a></td></tr>';
	    				for($j=1; $j<$foldArr[$foldId]->subArr[$subId]->listArr[$i]->itemArrLen; $j++){
	    					//其余行
	    					echo '<tr><td><a href="index.php?&foldId=',$foldId,'&subId=',$subId,'&listId=',$i,'&itemId=',$j,'">',
	    					$foldArr[$foldId]->subArr[$subId]->listArr[$i]->itemArr[$j]->data,'</a></td></tr>';
	    				}
	    			}	
	    			echo '</table>';*/
	    			for($i=0; $i<$foldArr[$foldId]->subArr[$subId]->listArrLen; $i++){
	    				$header = $foldArr[$foldId]->subArr[$subId]->listArr[$i]->data;
	    				echo '<table style="width:98%;margin:0px 0px 10px 0px; text-align:left">';
	    				echo '<trstyle="width:100%;" ><th style="width:100%;" colspan="4" bgcolor="#e2e2e2">',$header,'</th></tr>';
	    				$tempLen = $foldArr[$foldId]->subArr[$subId]->listArr[$i]->itemArrLen;
	    				$rowNum = floor($tempLen/4);
	    				$last = $tempLen%4;
	    				for($j=0; $j<$rowNum; $j++){
	    					echo '<tr style="width:100%;">';
	    					for($k=0; $k<4; $k++){
	    						echo '<td style="width:25%;"><a href="index.php?&foldId=',$foldId,'&subId=',$subId,'&listId=',$i,'&itemId=',$j*4+$k,'">',
	    					$foldArr[$foldId]->subArr[$subId]->listArr[$i]->itemArr[$j*4+$k]->data,'</a></td>';
	    					}
	    					echo '</tr>';
	    				}
	    				if($last != 0){
	    					echo '<tr style="width:100%;">';
	    					for($j=0; $j<$last; $j++){
	    						echo '<td style="width:25%;"><a href="index.php?&foldId=',$foldId,'&subId=',$subId,'&listId=',$i,'&itemId=',$tempLen-$last+$j,'">',
	    						$foldArr[$foldId]->subArr[$subId]->listArr[$i]->itemArr[$tempLen-$last+$j]->data,'</a></td>';
	    					}
	    					for($k=0; $k<4-$last; $k++)
	    						echo '<td style="width:25%;"></td>';
	    					echo '</tr>';
	    				}
	    				
	    				//echo '<tr style="width:100%;"><th style="width:100%;" colspan="2">更多内容等待更新...</th></tr>';
	    				echo '</table>';
	    			}

	    			
	    		}else if($pageType == 5){
	    			
	    			
	    			//具体内容，使用iframe显示
	    			echo '<iframe class="mainConDiv" frameborder="0" src="../../files/',$firstName,'/',$secondName,'/',$thirdName,'/',$forthName,'/',$forthName,'.htm','"/>';
	    		}else if($pageType == 6){
	    			
	    			
	    			//搜索结果显示
	    			if($searchId == -1){
	    				search_data(1);
	    				search_data(2);
	    				search_data(3);
	    			}else{
	    				search_data($searchId);
	    			}
	    			sort_data();
	    			//echo '<p>',count($matchArr),'</p>','<p>',count($matchArrCategory),'<p>';
	    			for($i=0; $i<count($matchArrCategory); $i++){
	    				$tempFold = $matchArrCategory[$i]->foldId;
	    				$tempSub = $matchArrCategory[$i]->subId;
	    				$tempList = $matchArrCategory[$i]->listId;
	    				$tempItem = $matchArrCategory[$i]->itemId;
	    				$tempFirstName = $foldArr[$tempFold]->data;
	    				$tempSecondName = $foldArr[$tempFold]->subArr[$tempSub]->data;
	    				$tempThirdName = $foldArr[$tempFold]->subArr[$tempSub]->listArr[$tempList]->data;
	    				$tempForthName = $foldArr[$tempFold]->subArr[$tempSub]->listArr[$tempList]->itemArr[$tempItem]->data;
	    				
	    				if($matchArrCategory[$i]->level > 1){
	    				echo 
	    				'<div class="resDiv"><a href="index.php?foldId=',$tempFold,'&subId=',$tempSub,'&listId=',$tempList,'&itemId=',$tempItem,'">',
		    			'<p>',$tempFirstName,'->',$tempSecondName,'->',$tempThirdName,'->',$tempForthName,'</p></a>',
		    			'<p>',$matchArrCategory[$i]->abstract,'</p>',
		    			'</div>';
		    		}
		    			
	    			}
	    			if(count($matchArrCategory)<1){
	    			for($i=0; $i<count($matchArr); $i++){
	    				$tempFold = $matchArr[$i]->foldId;
	    				$tempSub = $matchArr[$i]->subId;
	    				$tempList = $matchArr[$i]->listId;
	    				$tempItem = $matchArr[$i]->itemId;
	    				$tempFirstName = $foldArr[$tempFold]->data;
	    				$tempSecondName = $foldArr[$tempFold]->subArr[$tempSub]->data;
	    				$tempThirdName = $foldArr[$tempFold]->subArr[$tempSub]->listArr[$tempList]->data;
	    				$tempForthName = $foldArr[$tempFold]->subArr[$tempSub]->listArr[$tempList]->itemArr[$tempItem]->data;
	    				
	    				echo 
	    				'<div class="resDiv"><a href="index.php?foldId=',$tempFold,'&subId=',$tempSub,'&listId=',$tempList,'&itemId=',$tempItem,'">',
		    			'<p>',$tempFirstName,'->',$tempSecondName,'->',$tempThirdName,'->',$tempForthName,'</p></a>',
		    			'<p>',$matchArr[$i]->abstract,'</p>',
		    			'</div>';
		    			
		    			
	    			}
	    		}
	    			
	    		}
	    		
	    		if($pageType != 5){
	    			//echo '<div class="contactDiv"><p>联系我们  |  QQ:78080458  |  微信:15601317542</p></div>';
	    			echo '</div>';
	    		}
	    		

	    		echo '</div>';
	   //  		echo '
			
				// <div id="left_layer" style="position:fixed;bottom:10px;left:10px;">
			 //  	<img src="../../images/contact.jpg" style="width:135px;height:185px;"><br>
			 //  	<span style="CURSOR:hand;color:black;font-weight:bold;position:absolute;bottom:2px;right:2px;border: 1px solid;border-radius: 1px;" onclick="onClose(this);">X</span>
				// </div>';
    		}
    	?>				
    </div>

    
    
</body>

</html>