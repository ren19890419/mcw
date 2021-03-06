﻿<?php
require_once($_SERVER["DOCUMENT_ROOT"].'/include/update/common.php');

use Illuminate\Database\Capsule\Manager as DB;

$base_url = base_url();
$TypeID=I("TypeID");
$OpenID = I("OpenID");
if(I("OpenID")){
    $_SESSION[$UsersID.'OpenID'] = $OpenID;
    header("location:/api/".$UsersID."/pintuan/my/address/edit/".( $OpenID . '/')."?wxref=mp.weixin.qq.com");
    exit;
}else{
    if(empty($_SESSION[$UsersID.'OpenID'])){
        $_SESSION[$UsersID.'OpenID'] = session_id();
    }
}
if(!strpos($_SERVER['REQUEST_URI'],"mp.weixin.qq.com")){
    header("location:?wxref=mp.weixin.qq.com");
}
$rsConfig = DB::table("user_config")->where(["Users_ID" => $UsersID])->first();
$UserLevel=json_decode($rsConfig['UserLevel'],true);
if($UserID){
    $rsUser = DB::table("user")->where(["Users_ID" => $UsersID, 'User_ID' => $UserID])->first();
}else{
    $_SESSION[$UsersID."HTTP_REFERER"]="/api/".$UsersID."/pintuan/my/address/edit/".(empty($TypeID)?'':$TypeID.'/')."?wxref=mp.weixin.qq.com";
    header("location:/api/".$UsersID."/user/login/?wxref=mp.weixin.qq.com");
}

$AddressID = I("AddressID");
$rsAddress = DB::table("user_address")->where(["Users_ID" => $UsersID, "User_ID" => $UserID, "Address_ID" =>
    $AddressID])->first();

$Province = !empty($rsAddress['Address_Province'])?$rsAddress['Address_Province']:0;
$City = !empty($rsAddress['Address_City'])?$rsAddress['Address_City']:0;
$Area = !empty($rsAddress['Address_Area'])?$rsAddress['Address_Area']:0;

if($AddressID){
    $title = '编辑收货地址';
}else{
    $title = '新增收货地址';
}

$rsAdressNum = DB::table("user_address")->where(["Users_ID" => $UsersID, "User_ID" => $UserID])->count();
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" />
    <meta content="telephone=no" name="format-detection" />
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?=$title?></title>
    <link href='/static/css/global.css' rel='stylesheet' type='text/css' />
    <link href='/static/api/css/global.css' rel='stylesheet' type='text/css' />
    <link href='/static/api/shop/skin/default/style.css' rel='stylesheet' type='text/css' />
    <link href="/static/css/select2.css" rel="stylesheet"/>
    <link href="/static/css/bootstrap.css" rel="stylesheet" />
    <link rel="stylesheet" href="/static/css/font-awesome.css" />
    <link rel="stylesheet" href="/static/api/shop/skin/default/css/address_edit.css" />
    <script type='text/javascript' src='/static/js/jquery-1.7.2.min.js'></script>
    <script type='text/javascript' src='/static/js/jquery.validate.min.js'></script>
    <script type='text/javascript' src='/static/js/jquery.metadata.js'></script>
    <script type='text/javascript' src='/static/js/jquery.validate.zh_cn.js'></script>
    <script type='text/javascript' src='/static/api/js/global.js?t=1436336112'></script>
    <script type='text/javascript' src='/static/api/js/user.js?t=1436336112'></script>
    <script type='text/javascript' src="/static/js/select2.js"></script>
    <script type="text/javascript" src="/static/js/location.js"></script>
    <script type="text/javascript" src="/static/js/area.js"></script>
    <script type="text/javascript">
        var base_url = '<?=$base_url?>';
        var Users_ID = '<?=$UsersID?>';
        jQuery.validator.addMethod("isMobile", function(value, element) {
            var length = value.length;
            var mobile = /^(((13[0-9]{1})|(14[0-9]{1})|(15[0-9]{1})|(17[0-9]{1})|(18[0-9]{1}))+\d{8})$/;
            return this.optional(element) || (length == 11 && mobile.test(value));
        }, "格式错");

        $(document).ready(function(){
            showLocation(<?=$Province?>,<?=$City?>,<?=$Area?>);
            user_obj.my_address_init();
        });
    </script>
</head>
<body >
<header class="bar bar-nav">
    <a href="javascript:history.go(-1)" class="pull-left"><img src="/static/api/shop/skin/default/images/black_arrow_left.png" /></a>
    <h1 class="title" id="page_title"><?=$title?></h1>
</header>
<div id="wrap">
    <!-- 地址信息简述begin -->
    <div class="container">
        <div class="row">
            <form  class="user_address_form" id="address_form" method="post" action="/api/<?php echo $UsersID ?>/pintuan/" >
                <ul class="list-group">
                    <li class="list-group-item">
                        <label>收货人：&nbsp;</label>
                        <input type="text" name="Name"  required value="<?=$rsAddress['Address_Name']?>" />
                        <input type="hidden" name="UsersID" value="<?= $UsersID?>">
                    </li>
                    <li class="list-group-item">
                        <label>手机号码：&nbsp;</label>
                        <input type="text" name="Mobile" required isMobile="true" value="<?=$rsAddress['Address_Mobile']?>" />
                    </li>
                    <li class="list-group-item">
                        <label>所在省份：</label>
                        <select name="Province"  id="loc_province" required style="width:65%">
                            <option value="">选择省份</option>
                        </select>
                    </li>
                    <li class="list-group-item">
                        <label>所在城市：</label>
                        <select name="City" id="loc_city"  required  style="width:65%">
                            <option value="">选择城市</option>
                        </select>
                    </li>
                    <li class="list-group-item">
                        <label>所在区县：</label>
                        <select name="Area"  id="loc_town" required  style="width:65%">
                            <option value="">选择区县</option>
                        </select>
                    </li>
                    <li class="list-group-item">
                        <label>详细地址：&nbsp;</label>
                        <input type="text" name="Detailed"  required value="<?=$rsAddress['Address_Detailed']?>"/>
                    </li>
                    <?php if(!$rsAdressNum){?>
                        <input type="hidden" name="default" value="1"  >
                    <?php }else{ ?>
                        <li class="list-group-item">
                            <label>设为默认收货地址</label>
                            <input type="checkbox" name="default" value="1"  <?=$rsAddress['Address_Is_Default']==1?'checked':''?>/>
                        </li>
                    <?php } ?>
                    <li class="list-group-item">
                        <button id="submit-btn" type="button" style="background-color:#f61d4b;">提交</button>
                        <div class="clearfix"></div>
                    </li>
                </ul>
                <input type="hidden" name="AddressID" value="<?php echo $AddressID ?>" />
                <input type="hidden" name="action" value="address_edit_save" />
            </form>
        </div>
    </div>
    <!-- 地址信息简述end-->
</div>
</body>
</html>