<?php
$Dwidth = array('640','142','142','142','142','142','142','142','142');
$DHeight = array('350','142','142','142','142','142','142','142','142');

$Home_Json=json_decode($rsSkin['Home_Json'],true);
for($no=1;$no<=9;$no++){
	$json[$no-1]=array(
		"ContentsType"=>$no==1?"1":"0",
		"Title"=>$no==1?json_encode($Home_Json[$no-1]['Title']):$Home_Json[$no-1]['Title'],
		"ImgPath"=>$no==1?json_encode($Home_Json[$no-1]['ImgPath']):$Home_Json[$no-1]['ImgPath'],
		"Url"=>$no==1?json_encode($Home_Json[$no-1]['Url']):$Home_Json[$no-1]['Url'],
		"Postion"=>$no>9 ? "t".$no : "t0".$no,
		"Width"=>$Dwidth[$no-1],
		"Height"=>$DHeight[$no-1],
		"NeedLink"=>"1"
	);
}
?>
<?php require_once('header.php');?>
<div id="web_page_contents">
<link href='/static/js/plugin/flexslider/flexslider.css' rel='stylesheet' type='text/css' />
<link href='/static/api/web/skin/<?php echo $rsConfig['Skin_ID'];?>/page.css?t=<?php echo time();?>' rel='stylesheet' type='text/css' />
<link href='/static/api/web/skin/<?php echo $rsConfig['Skin_ID'];?>/page_media.css?t=<?php echo time();?>' rel='stylesheet' type='text/css' />
<script type='text/javascript' src='/static/js/plugin/flexslider/flexslider.js'></script>
<script type='text/javascript' src='/static/api/web/js/index.js?t=<?php echo time();?>'></script>
<script language="javascript">
var web_skin_data=<?php echo json_encode($json) ?>;
var MusicPath='<?php echo $rsConfig['MusicPath'] ? $rsConfig['MusicPath'] : ''?>';
$(document).ready(index_obj.index_init);
</script>
<div id="web_skin_index">
    <div class="web_skin_index_list banner" rel="edit-t01">
        <div class="img"></div>
    </div>
    <?php if($rsConfig['CallEnable']){?>
    	<div id="web_skin_index_tel">
        	<a href="tel:<?php echo $rsConfig['CallPhoneNumber'];?>" class="autotel"><?php echo $rsConfig['CallPhoneNumber'];?></a>
        </div>
     <?php }?>
	    <div class="web_wrap">
		            <div class="web_skin_index_list items fl mar_r" rel="edit-t02">
                <div class="img"></div>
                <div class="text"></div>
            </div>
        	            <div class="web_skin_index_list items fl mar_r" rel="edit-t03">
                <div class="img"></div>
                <div class="text"></div>
            </div>
        	            <div class="web_skin_index_list items fl mar_r" rel="edit-t04">
                <div class="img"></div>
                <div class="text"></div>
            </div>
        	            <div class="web_skin_index_list items fl" rel="edit-t05">
                <div class="img"></div>
                <div class="text"></div>
            </div>
        	<div class="blank9"></div>            <div class="web_skin_index_list items fl mar_r" rel="edit-t06">
                <div class="img"></div>
                <div class="text"></div>
            </div>
        	            <div class="web_skin_index_list items fl mar_r" rel="edit-t07">
                <div class="img"></div>
                <div class="text"></div>
            </div>
        	            <div class="web_skin_index_list items fl mar_r" rel="edit-t08">
                <div class="img"></div>
                <div class="text"></div>
            </div>
        	            <div class="web_skin_index_list items fl" rel="edit-t09">
                <div class="img"></div>
                <div class="text"></div>
            </div>
        	<div class="blank9"></div>    </div>
</div>
</div>
<div id="footer_points"></div>
<footer id="footer">
	<ul>
     <?php
	 	$i=0;
		$DB->get("web_column","*","where Users_ID='".$UsersID."' and Column_ParentID=0 and Column_NavDisplay=1 order by Column_Index asc limit 0,4");
		while($rsColumn=$DB->fetch_assoc()){
			$i++;
			$html = $i==1 ? '<li class="first">' : '<li>';
			echo $html.'<a href="'.(empty($rsColumn["Column_Link"])?'/api/'.$UsersID.'/web/column/'.$rsColumn["Column_ID"].'/':$rsColumn["Column_LinkUrl"]).'"><img src="/static/api/web/skin/default/images/nav-dot.png" />'.$rsColumn["Column_Name"].'</a></li>';
	}?>
	</ul>
</footer>
<?php if($rsConfig["PagesShow"]){?>
<script type='text/javascript' src='/static/js/plugin/animation/pagesshow.js'></script>
<?php if($rsConfig["PagesShow"]==1){?>
	<script language="javascript">
		var showtime='<?php echo $rsConfig["ShowTime"];?>000';
		pagesshow_obj.url='<?php echo 'http://'.$_SERVER["HTTP_HOST"].$rsConfig["PagesPic"];?>';
		$(document).ready(pagesshow_obj.msk_init);
		window.onresize=function(){pagesshow_obj.msk_init(1)};
	</script>
<?php }?>
<?php if($rsConfig["PagesShow"]==2){?>
	<script language="javascript">
		var showtime='<?php echo $rsConfig["ShowTime"];?>000';
		$(document).ready(pagesshow_obj.fade_init);
		window.onresize=function(){pagesshow_obj.fade_init(1)};
	</script>
<?php }?>
<?php if($rsConfig["PagesShow"]==3){?>
	<script language="javascript">
		var showtime='<?php echo $rsConfig["ShowTime"];?>000';
		pagesshow_obj.url='<?php echo 'http://'.$_SERVER["HTTP_HOST"].$rsConfig["PagesPic"];?>';
		$(document).ready(pagesshow_obj.door_init);
		window.onresize=function(){pagesshow_obj.door_init(1)};
	</script>
<?php }?>
<div id="PagesShow"><img src="http://<?php echo $_SERVER["HTTP_HOST"].$rsConfig["PagesPic"];?>" /></div>
<?php }?>
<?php require_once('footer.php');?>