<?php
require_once('../global.php');

$condition = "where Biz_ID=".$_SESSION["BIZ_ID"]." and Users_ID='".$rsBiz["Users_ID"]."'";
$OrderBy = "Products_ID desc";
if ($_POST) {	
	if (empty($_POST['Products_ID'])) {
		 $res = array(
			"status"=>0,
			"info"=>"请选择要删除的产品！"
	);
		echo json_encode($res,JSON_UNESCAPED_UNICODE);
	exit;
	}
	
	$productids = implode(',',$_POST['Products_ID']);
	$Flag=$DB->Del("shop_Products","Users_ID='".$_SESSION["Users_ID"]."' and Products_ID in ($productids)");	
	if ($Flag) {
		$res = array(
			"status"=>1,
			"info"=>"批量删除成功！"
	);
		echo json_encode($res,JSON_UNESCAPED_UNICODE);
	exit;
	} else {
		$res = array(
			"status"=>0,
			"info"=>"批量删除失败！"
	);
		echo json_encode($res,JSON_UNESCAPED_UNICODE);
	exit;
	}
	}
$_GET = daddslashes($_GET,1);
if(isset($_GET['search'])){
	if($_GET['Keyword']){
		$condition .= " and Products_Name like '%".$_GET['Keyword']."%'";
	}
	if($_GET['SearchCateId']>0){
		$catids = $_GET['SearchCateId'];
		$condition .= " and Products_Category = ".$catids;
	}
	
	if(!empty($_GET['SearchBizCateId'])>0){
		$catid[] = $_GET["SearchBizCateId"];
		$DB->Get("biz_category","Category_ID","where Users_ID='".$rsBiz["Users_ID"]."' and Biz_ID=".$_SESSION["BIZ_ID"]." and Category_ParentID=".$_GET["SearchBizCateId"]);
		while($r = $DB->fetch_assoc()){
			$catid[] = $r["Category_ID"];
		}
		$condition .= " and Products_BizCategory in(".(implode(",",$catid)).")";
	}
	
	if($_GET["Attr"]){
		$condition .= " and ".$_GET["Attr"]."=1";
	}
	if(isset($_GET['MinPrice']) && intval($_GET['MinPrice'])>0){
		$condition .= " and Products_PriceX>=".intval($_GET['MinPrice']);
	}
	if(isset($_GET['MaxPrice']) && intval($_GET['MaxPrice'])>0){
		$condition .= " and Products_PriceX<=".intval($_GET['MaxPrice']);
	}
	if($_GET['OrderBy']){
		$OrderBy = $_GET['OrderBy'];
	}
}

if(isset($_GET["action"]))
{
	if($_GET["action"]=="del")
	{
		$Flag=$DB->Del("shop_products","Users_ID='".$rsBiz["Users_ID"]."' and Biz_ID=".$_SESSION["BIZ_ID"]." and Products_ID=".$_GET["ProductsID"]);
		if($Flag){
			$DB->Del("shop_products_attr","Products_ID=".$_GET["ProductsID"]);
			echo '<script language="javascript">alert("删除成功");window.location="'.$_SERVER['HTTP_REFERER'].'";</script>';
		}else{
			echo '<script language="javascript">alert("删除失败");history.back();</script>';
		}
		exit;
	}
}

$condition .= " order by ".$OrderBy;
$jcurid = 1;
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
</head>

<body>
<!--[if lte IE 9]><script type='text/javascript' src='/static/js/plugin/jquery/jquery.watermark-1.3.js'></script>
<![endif]-->

<div id="iframe_page">
  <div class="iframe_content">
    <link href='/static/member/css/shop.css' rel='stylesheet' type='text/css' />
    <script type='text/javascript' src='/biz/js/shop.js?t=<?=time();?>'></script>
    <script language="javascript">$(document).ready(shop_obj.products_list_init);</script>
    <?php require_once($_SERVER["DOCUMENT_ROOT"].'/biz/products/bizshop_menubar.php');?>
    <div id="products" class="r_con_wrap">
	<div class="control_btn">
        <a href="/biz/products/products_add.php" class="btn_green btn_w_120">添加产品</a>
      <a href="#search" class="btn_green btn_w_120">产品搜索</a> 
      <!--<a href="output.php?type=product_gross_info&action=outproduct" class="btn_green btn_w_120">导出产品</a>-->
	  <a href="javascript:void(0);" class="btn_green btn_w_120" id="auditingAll">批量删除</a>
      </div>
      <form class="search" id="search_form" method="get" action="products.php">
        关键词：
        <input type="text" name="Keyword" value="" class="form_input" size="15" />
        商城分类：
        <select name='SearchCateId'>
          <option value=''>--请选择--</option>
          <?php
          $DB->get("shop_category","*","where Users_ID='".$rsBiz["Users_ID"]."' order by Category_ParentID asc,Category_Index asc");
		  $shop_cate = array();
		  while($r=$DB->fetch_assoc()){
			  if($r["Category_ParentID"]==0){
				  $shop_cate[$r["Category_ID"]] = $r;
			  }else{
				  $shop_cate[$r["Category_ParentID"]]["child"][] = $r;
			  }
		  }
		  foreach($shop_cate as $key=>$value){			  
			  if(!empty($value["child"])){
				  foreach($value["child"] as $v){
					  echo '<option value="'.$v["Category_ID"].'">└'.$v["Category_Name"].'</option>';
				  }
			  }
		  }?>
        </select>&nbsp;
        <?php if($IsStore==1){?>
        自定义分类：
        <select name='SearchBizCateId'>
          <option value=''>--请选择--</option>
          <?php
          $DB->get("biz_category","*","where Users_ID='".$rsBiz["Users_ID"]."' and Biz_ID=".$_SESSION["BIZ_ID"]." order by Category_ParentID asc,Category_Index asc");
		  $diy_cate = array();
		  while($r=$DB->fetch_assoc()){
			  if($r["Category_ParentID"]==0){
				  $diy_cate[$r["Category_ID"]] = $r;
			  }else{
				  $diy_cate[$r["Category_ParentID"]]["child"][] = $r;
			  }
		  }
		  foreach($diy_cate as $key=>$value){
			  echo '<option value="'.$value["Category_ID"].'">'.$value["Category_Name"].'</option>';
			  if(!empty($value["child"])){
				  foreach($value["child"] as $v){
					  echo '<option value="'.$v["Category_ID"].'">└'.$v["Category_Name"].'</option>';
				  }
			  }
		  }?>
        </select>&nbsp;
        <?php }?>
        其他属性：
        <select name="Attr">
          <option value="0">--请选择--</option>
          <option value="Products_SoldOut">下架</option>
          <option value="Products_BizIsNew">新品</option>
          <option value="Products_BizIsHot">热卖</option>
		  <option value="Products_BizIsRec">推荐</option>
        </select>&nbsp;
        价格：
        <input type="text" name="MinPrice" size="5" /> ~ <input type="text" name="MaxPrice" size="5" />
        排序：
        <select name="OrderBy">
          <option value="Products_ID DESC">默认</option>
          <option value="Products_CreateTime DESC">添加时间降序</option>
          <option value="Products_CreateTime ASC">添加时间升序</option>
          <option value="Products_Sales DESC">销量降序</option>
          <option value="Products_Sales ASC">销量升序</option>
        </select>
        <input type="hidden" name="search" value="1" />
        <input type="submit" class="search_btn" value="搜索" />
        <!--<input type="button" class="output_btn" value="导出" />-->
      </form>
      <table width="100%" align="center" border="0" cellpadding="5" cellspacing="0" class="r_con_table">
        <thead>
          <tr>
		  <td width="6%" nowrap="nowrap"><input type="checkbox" onclick="selectAll(this);"/>全选</td>
            <td width="6%" nowrap="nowrap">序号</td>
            <td width="15%" nowrap="nowrap">名称</td>
            <td width="15%" nowrap="nowrap">结算明细</td>
            <td width="9%" nowrap="nowrap">价格</td>
            <td width="10%" nowrap="nowrap">图片</td>
            <td width="10%" nowrap="nowrap">二维码</td>
            <td width="9%" nowrap="nowrap">其他属性</td>
			<td width="6%" nowrap="nowrap">状态</td>
            <td width="10%" nowrap="nowrap">添加时间</td>
		  	<td width="5%" nowrap="nowrap">销量/剩余库存</td>
		  	<td width="9%" nowrap="nowrap" class="last">操作</td>
          </tr>
        </thead>
        <tbody>
          <?php 
		  $lists = array();
		  $DB->getPage("shop_products","*",$condition,10);
		  
		  while($r=$DB->fetch_assoc()){
			  $lists[] = $r;
		  }
		  foreach($lists as $k=>$rsProducts){
			  $JSON=json_decode($rsProducts['Products_JSON'],true);
		  ?>
           <tr>
		   <td><input type="checkbox" class="auditingid" name="Products_ID[]" value="<?php echo $rsProducts["Products_ID"] ?>" /></td>
            <td nowrap="nowrap"><?php echo $rsProducts["Products_ID"] ?></td>
            <td><?php echo $rsProducts["Products_Name"] ?></td>
            <td style="text-align:left; padding:5px">
				<?php if ($rsBiz['Finance_Type'] == 1) { ?>
            	<?php if($rsProducts["Products_FinanceType"] == 0) { ?>
                结算类型：按交易额比例<br />
                网站提成：<?php echo $rsProducts["Products_PriceX"]?> * <?php echo $rsProducts["Products_FinanceRate"];?> % = <?php echo number_format($rsProducts["Products_PriceX"] * $rsProducts["Products_FinanceRate"]/100,2,'.','');?>
				<?php } else { ?>
                结算类型：按产品供货价<br />
                供货价：<?php echo $rsProducts["Products_PriceS"];?><br />
                网站提成：<?php echo $rsProducts["Products_PriceX"]?> - <?php echo $rsProducts["Products_PriceS"];?> = <?php echo $rsProducts["Products_PriceX"]-$rsProducts["Products_PriceS"];?>
                <?php } ?>
				<?php } else { ?>
				结算类型：按交易额比例<br />
                网站提成：<?php echo $rsProducts["Products_PriceX"]?> * <?php echo $rsBiz["Finance_Rate"];?> % = <?php echo number_format($rsProducts["Products_PriceX"] * $rsBiz["Finance_Rate"]/100,2,'.','');?>
				<?php } ?>
            	
			</td>
            
            <td nowrap="nowrap"><del>￥<?php echo $rsProducts["Products_PriceY"] ?><br>
              </del>￥<?php echo $rsProducts["Products_PriceX"] ?></td>
            <td nowrap="nowrap"><?php echo empty($JSON["ImgPath"])?'':'<img src="'.$JSON["ImgPath"][0].'" class="proimg" />'; ?></td>
            <td nowraqp="nowrap">
            <img width="140" height="140" src="<?=$rsProducts['Products_Qrcode']?>" /></td>
            <td nowrap="nowrap"><?php echo empty($rsProducts["Products_SoldOut"])?"":"下架<br>";
			echo empty($rsProducts["Products_IsShippingFree"])?"":"免运费<br>";
			echo empty($rsProducts["Products_BizIsNew"])?"":"新品<br>";
			echo empty($rsProducts["Products_BizIsRec"])?"":"推荐<br>";
			echo empty($rsProducts["Products_BizIsHot"])?"":"热卖"; ?></td>
            <td nowrap="nowrap"><?php echo $rsProducts["Products_Status"]==0 ? '<font style="color:red">未审核</font>' : '<font style="color:blue">已审核</font>'; ?></td>
			<td nowrap="nowrap"><?php echo date("Y-m-d",$rsProducts["Products_CreateTime"]) ?></td>
		   	<td nowrap="nowrap"><?php echo $rsProducts["Products_Sales"];?>/<?php echo $rsProducts["Products_Count"] ?></td>
		   	<td class="last" nowrap="nowrap"><a href="products_edit.php?ProductsID=<?php echo $rsProducts["Products_ID"] ?>"><img src="/static/member/images/ico/mod.gif" align="absmiddle" alt="修改" /></a>
			<a href="products.php?action=del&ProductsID=<?php echo $rsProducts["Products_ID"] ?>" onClick="if(!confirm('删除后不可恢复，继续吗？')){return false};"><img src="/static/member/images/ico/del.gif" align="absmiddle" alt="删除" /></a>
			</td>
          </tr>
          <?php }?>
        </tbody>
      </table>
      <div class="blank20"></div>
      <?php $DB->showPage(); ?>
    </div>
  </div>
</div>
<script>
$(document).ready(function(){
	$('#auditingAll').click(function(){
				if(confirm("您确定要删除？")){
                var postData = $('.auditingid').serialize();
                if(postData == ''){
                    alert('请选择要删除的产品！');
                    return false;
                     
                }   
				}
                $.post('?',postData,function(data){
                    alert(data.info);
                    if(data.status === 1){
                        $('.delid').removeAttr('checked');
                        window.location.reload();
                    }            
                },'json');
        })	
});
  function selectAll(checkbox) {
        $('input[type=checkbox]').prop('checked', $(checkbox).prop('checked'));
    }
</script>
</body>
</html>