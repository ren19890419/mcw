<?php
if(empty($_SESSION["Users_Account"]))
{
	header("location:/member/login.php");
}

if($_POST){	
	$Data=array(
		"usersid"=>$_SESSION["Users_ID"],		
		"shippingcode"=>$_POST["Company"],
		"cusname"=>$_POST["cusname"],
		"cuspasswd"=>$_POST["cuspasswd"],
		"sendsite"=>isset($_POST["sendsite"]) ? $_POST["sendsite"] : ''
	);
	$Flag=$DB->Add("users_express_info",$Data);
	if($Flag){
		echo '<script language="javascript">alert("添加成功");window.location.href="settingdianzi_print.php";</script>';
	}else{
		echo '<script language="javascript">alert("添加失败");history.back();</script>';
	}
	exit;
}else{
	$companys = array();
	$users_express_infoarr = array();
	$DB->Get("users_express_info","shippingcode","where usersid='".$_SESSION["Users_ID"]."'");
	while($rr = $DB->fetch_assoc()){
		$users_express_infoarr[] = $rr['shippingcode'];
	}
	$DB->Get("shop_shipping_company","Shipping_ID,Shipping_Name,Shipping_Code","where Biz_ID=0 and Users_ID='".$_SESSION["Users_ID"]."'");
	while($r = $DB->fetch_assoc()){
		if (in_array($r['Shipping_Code'],$users_express_infoarr)) {
			continue;
		}
		$companys[$r["Shipping_ID"]] = $r;
	}
}
$jcurid = 8;
?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title></title>
<link href='/static/css/global.css' rel='stylesheet' type='text/css' />
<link href='/static/member/css/main.css' rel='stylesheet' type='text/css' />
<script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
<script type='text/javascript' src='/static/member/js/global.js'></script>
<script type='text/javascript' src='/static/member/js/shipping.js?t=20180411'></script>
<script>
$(document).ready(shipping_obj.printtemplate_init);
</script>
</head>

<body>
<div id="iframe_page">
  <div class="iframe_content">
    <?php require_once($_SERVER["DOCUMENT_ROOT"].'/member/wechat/jiebase_menubar.php');?>
    <div id="printtemplate" class="r_con_wrap">
      
      <script language="javascript"></script>
      <form id="printtemplate_form" class="r_con_form" method="post" action="?">        
        <div class="rows">
          <label>物流公司</label>
          <span class="input">
          <select name="Company" id="com" notnull>
          	<?php
            	foreach($companys as $key=>$value){
			?>
            <option value="<?php echo $value["Shipping_Code"];?>"><?php echo $value["Shipping_Name"];?></option>
            <?php }?>
          </select>
          <font class="fc_red">*</font></span>
          <div class="clear"></div>
        </div>
		<div class="rows">
          <label>客户帐号</label>
          <span class="input">
          <input name="cusname" value="" type="text" class="form_input" size="40" maxlength="60" notnull>
          <font class="fc_red">*</font></span>
          <div class="clear"></div>
        </div>
		<div class="rows">
          <label>客户密码</label>
          <span class="input">
          <input name="cuspasswd" value="" type="text" class="form_input" size="40" maxlength="60" notnull>
          <font class="fc_red">*</font></span>
          <div class="clear"></div>
        </div>   
		<div class="rows" id="sendsite" style="display:none">
          <label>快递网点标识</label>
          <span class="input">
          <input name="sendsite" value="" type="text" class="form_input" size="40" maxlength="60" notnull>
          <font class="fc_red">*</font></span>
          <div class="clear"></div>
        </div>
        <div class="rows">
          <label></label>
          <span class="input">
          <input type="submit" class="btn_green" value="提交保存" name="submit_btn">
          </span>
          <div class="clear"></div>
        </div>        
      </form>
    </div>
  </div>
</div>
</body>
</html>