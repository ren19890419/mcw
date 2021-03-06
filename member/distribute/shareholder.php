<?php
$base_url = base_url();

if(empty($_SESSION["Users_Account"]))
{
	header("location:/member/login.php");
}

$rsConfigmenu = Dis_Config::find($_SESSION["Users_ID"]);

$psize = 10;
$condition = "where Users_ID='".$_SESSION["Users_ID"]."'";
if(isset($_GET["search"])){
	if($_GET["search"]==1){		
		if(!empty($_GET["OrderNo"])){
			$OrderID = substr($_GET["OrderNo"],8);
			$OrderID =  empty($OrderID) ? 0 : intval($OrderID);
			$condition .= " and Order_ID=".$OrderID;
		}		
		if(!empty($_GET["AccTime_S"])){
			$condition .= " and Record_CreateTime>=".strtotime($_GET["AccTime_S"]);
		}
		if(!empty($_GET["AccTime_E"])){
			$condition .= " and Record_CreateTime<=".strtotime($_GET["AccTime_E"]);
		}
		if(!empty($_GET["psize"])){
			$psize = intval($_GET["psize"]);
		}
	}
}
$jcurid = 5;
?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title></title>
<link href='/static/css/global.css' rel='stylesheet' type='text/css' />
<link href='/static/member/css/main.css' rel='stylesheet' type='text/css' />
<script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
<script type='text/javascript' src='/static/js/jquery.datetimepicker.js'></script>
<link href='/static/css/jquery.datetimepicker.css' rel='stylesheet' type='text/css' />
<script type='text/javascript' src='/static/member/js/distribute/order.js?t=4'></script>
<link href='/static/js/plugin/operamasks/operamasks-ui.css' rel='stylesheet' type='text/css' />
<script type='text/javascript' src='/static/js/plugin/operamasks/operamasks-ui.min.js'></script> 
<script language="javascript">$(document).ready(order_obj.orders_init);</script>
</head>

<body>
<!--[if lte IE 9]><script type='text/javascript' src='/static/js/plugin/jquery/jquery.watermark-1.3.js'></script>
<![endif]-->
<div id="iframe_page">
  <div class="iframe_content">
    <link href='/static/member/css/user.css' rel='stylesheet' type='text/css' />
    <?php require_once($_SERVER["DOCUMENT_ROOT"].'/member/distribute/distribute_menubar.php');?>
    <div id="user" class="r_con_wrap">
	<form class="search" id="search_form" method="get" action="?">        
		订单号：<input type="text" name="OrderNo" value="" class="form_input" size="15" />&nbsp;        
        时间：
        <input type="text" class="input" name="AccTime_S" value="" maxlength="20" />
        -
        <input type="text" class="input" name="AccTime_E" value="" maxlength="20" />
        &nbsp;
        <input type="text" name="psize" value="" class="form_input" size="5" /> 条/页
        <input type="submit" class="search_btn" value="搜索" />        
        <input type="hidden" value="1" name="search" />
      </form>
      <table width="100%" align="center" border="0" cellpadding="5" cellspacing="0" class="r_con_table">
        <thead>
          <tr>
            <td width="10%" nowrap="nowrap">序号</td>           
			<td width="20%" nowrap="nowrap">订单号</td>
             <td width="20%" nowrap="nowrap">分红总金额</td>
			 <td width="20%" nowrap="nowrap">当前股东人数</td>
			 <td width="10%" nowrap="nowrap">单人分红金额</td>
            <td width="10%" nowrap="nowrap">分红类型</td>
            <td width="10%">时间</td>
          </tr>
        </thead>		
        <tbody>	
			<?php
		  $condition .= " order by Record_ID desc";
		  $DB->getPage("distribute_sha_rec","*",$condition,$psize);		  
		  /*获取订单列表牵扯到的分销商*/
		  $record_list = array();		 
		  while($rr=$DB->fetch_assoc()){
			$record_list[] = $rr;			
		  }
		  $i = 1;
		$zmoney = 0;
			?>
			<?php //print_r($record_list);?>
	<?php foreach($record_list as $key=>$record) { 
			$Sha_Accountidarr = explode(",", $record['Sha_Accountid']);
			foreach ($Sha_Accountidarr as $accid) {
				if (!empty($accid)) {
					$disaccid = $accid;
					break;
				}
			}
			if (!isset($disaccid)) { echo '非法操作';exit; }
			$rssha_level = $DB->GetRs("distribute_account","sha_level","where Account_ID=".$disaccid);
			$shaarr = json_decode($rsConfigmenu['Sha_Rate'], true);
			if (!$rssha_level) continue;
	?>	
           <tr>
           	<td><?=$i?></td>
			<td><?php echo date("Ymd",$record["Order_CreateTime"]).$record["Order_ID"] ?></td>
            <td><span class="red">&yen;<?=round_pad_zero($record['Record_Money'],2)?></span></td>
			<td><?=$record['Sha_Qty']?></td>
			<td><?=round_pad_zero(($record['Record_Money']/$record['Sha_Qty']),2)?></td>
            <td><?php echo isset($rssha_level['sha_level']) ? $shaarr['sha'][$rssha_level['sha_level']]['name'] : '已删除';?></td>
            <td><?=ldate($record['Record_CreateTime'])?></td>
          </tr>
		  <?php		 
		  $i++;
		  $zmoney += round_pad_zero($record['Record_Money'],2);
		  ?>
	<?php } ?>
        </tbody>		
      </table>
      <div class="page center-block"><?php $DB->showPage();?><strong class="red"><?php echo count($record_list) > 0 ? '金额总计：'.$zmoney : '';?></strong></div>
    </div>
  </div>  
</div>
</body>
</html>

