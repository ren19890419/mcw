<?php if(isset($_GET['cfgPay']) && $_GET['cfgPay']==1){?>
<?php
    require_once ($_SERVER["DOCUMENT_ROOT"] . '/Framework/Conn.php');
    require_once ($_SERVER["DOCUMENT_ROOT"] . '/include/helper/flow.php');
    require_once ($_SERVER["DOCUMENT_ROOT"] . '/cron/windowSchedule.php');
	require_once ($_SERVER["DOCUMENT_ROOT"] . '/cron/crontab.php');
    if(isset($_GET['action']) && $_GET['action'] == 'taskRemove'){
        $Users_Id = isset($_SESSION["Users_ID"]) ? $_SESSION["Users_ID"] : '';
        $taskName = $_SESSION["Users_ID"]."_Task";
        $task = new Task();
        $task->remove($taskName);
        $DB->Del("users_schedule","Users_ID='{$Users_Id}'");
        echo "<script> alert(\"删除计划任务成功\");history.go(-1); </script>";
        exit;
    }
	if(isset($_GET["cliid"])){	
		$subid = $_GET["cliid"];
	}
    if ($_POST) {
        $RunType = $_POST['RunType'];
        $day = intval($_POST['day']);
        $Time = $_POST['Time'];
        $Users_Id = isset($_SESSION["Users_ID"]) ? $_SESSION["Users_ID"] : '';
        $StartRunTime = "";
        if(!$Users_Id){
            echo "<script> alert(\"Session过期，请重新登录\");top.location.href = '/member/login.php'; </script>";
            exit;
        }
        if(!$day){
            $day =1;
        }
        if(empty($Time) || !$Time){
            $Time = date("H:i");
        }

        $data = array(
            'Users_ID' => $Users_Id,
            'StartRunTime' => $Time,
            'RunType' => $RunType,
            'Status' => 1,
            'LastRunTime' => strtotime(date("Y-m-d",time())),
            'day' =>$day
        );
        //添加计划任务
        $sch = $DB->GetRs("users_schedule", "*", "WHERE Users_ID='{$Users_Id}'");
        if ($sch) {
            $DB->Set("users_schedule", $data, "WHERE Users_ID='{$Users_Id}'");
        } else {
            $DB->Add("users_schedule", $data);
        }
		if(PHP_OS == 'WINNT'){
			$taskName = $_SESSION["Users_ID"]."_Task";
			$task = new Task();
			if ($sch) {
				$task->remove($taskName);
			}
			$type = "";
			if($RunType == 1){  //按周
				$task->add("mo",1);
				$type = "WEEKLY";
			}else if($RunType ==2 ){  //按天
				$task->add("mo",$day);
				$type = "DAILY";
			}else{  //按月
				$task->add("mo",1);
				$type = "MONTHLY";
			}
			$task->add("st",$Time);
			$task->add("ru",'"System"');
			$task->create($taskName ,"cmd /c " .$_SERVER["DOCUMENT_ROOT"]."/cron/Run.bat  http://".$_SERVER['HTTP_HOST']."/api/pintuan/sync/");
			$task->getXML($taskName);
		}else{
			// 非windows 执行 
			$arrTime = explode(':', $Time);
			$cron  = $arrTime[1] . " " . $arrTime[0] . ' ';
			if($RunType == 1){  //按周
				$cron .= "* * */" . $day . " ";
			}else if($RunType ==2 ){  //按天
				$cron .= "*/" . $day . " * * ";
			}else{  //按月
				$cron .= "* */" . $day . " * ";
			}
			$cron .= " curl -s http://".$_SERVER['HTTP_HOST']."/api/pintuan/sync/";
			try{
				Crontab::removeJob($cron);
				Crontab::addJob($cron);
			}catch(Exception $e){
				echo "请授予当前用户运行crontab的权限";exit;
			}
		}
        echo "<script> alert(\"修改成功\");history.go(-1);</script>";
        exit;
    }
$jcurid = 1;
    ?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title>计划任务配置</title>
    <link href='/static/css/global.css' rel='stylesheet' type='text/css' />
    <link href='/static/member/css/main.css' rel='stylesheet' type='text/css' />
    <script type='text/javascript' src='/static/js/jquery-1.9.1.min.js'></script>
</head>

<body>
	<!--[if lte IE 9]><script type='text/javascript' src='/static/js/plugin/jquery/jquery.watermark-1.3.js'></script>
<![endif]-->
	<div id="iframe_page">
		<div class="iframe_content">
			<link href='/static/member/css/shop.css' rel='stylesheet' type='text/css' />
			<?php require_once($_SERVER["DOCUMENT_ROOT"].'/member/pintuan/marketing_menubar.php');?>
			<div id="payment" class="r_con_wrap">
				<form id="payment_form" class="r_con_form" method="post" action="/member/pintuan/config.php?cfgPay=1">
					<?php $sch = $DB->GetRs("users_schedule", "*", "WHERE Users_ID='{$_SESSION['Users_ID']}'");
					       $type = 2;
					       if($sch){
					           $type = $sch['RunType'];
					           $time = $sch['StartRunTime'];
					           $day = $sch['day'];
					           $lastRunTime = $sch['LastRunTime'];
					          
					       }
					?>
					<div class="rows">
						<label>运行方式</label> <span class="input time"> <select
							name='RunType'>
								<option value="1" <?=$type==1?"selected":"" ?>>按周</option>
								<option value="2" <?=$type==2?"selected":"" ?>>按天</option>
								<option value="3" <?=$type==3?"selected":"" ?>>按月</option>
						</select>&nbsp; (若按天运行，请手动填写天数)<font class="fc_red">*</font></span>
						<div class="clear"></div>
					</div>
					<div class="rows">
						<label>运行时间</label> <span class="input time"> <input name="Time"
							type="text" value="<?=isset($time)?$time:date('H:i:s') ?>" class="form_input"
							size="40" notnull /> <font class="fc_red">*</font> <span
							class="tips">设置抽奖运行的时间段</span></span>
						<div class="clear"></div>
						<label>运行天数</label> <span class="input time"> <input name="day"
							type="text" value="<?php echo isset($day)?$day:2; ?>" class="form_input" size="40" notnull /> <font
							class="fc_red">*</font> <span class="tips">每隔N天进行运行</span></span>
					</div>
					<div class="rows">
						<label></label> <span class="input"> <input type="submit"
							class="btn_green" value="确定" name="submit_btn">   <input type="button"
							class="btn_green" value="删除计划任务" name="removeTask"></span>
						<div class="clear"></div>
					</div>
				</form>
			</div>
		</div>
	</div>
	<script>
	$(function(){
		$("input[name='removeTask']").click(function(){
			location.href = "/member/pintuan/config.php?cfgPay=1&action=taskRemove";
		});
		
		$("select[name='RunType']").change(function(){

			var RunType = $("select[name='RunType']").val();
			if(RunType==1){
				$("input[name='day']").val("7");
			}else if(RunType==3){
				$("input[name='day']").val("<?php echo date("t",time());?>");
			}
	    });

	});
	</script>
</body>
</html>
<?php die(); } ?>
<?php
$DB->showErr=false;
if(empty($_SESSION["Users_Account"]))
{
	header("location:/member/login.php");
}
//require_once('vertify.php');
$rsConfig=$DB->GetRs("pintuan_config","*","where Users_ID='".$_SESSION["Users_ID"]."'");
if(empty($rsConfig)){
	$Data=array(
		"Users_ID"=>$_SESSION["Users_ID"],
		"SiteName"=>"拼团",
		"CallEnable"=>0,
	);
	$DB->Add("pintuan_config",$Data);
	$rsConfig=$DB->GetRs("pintuan_config","*","where Users_ID='".$_SESSION["Users_ID"]."'");
}
//print_r($rsConfig); 
$json=$DB->GetRs("wechat_material","*","where Users_ID='".$_SESSION["Users_ID"]."' and Material_Table='pintuan' and Material_TableID=0 and Material_Display=0");
if(empty($json)){
	$Material=array(
		"Title"=>"拼团",
		"ImgPath"=>"/static/api/images/cover_img/web.jpg",
		"TextContents"=>"",
		"Url"=>"/api/".$_SESSION["Users_ID"]."/pintuan/"
	);
	$Data=array(
		"Users_ID"=>$_SESSION["Users_ID"],
		"Material_Table"=>"pintuan",
		"Material_TableID"=>0,
		"Material_Display"=>0,
		"Material_Type"=>0,
		"Material_Json"=>json_encode($Material,JSON_UNESCAPED_UNICODE),
		"Material_CreateTime"=>time()
	);
	$DB->Add("wechat_material",$Data);
	$MaterialID=$DB->insert_id();
	$rsMaterial=$Material;
}else{
	$rsMaterial=json_decode($json['Material_Json'],true);
}
 
$rsKeyword=$DB->GetRs("wechat_keyword_reply","*","where Users_ID='".$_SESSION["Users_ID"]."' and Reply_Table='pintuan' and Reply_TableID=0 and Reply_Display=0");
if(empty($rsKeyword)){
	$MaterialID=empty($json['Material_Json'])?$MaterialID:$json['Material_ID'];
	$Data=array(
		"Users_ID"=>$_SESSION["Users_ID"],
		"Reply_Table"=>"pintuan",
		"Reply_TableID"=>0,
		"Reply_Display"=>0,
		"Reply_Keywords"=>"拼团",
		"Reply_PatternMethod"=>0,
		"Reply_MsgType"=>1,
		"Reply_MaterialID"=>$MaterialID,
		"Reply_CreateTime"=>time()
	);
	$DB->Add("wechat_keyword_reply",$Data);
	$rsKeyword=$Data;
}
if($_POST)
{
	//开始事务定义
	$flag=true;
	$msg="";
	mysql_query("begin");
	$Data=array(
                "info"=>isset($_POST["info"])?$_POST["info"]:0,
                "is_ems"=>isset($_POST["is_ems"])?$_POST["is_ems"]:0, 
                "is_back"=>isset($_POST["is_back"])?$_POST["is_ems"]:0,
            		"SiteName"=>$_POST["SiteName"],
            		"CallEnable"=>isset($_POST["CallEnable"])?$_POST["CallEnable"]:0,
            		"CallPhoneNumber"=>$_POST["CallPhoneNumber"],
            	);
	$Set=$DB->Set("pintuan_config",$Data,"where Users_ID='".$_SESSION["Users_ID"]."'");
	$flag=$flag&&$Set;
	$Data=array(
		"Reply_Keywords"=>$_POST["Keywords"],
		"Reply_PatternMethod"=>isset($_POST["PatternMethod"])?$_POST["PatternMethod"]:0
	);
	$Set=$DB->Set("wechat_keyword_reply",$Data,"where Users_ID='".$_SESSION["Users_ID"]."' and Reply_Table='pintuan' and Reply_TableID=0 and Reply_Display=0");
	$flag=$flag&&$Set;
	$Material=array(
		"Title"=>$_POST["Title"],
		"ImgPath"=>$_POST["ImgPath"],
		"TextContents"=>"",
		"Url"=>"/api/".$_SESSION["Users_ID"]."/pintuan/"
	);
	$Data=array(
		"Material_Json"=>json_encode($Material,JSON_UNESCAPED_UNICODE)
	);
	$Set=$DB->Set("wechat_material",$Data,"where Users_ID='".$_SESSION["Users_ID"]."' and Material_Table='pintuan' and Material_TableID=0 and Material_Display=0");
	$flag=$flag&&$Set;
	if($flag)
	{
		mysql_query("commit");
                echo '<script language="javascript">alert("保存成功");window.location="config.php";</script>';
                exit;
	}else
	{
		mysql_query("roolback");
                 echo '<script language="javascript">alert("保存失败");window.location="config.php";</script>';
                 exit;
	}
}
$jcurid = 1;
?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
    <title></title>
    <link href='/static/css/global.css' rel='stylesheet' type='text/css' />
    <link href='/static/member/css/main.css' rel='stylesheet' type='text/css' />
    <script type='text/javascript' src='/static/js/jquery-1.9.1.min.js'></script>
    <script type='text/javascript' src='/static/member/js/global.js'></script>
    <?php require_once (CMS_ROOT . '/member/image_config.php'); ?>
</head>
<body>
<!--[if lte IE 9]><script type='text/javascript' src='/static/js/plugin/jquery/jquery.watermark-1.3.js'></script>
<![endif]-->
<div id="iframe_page">
  <div class="iframe_content">
   <?php require_once($_SERVER["DOCUMENT_ROOT"].'/member/pintuan/marketing_menubar.php');?>
    <script language="javascript">
$(document).ready(function(){
	global_obj.config_form_init();
});
</script> 
    <div class="r_con_config r_con_wrap">
        <form id="" action="?" method="post">
        <input type="hidden" name="is_back" value="1"/>
        <input type="hidden" name="is_ems" value="1"/>
        <table border="0" cellpadding="0" cellspacing="0">
          <tr>
            <td width="50%" valign="top"><h1><span class="fc_red">*</span> <strong>拼团网名称</strong></h1>
              <input type="text" class="input" name="SiteName" value="<?php echo $rsConfig["SiteName"] ?>" maxlength="30" notnull /></td>
            <td width="50%" valign="top">
                <input type="hidden" name="CallEnable" value="1" />
              <input type="hidden" class="input" name="CallPhoneNumber" value="<?php echo empty($rsConfig["CallPhoneNumber"])?"":$rsConfig["CallPhoneNumber"]; ?>" />
            </td>
          </tr>
        </table>
        <table align="center" border="0" cellpadding="0" cellspacing="0" id="config_form">
          <tr>
            <td><h1><strong>触发信息设置</strong></h1>
              <div class="reply_msg">
                <div class="m_left"> <span class="fc_red">*</span> 触发关键词<br />
                  <input type="text" class="input" name="Keywords" value="<?php echo $rsKeyword["Reply_Keywords"] ?>" maxlength="100" notnull />
                  <br />
                  <br />
                  <br />
                  <span class="fc_red">*</span> 匹配模式<br />
                  <div class="input">
                    <label>
                      <input type="radio" name="PatternMethod" value="0"<?php echo empty($rsKeyword["Reply_PatternMethod"])?" checked":""; ?> />
                      精确匹配<span class="tips">（输入的文字和此关键词一样才触发）</span></label>
                  </div>
                  <div class="input">
                    <label>
                      <input type="radio" name="PatternMethod" value="1"<?php echo $rsKeyword["Reply_PatternMethod"]==1?" checked":""; ?> />
                      模糊匹配<span class="tips">（输入的文字包含此关键词就触发）</span></label>
                  </div>
                  <br />
                  <br />
                  <span class="fc_red">*</span> 图文消息标题<br />
                  <input type="text" class="input" name="Title" value="<?php echo $rsMaterial["Title"] ?>" maxlength="100" notnull />
                </div>
                <div class="m_right"> <span class="fc_red">*</span> 图文消息封面<span class="tips">（大图尺寸建议：640*360px，500KB以内，gif,jpg,jpeg,png格式）</span><br />
                    <div class="file" style="margin-top:10px;">
                        <input type="button" id="ImgUpload" value="添加图片" style="width:80px;" />
                        <input type="hidden" id="ImgPath" name="ImgPath" value="<?= $rsMaterial["ImgPath"] ? $rsMaterial["ImgPath"] : ''?>" />
                        <span class="tips">图片建议尺寸：640*360px</span>
                    </div>
                  <div class="img" id="ImgDetail" style="margin-top: 10px;"><img src="<?php echo empty($rsMaterial["ImgPath"])?"/api/images/cover/pintuan.jpg":$rsMaterial["ImgPath"]; ?>" width="640" height="360"></div>
                </div>
                <div class="clear"></div>
              </div>
          </tr>
        </table>
        <div class="submit">
          <input type="submit" name="submit" value="提交保存" />
        </div>
      </form>
    </div>
  </div>
</div>
</body>
</html>