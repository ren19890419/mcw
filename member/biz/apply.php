<?php
if(empty($_SESSION["Users_Account"])){
	header("location:/member/login.php");
}
 
 
if(isset($_GET["action"])){
	if($_GET["action"]=="del"){
		//$Flag=$DB->Del("biz_apply","Users_ID='".$_SESSION["Users_ID"]."' and id=".$_GET["itemid"]);
                mysql_query("BEGIN");
                $Flag=$DB->Set("biz_apply",array("is_del"=>0,"status"=>-1),"where Users_ID='".$_SESSION["Users_ID"]."' and id=".$_GET["itemid"]);
                $BizInfo = $DB->GetRS('biz_apply','*','WHERE id = '.$_GET['itemid']); 
                $Flag_a = $DB->Set("biz",array("is_auth"=>-1),"where Users_ID='".$_SESSION["Users_ID"]."' and Biz_ID=".$BizInfo["Biz_ID"]);
		if($Flag && $Flag_a)
		{
                    mysql_query('commit');
			echo '<script language="javascript">alert("删除成功");window.location="'.$_SERVER['HTTP_REFERER'].'";</script>';
		}else
		{
                    mysql_query("ROLLBACK");
			echo '<script language="javascript">alert("删除失败");history.back();</script>';
		}
		exit;
	}
	
	if($_GET["action"]=="read"){
                mysql_query("BEGIN");
		$Flag = $DB->Set("biz_apply",array("status"=>2),"where Users_ID='".$_SESSION["Users_ID"]."' and id=".$_GET["itemid"]);
                $BizInfo = $DB->GetRS('biz_apply','*','WHERE id = '.$_GET['itemid']); 
                $Flag_a = $DB->Set("biz",array("is_auth"=>2),"where Users_ID='".$_SESSION["Users_ID"]."' and Biz_ID=".$BizInfo["Biz_ID"]);
		if($Flag && $Flag_a)
		{
                    mysql_query('commit');
                    echo '<script language="javascript">alert("审核成功");window.location="'.$_SERVER['HTTP_REFERER'].'";</script>';
		}else
		{
                   mysql_query("ROLLBACK");
			echo '<script language="javascript">alert("审核失败");history.back();</script>';
		}
		exit;
	}
        if($_GET["action"]=="back"){
                mysql_query("BEGIN");
		$Flag=$DB->Set("biz_apply",array("status"=>-1),"where Users_ID='".$_SESSION["Users_ID"]."' and id=".$_GET["itemid"]);
                $BizInfo = $DB->GetRS('biz_apply','*','WHERE id = '.$_GET['itemid']); 
		$Flag_a = $DB->Set("biz",array("is_auth"=>-1),"where Users_ID='".$_SESSION["Users_ID"]."' and Biz_ID=".$BizInfo["Biz_ID"]);
		if($Flag && $Flag_a)
		{   
                     mysql_query('commit');
			echo '<script language="javascript">alert("驳回成功");window.location="'.$_SERVER['HTTP_REFERER'].'";</script>';
		}else
		{
                    mysql_query("ROLLBACK");
			echo '<script language="javascript">alert("驳回失败");history.back();</script>';
		}
		exit;
	}
}
$condition = "where Users_ID='".$_SESSION["Users_ID"]."'";
if(isset($_GET['search'])){
	if($_GET['Biz_Account']){
            $BizInfo = $DB->getRs('biz','Biz_ID','where Biz_Account = "'.$_GET['Biz_Account'].'"');
            if (!empty($BizInfo)) {
              $condition .= " and biz_id = ".$BizInfo['Biz_ID'];  
            } else {
                $condition .= " and biz_id = 'a'";  
            }	
	} 
	if($_GET['status']!=""){
           
		$condition .= " and status=".$_GET['status'];
	}
}


/*$salesman_array = array();
$is_salesman_array = array();
$DB->Get("distribute_account","Real_Name,Invitation_Code,Is_Salesman","where Users_ID='".$_SESSION["Users_ID"]."' and Is_Salesman=1 and Invitation_Code <> ''");
while($row = $DB->fetch_assoc()){
    if (!empty($row['Invitation_Code'])){
        $salesman_array[$row['Invitation_Code']] = $row['Real_Name'];
	$is_salesman_array[$row['Invitation_Code']] = $row['Is_Salesman'];
    }
}*/

/*$shop_cate = array();
$DB->get("shop_category","Category_ID,Category_Name","where Users_ID='".$_SESSION["Users_ID"]."'");
while ($r = $DB->fetch_assoc()) {
    $shop_cate[$r['Category_ID']] = $r['Category_Name']; 
}*/
$biz_array = array();
$DB->get("biz","Biz_ID,Biz_Account","where Users_ID='".$_SESSION["Users_ID"]."'");
while ($r = $DB->fetch_assoc()) {
    $biz_array[$r['Biz_ID']] = $r['Biz_Account']; 
}
 
$condition .= " and is_del =1  order by CreateTime desc";

$_Status = array(1=>'<font style="color:#ff0000">未审核</font>',2=>'<font style="color:blue">审核通过</font>',-1=>'<font style="color:blue">已驳回</font>');
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
<script type='text/javascript' src='/static/member/js/global.js'></script>
<style type="text/css">
#bizs .search{padding:10px; background:#f7f7f7; border:1px solid #ddd; margin-bottom:8px; font-size:12px;}
#bizs .search *{font-size:12px;}
#bizs .search .search_btn{background:#1584D5; color:white; border:none; height:22px; line-height:22px; width:50px;}
</style>
</head>

<body>
<!--[if lte IE 9]><script type='text/javascript' src='/static/js/plugin/jquery/jquery.watermark-1.3.js'></script>
<![endif]-->

<div id="iframe_page">
  <div class="iframe_content">
    <?php require_once($_SERVER["DOCUMENT_ROOT"].'/member/biz/biz_menubar.php');?>
	
    <div id="bizs" class="r_con_wrap">
      <form class="search" method="get" action="?">
          商家账号：
        <input type="text" name="Biz_Account" value="" placeholder='请输入商家账号' class="form_input" size="15" />       
        状态：
        <select name="status">
          <option value="">全部</option>
          <option value="1">未审核</option>
          <option value="2">审核通过</option>
          <option value="-1">已驳回</option>
        </select>
        <input type="hidden" name="search" value="1" />
        <input type="submit" class="search_btn" value="搜索" />
      </form>
      <table width="100%" align="center" border="0" cellpadding="5" cellspacing="0" class="r_con_table">
        <thead>
          <tr>

            <td width="6%" nowrap="nowrap">ID</td>
            <td width="8%" nowrap="nowrap">商家账号</td>
            <td width="8%" nowrap="nowrap">认证类型</td>
            <td width="13%" nowrap="nowrap">申请时间</td>
            <td width="8%" nowrap="nowrap">状态</td>
            <td width="10%" nowrap="nowrap" class="last">操作</td>
          </tr>
        </thead>
        <tbody>
        <?php 
		  $lists = array();
		  $DB->getPage("biz_apply","*",$condition,10);
		  
		  while($r=$DB->fetch_assoc()){
			  $lists[] = $r;
		  }
		  foreach($lists as $k=>$rsBiz){
		?>
              
          <tr>
            <td nowrap="nowrap"><?php echo $rsBiz["id"];?></td>
            
            <td><?php echo !empty($biz_array[$rsBiz["Biz_ID"]])?$biz_array[$rsBiz["Biz_ID"]]:'商家不存在或已删除'; ?></td>
           
            <td><?php if($rsBiz['authtype']==1){echo '企业认证';}elseif($rsBiz['authtype']==2){echo'个人认证';}?></td>   
            <td nowrap="nowrap"><?php echo date("Y-m-d H:i:s",$rsBiz["CreateTime"]) ?></td>
            <td nowrap="nowrap"><?php echo $_Status[$rsBiz["status"]]; ?></td>
            <td class="last" nowrap="nowrap">
                <a href="./apply_detail.php?itemid=<?php echo $rsBiz["id"] ?>">[查看]</a>
                <?php if($rsBiz["status"] < 2){?>
                <a href="?action=read&itemid=<?php echo $rsBiz["id"] ?>">[通过]</a>
                <?php } ?>
                 <?php if($rsBiz["status"] == 1){?>
                <a href="?action=back&itemid=<?php echo $rsBiz["id"] ?>">[驳回]</a>
                <?php } ?>
                <a href="?action=del&itemid=<?php echo $rsBiz["id"] ?>" onClick="if(!confirm('删除后不可恢复，继续吗？')){return false};">[删除]</a>
            </td>

          </tr>
          <?php }?>
        </tbody>
      </table>
      <div class="blank20"></div>
      <?php $DB->showPage(); ?>
      <div style="background:#F7F7F7; border:1px #dddddd solid; height:40px; line-height:40px; font-size:12px; margin:10px 0px; padding-left:15px; color:#ff0000">提示：商家入驻地址 <a href="/biz/reg.php?usersid=<?php echo $_SESSION["Users_ID"];?>" target="_blank">http://<?php echo $_SERVER['HTTP_HOST'];?>/biz/reg.php?usersid=<?php echo $_SESSION["Users_ID"];?></a></div>
    </div>
  </div>
</div>
</body>
</html>