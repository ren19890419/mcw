<?php
require_once('global.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/url.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/helper/distribute.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/include/compser_library/Protitle.php');

//会员信息
$front_title = get_dis_pro_title($DB,$UsersID);
$dis_config = Dis_Config::where('Users_ID',$UsersID)->first();
//自身消费额
if($dis_config->Pro_Title_Status == 4){
	$user_consue_choice = Order::where(array('User_ID'=>$_SESSION[$UsersID.'User_ID']))
					->whereIn('Order_Status', array(4,6,7));

	$user_consue = $user_consue_choice->sum('Order_TotalPrice');
	$user_back_consue = $user_consue_choice->sum('Back_Amount_Source');
	$user_consue = $user_consue - $user_back_consue;
}elseif($dis_config->Pro_Title_Status == 2){
		$user_consue_choice = Order::where(array('User_ID'=>$_SESSION[$UsersID.'User_ID']))
					->whereIn('Order_Status', array(2,3,4,6,7));
	$user_consue = $user_consue_choice->sum('Order_TotalPrice');
	$user_back_consue = $user_consue_choice->sum('Back_Amount_Source');
	$user_consue = $user_consue - $user_back_consue;
}

$ProTitle = new ProTitle($UsersID, $_SESSION[$UsersID.'User_ID']);
//自身销售额(直接下级普通用户 + 自身消费金额)


if($dis_config->Pro_Title_Status == 4){
	$user_count_choice = Order::where(function($query) use ($UsersID){
		$query->where(array('Owner_ID'=>$_SESSION[$UsersID.'User_ID']))->orWhere(['User_ID' => $_SESSION[$UsersID.'User_ID']]);
	})->whereIn('Order_Status', array(4,6,7));
	$user_count = $user_count_choice->sum('Order_TotalPrice');
	$user_back_count = $user_count_choice->sum('Back_Amount_Source');
	$user_count = $user_count - $user_back_count;
}elseif($dis_config->Pro_Title_Status == 2){
	$user_count_choice = Order::where(function($query) use ($UsersID){
		$query->where(array('Owner_ID'=>$_SESSION[$UsersID.'User_ID']))->orWhere(['User_ID' => $_SESSION[$UsersID.'User_ID']]);
	})->whereIn('Order_Status', array(2,3,4,6,7));
	$user_count = $user_count_choice->sum('Order_TotalPrice');
	$user_back_count = $user_count_choice->sum('Back_Amount_Source');
	$user_count = $user_count - $user_back_count;
}
//团队销售额计算使用
$Sales_Group_2 = $user_count;

//获取所有直接下级分销商用户销售额
$sess_userid = $_SESSION[$UsersID.'User_ID'];
$sons_dis_userid = $ProTitle->get_sons_dis_userid($sess_userid);
if ($sons_dis_userid) {
	//这里使用User_ID=Owner_ID 条件可以避免从普通用户升级到分销商里，避免一些订单被重复统计
	$user_dis_sale_amount = $ProTitle->get_dis_sale_amount($sons_dis_userid);

	$user_count = $user_count + $user_dis_sale_amount;
}
unset($sons_dis_userid);

//修正团队销售客未包含“自身消费额”和“自身销售额”,而在"自身销售额"里已经包含过了 自身消费额，所以直接加上“自身销售额”就可以了。
$level_config = $rsConfig['Dis_Level'];
$posterity = $accountObj->getPosterity($level_config);
$Sales_Group = round_pad_zero(get_my_leiji_sales($UsersID,$User_ID,$posterity),2);

$ex_bonus = array(
	"total"=>0,
	"pay"=>0,
	"payed"=>0
);
$DB->Get("distribute_account_record","Nobi_Money,Record_Status","where User_ID=".$_SESSION[$UsersID.'User_ID']);
while($r=$DB->fetch_assoc()){
	if($r["Record_Status"]==2){
		$ex_bonus["payed"] += $r["Nobi_Money"];
	}else{
		$ex_bonus["pay"] += $r["Nobi_Money"];
	}
	$ex_bonus["total"] += $r["Nobi_Money"];
}

$header_title = '爵位晋升';
require_once('header.php');
$ProTitle->up_front_nobility_level($user_consue, $user_count, $Sales_Group);
$rsAccount_nowup_obj = Dis_Account::Multiwhere(array('Users_ID'=>$UsersID,'User_ID'=>$User_ID))
			   ->first();
$rsAccount_nowup = $rsAccount_nowup_obj->toArray();   
?>
<body>
<link href="/static/api/distribute/css/protitle.css" rel="stylesheet">
<script language="javascript">
	var base_url = '<?=$base_url?>';
	var UsersID = '<?=$UsersID?>';
	$(document).ready(distribute_obj.pro_file_init);
</script>

<header class="bar bar-nav">
  <a href="javascript:history.back()" class="fa fa-2x fa-chevron-left grey pull-left"></a>
  <a href="/api/<?=$UsersID?>/distribute/?love" class="fa fa-2x fa-sitemap grey pull-right"></a>
  <h1 class="title">我的称号</h1>
  
</header>

<div class="wrap">
 <div class="container">
    
  
  	<div class="row">
      
    	
        <div class="panel panel-default">
  <!-- Default panel contents -->
 
  <div class="panel-body">
    
    <p><h4 style="color:#F29611;">
	<?php if(!empty($rsAccount_nowup['Professional_Title'])&&!empty($front_title[$rsAccount_nowup['Professional_Title']])):?>
		<?php if(!empty($front_title[$rsAccount_nowup['Professional_Title']]['ImgPath'])):?><img src="<?=$front_title[$rsAccount_nowup['Professional_Title']]['ImgPath']?>" /><?php endif;?> <?=$front_title[$rsAccount_nowup['Professional_Title']]['Name']?>
    <?php else:?>
       暂无爵位
	<?php endif;?>
    </h4></p>
     
	 <p>自身消费额:&nbsp;&nbsp;&yen;<span class="red"><?=sprintf("%.2f",$user_consue);?></span></p>
	 <p>自身销售额:&nbsp;&nbsp;&yen;<span class="red"><?=sprintf("%.2f",$user_count);?></span></p>
     <p>团队销售额:&nbsp;&nbsp;&yen;<span class="red"><?=sprintf("%.2f",$Sales_Group);?></span></p>	   
	  
	  <?php if($ex_bonus["total"]){?>
		<p>总奖金:&nbsp;&nbsp;<span class="red">&yen;<?php echo ($ex_bonus["total"] < 0) ? 0 : $ex_bonus["total"];?></span></p>
		<p>已有 <span class="red">&yen;<?=$ex_bonus['payed']?></span> 发放到您的可提现佣金中</p>
	  <?php }else{?>	  
		<p class="red">目前无奖金!!!</p>  
	  <?php }?>
  </div>
        
 
		
	
		
		<table class="table">
        <thead>
          <tr>
           	<th>#</th>
				<th>爵位</th>
				<th>自身消费额</th>
				<th>自身销售额</th>
				<th>团队销售额</th>
				<th>奖励百分比</th>
          </tr>
        </thead>
        <tbody>
		  <?php
if (count($front_title) > 0) {
		  foreach($front_title as $key=>$item):?>	
          <tr>
            <td scope="row"><?=$key?></td>
            <td><?=$item['Name']?></td>
            <td><span class="red">&yen;<?=$item['Consume']?></span></td>
            <td><span><?=$item['Sales_Self']?></span></td>
            <td><?=$item['Sales_Group']?></td>
			<td><span class="label label-info"><?=$item['Bonus']?>%</span></td>
          </tr>
           <?php endforeach;
}
?>
        </tbody>
      </table>
    	
    </div>
  </div>
</div>

 
<?php require_once('../shop/skin/distribute_footer.php');?> 
 
 
</body>
</html>

