<?php
require_once ($_SERVER["DOCUMENT_ROOT"] . '/include/update/common.php');

use Illuminate\Database\Capsule\Manager as DB;

if ($_POST) {
    $isDraw = I("Isdraw", 0);
    $tcount = I("Tcount", 0);
    $ratio = I("ratio", 0);
    $priceD = I("PriceD");
    $priceT = I("PriceT");
    $json = I("JSON");
    $priceS = I("PriceS");
    $id = I("id",0);

    if($isDraw ==0) {
        if($tcount == 0)
        {
            showMsg("允许中奖团数  不能为0!");
        }
    }
    if(!is_numeric($priceD)){
        showMsg("单购价请填写数字!");
    }

    if(!is_numeric($priceD)){
        showMsg("单购价请填写数字!");
    }

    if(!is_numeric($priceT)){
        showMsg("团购价请填写数字!");
    }
    if(!$id){
        showMsg("无效的产品ID!");
    }
    $StartTime = I("starttime");
    $EndTime = I("stoptime");
    // 处理图片路径
    $json = json_encode($json);
    $Data=array(
        "Products_Index"=> 0 ,
        "Products_Name"=> I("Name"),
        "Products_Category"=> I("TypeID"),
        "Products_Count"=> I("Count", 10),
        "Products_PriceT"=> $priceT,
        "Products_PriceD"=> $priceD,
        "Products_Profit"=> I("Products_Profit", 0),
        "commission_ratio"=> I("commission_ratio", 0),
        "nobi_ratio"=> I("nobi_ratio", 0),
        "platForm_Income_Reward" => I("platForm_Income_Reward", 0),
        "area_Proxy_Reward" => I("area_Proxy_Reward", 0),
        "sha_Reward" => I("sha_Reward", 0),
        "Products_Distributes"=> I("Distribute") ? json_encode(I("Distribute"),JSON_UNESCAPED_UNICODE) : "",
        "Products_JSON"=> $json,
        "Products_BriefDescription"=>htmlspecialchars(I("BriefDescription"), ENT_QUOTES),
        "Shipping_Free_Company"=> I("Shipping_Free_Company", 0),
        "Products_IsNew"=> I("products_IsNew",0),
        "Products_IsHot"=> I("products_IsHot",0),
        "Products_IsRecommend"=> I("products_IsRecommend", 0),
        "Products_Description"=>htmlspecialchars(I("Description"), ENT_QUOTES),
        "Products_Weight" => I("Weight"),
        "order_process"=> I("ordertype"),
        "is_buy"=> I("Isbuy", 0),
        "Is_Draw"=> I("Isdraw", 0),
        "Award_rule"=> I("Awardrule"),
        "Ratio"=> $ratio,
        "people_num"=>intval(I("Peoplenum")),
        "Products_Sales"=>0,
        "starttime"=>strtotime($StartTime),
        "stoptime"=>strtotime($EndTime) + 86398,
        "people_once"=> intval(I("people_once", 1)),
        "Products_pinkage"=> I("Products_pinkage", 0),
        "Products_refund"=> I("Products_refund", 0),
        "Products_compensation"=> I("Products_compensation", 0),
        "Team_Count"=>$tcount
    );

    $time = time();
    if(I("minTime")){
        if(I("minTime")>$Data['starttime']){
            showMsg("拼团开始时间不能小于活动时间");
        }
        if(I("maxTime")<$Data['stoptime']){
            showMsg("拼团结束时间不能大于活动结束时间");
        }
    }
    if($Data['stoptime']<$time){
        showMsg("拼团结束时间不能小于当前时间");
    }

    if($Data['starttime']>$Data['stoptime']){
        showMsg("拼团开始时间不能大于结束时间");
    }
    if ($rsBiz["Finance_Type"] == 0) { // 商品按交易额比例
        $Data["Products_FinanceType"] = 0;
        $Data["Products_FinanceRate"] = $rsBiz["Finance_Rate"];
        $Data["Products_PriceSd"] = number_format(
            $_POST['PriceD'] * (1 - $rsBiz["Finance_Rate"] / 100), 2, '.',
            '');
        $Data["Products_PriceSt"] = number_format(
            $_POST['PriceT'] * (1 - $rsBiz["Finance_Rate"] / 100), 2, '.',
            '');
    } else { // 商品按供货价
        if ($_POST["FinanceType"] == 0) { // 商品按交易额比例
            if(!is_numeric(I("FinanceRate")) || I("FinanceRate")<=0){
                echo '<script language="javascript">alert("网站提成比例必须大于零！");history.back();</script>';
                exit();
            }
            $Data["Products_FinanceType"] = 0;
            $Data["Products_FinanceRate"] = I("FinanceRate");
            $Data["Products_PriceSd"] = number_format($priceD * (1-I("FinanceRate")/100),2,'.','');
            $Data["Products_PriceSt"] = number_format($priceT * (1-I("FinanceRate")/100),2,'.','');
        } else { // 商品按供货价
            if(!is_numeric($priceS) || $priceS<=0 || $priceS>$priceT){
                showMsg("供货价格必须大于零，且小于团购价格！");
            }
            if(!is_numeric($priceS) || $priceS <=0 || $priceS > $priceD){
                showMsg("供货价格必须大于零，且小于单购购价格！");
            }
            $Data["Products_FinanceType"] = 1;
            $Data["Products_PriceSd"] = $priceS;
            $Data["Products_PriceSt"] = $priceS;
        }
    }
    $Flag = DB::table("pintuan_products")->where(["Products_ID" => $id])->update($Data);
    if ($Flag!==false) {
        if(I("cardids")){
            $idcards = I("cardids");
            $idcards = trim($idcards,",");
            DB::table("pintuan_virtual_card")->where(["Users_ID" =>$UsersID])->whereIn("Card_ID", $idcards)->update(["Products_Relation_ID" => $id]);
        }
        showMsg("保存成功！", "/biz/pintuan/products.php");
    }else{
        showMsg("保存失败！");
    }
} else {
    $shop_config = shop_config($UsersID);
    $dis_config = dis_config($UsersID);

    $Shop_Commision_Reward_Arr = array();
    if (! is_null($shop_config['Shop_Commision_Reward_Json'])) {
        $Shop_Commision_Reward_Arr = json_decode(
            $shop_config['Shop_Commision_Reward_Json'], true);
    }
}

$starttime = strtotime(date("Y-m-d") . " 00:00:00");
$endtime = time();
$day = date("d");
$monthStarttime = strtotime(date("Y-m-d") . " 00:00:00") - ($day - 1) * 86400;
$monthEndtime = strtotime(date("Y-m-d") . " 23:59:59");
if (isset($_GET["search"]) && $_GET["search"] == 1) {
    $SearchType = $_GET['SearchType'];
    $searchStarttime = strtotime($_GET['starttime'] . "00:00:00");
    $searchStoptime = strtotime($_GET['stoptime'] . "23:59:59") + 1;
    $days = ($searchStoptime - $searchStarttime) / 86400;
    $searchEnddate = $_GET['stoptime'];
} else {
    $day = date("d");
    $searchStarttime = strtotime(date("Y-m-d") . " 00:00:00") - ($day - 1) *
        86400;
    $searchStoptime = strtotime(date("Y-m-d") . " 23:59:59");
    $searchStartdate = date('Y-m-d', $searchStarttime);
    $searchEnddate = date('Y-m-d', $searchStoptime);
    $days = ($searchStoptime + 1 - $searchStarttime) / 86400;
    $SearchType = 'reg';
}
if ($searchStoptime <= $searchStarttime) {
    showMsg("截止时间不能小于开始时间！");
}
for ($i = $days; $i > 0; $i--) {
    $fromtime = strtotime($searchEnddate . "00:00:00") - ($i - 1) * 86400;
    $dates[] = date("m-d", $fromtime);
    $fromtimes[] = $fromtime;
}
$pintuanid= I("id");
$pintuan = DB::table("pintuan_products")->where(["Users_ID" => $UsersID, "Products_ID" =>
    $pintuanid])->first();
?>
<!DOCTYPE HTML>
<html>
<head>
    <meta charset="utf-8">
    <title></title>
    <link href='/static/css/global.css' rel='stylesheet' type='text/css' />
    <link href='/static/member/css/main.css' rel='stylesheet' type='text/css' />
    <script type='text/javascript' src='/static/js/jquery-1.11.1.min.js'></script>
    <script type='text/javascript' src='/static/js/jquery.validate.min.js'></script>
    <script type='text/javascript' src='/static/js/jquery.validate.zh_cn.js'></script>
    <script type='text/javascript' src='/static/member/js/global.js'></script>
    <script type='text/javascript' src='/static/member/js/products_attr_helper.js'></script>
    <link rel="stylesheet" href="/third_party/kindeditor/themes/default/default.css" />
    <script type='text/javascript' src="/third_party/kindeditor/kindeditor-min.js"></script>
    <script type='text/javascript' src="/third_party/kindeditor/lang/zh_CN.js"></script>
    <script type='text/javascript' src='/static/member/js/pintuan_shop.js'></script>

    <script type='text/javascript'>
        var Browser = new Object();
        KindEditor.ready(function(K) {
            K.create('textarea[name="Description"]', {
                themeType : 'simple',
                filterMode : false,
                uploadJson : '/biz/upload_json.php?TableField=web_column&Users_ID=<?php echo $UsersID;?>',
                fileManagerJson : '/biz/file_manager_json.php',
                allowFileManager : true

            });
            var editor = K.editor({
                uploadJson : '/biz/upload_json.php?TableField=web_article&Users_ID=<?php echo $UsersID;?>',
                fileManagerJson : '/biz/file_manager_json.php',
                showRemote : true,
                allowFileManager : true
            });
            K('#ImgUpload').click(function(){
                if(K('#PicDetail').children().length>=5){
                    alert('您上传的图片数量已经超过5张，不能再上传！');
                    return;
                }
                editor.loadPlugin('image', function() {
                    editor.plugin.imageDialog({
                        clickFn : function(url, title, width, height, border, align) {
                            K('#PicDetail').append('<div><a href="'+url+'" target="_blank"><img src="'+url+'" /></a><a onclick="return imagedel(this);"><span>删除</span></a><input type="hidden" name="JSON[ImgPath][]" value="'+url+'" /></div>');
                            editor.hideDialog();
                        }
                    });
                });
            });
            K('#PicDetail div span').click(function(){
                K(this).parent().remove();
            });
        });

        $(document).ready(shop_obj.products_add_init);

    </script>
    <style type="text/css">
        .dislevelcss{float:left;margin:5px 0px 0px 8px;text-align:center;border:solid 1px #858585;padding:5px;}
        .dislevelcss th{border-bottom:dashed 1px #858585;font-size:16px;}
        .r_con_form .rows .input .error { color:#f00; }
        .error,.fc_red { margin-left:20px;}
        .fc_red_1 {color:#f00;}
        .ele {  }
    </style>
</head>

<body>
<!--[if lte IE 9]><script type='text/javascript' src='/static/js/plugin/jquery/jquery.watermark-1.3.js'></script>
<![endif]-->

<div id="iframe_page">
    <div class="iframe_content">
        <link href='/static/member/css/shop.css' rel='stylesheet' type='text/css' />
        <link href='/static/member/css/user.css' rel='stylesheet' type='text/css' />
        <script type='text/javascript' src='/static/member/js/user.js'></script>
        <div class="r_nav">
            <ul>
                <li class="cur"><a href="./products.php">拼团管理</a></li>
                <li><a href="./orders.php">订单管理</a></li>
            </ul>
        </div>
        <div id="products" class="r_con_wrap">
            <link href='/static/js/plugin/lean-modal/style.css' rel='stylesheet' type='text/css' />
            <script type='text/javascript' src='/static/js/plugin/lean-modal/lean-modal.min.js'></script>
            <link href='/static/js/plugin/operamasks/operamasks-ui.css' rel='stylesheet' type='text/css' />
            <script type='text/javascript' src='/static/js/plugin/operamasks/operamasks-ui.min.js'></script>
            <script type='text/javascript' src='/static/js/plugin/daterangepicker/moment_min.js'></script>
            <link href='/static/js/plugin/daterangepicker/daterangepicker.css' rel='stylesheet' type='text/css' />
            <script type='text/javascript' src='/static/js/plugin/daterangepicker/daterangepicker.js'></script>
            <script type='text/javascript' src="/static/js/plugin/laydate/laydate.js"></script>
            <form id="product_add_form" class="r_con_form skipForm" method="post" action="product_edit.php">
                <input type="hidden" name="id" value="<?=$pintuanid ?>" />
                <input type="hidden" name="ordertype" value="0" />
                <div class="rows">
                    <label>产品名称</label>
                    <span class="input">
                      <input type="text" name="Name" value="<?php echo $pintuan["Products_Name"]?>" class="form_input" size="35" maxlength="100" notnull />
                      <font class="fc_red">*</font></span>
                    <div class="clear"></div>
                </div>
                <div class="rows" id="type_html">
                    <label>产品类型</label>
                    <span class="input">
                    <select name="TypeID" style="width:180px;" id="Type_ID" >
                    	<option value="">请选择类型</option>
                        <?php
                        $ParentCategory = DB::table("pintuan_category")->where(["Users_ID" => $UsersID, "parent_id" =>0])->orderBy("sort","asc")->get();
                        foreach($ParentCategory as $key=>$value){
                            $subCate = DB::table("pintuan_category")->where(["Users_ID" => $UsersID, "parent_id" =>$value["cate_id"]])->orderBy("sort","asc")->get();
                            if(!empty($subCate)){
                                echo '<optgroup label="'.$value["cate_name"].'">';
                                foreach($subCate as $rsCategory){
                                    echo '<option value="'.$rsCategory["cate_id"].'"'.($rsCategory["cate_id"]==$pintuan["Products_Category"]?" selected":"").'>'.$rsCategory["cate_name"].'</option>';
                                }
                                echo '</optgroup>';
                            }else{
                                echo '<option value="'.$value["cate_id"].'"'.($value["cate_id"]==$pintuan["Products_Category"]?" selected":"").'>'.$value["cate_name"].'</option>';
                            }
                        }
                        ?>
                    </select>
                    <font class="fc_red">*</font></span>
                    <div class="clear"></div>
                </div>
                <div class="rows">
                    <label>产品价</label>
                    <span class="input price"> 单购价格:￥
                      <input type="text" name="PriceD" value="<?php echo $pintuan["Products_PriceD"]?>" class="form_input" size="5" maxlength="10" /><font class="fc_red_1">*</font>
                      团购价格:￥
                      <input type="text" name="PriceT" value="<?php echo $pintuan["Products_PriceT"]?>" class="form_input" size="5" maxlength="10" /><font class="fc_red_1">*</font>
                      <font class="fc_red"></font>
                      </span>
                    <div class="clear"></div>
                </div>
                <?php if($rsBiz["Finance_Type"]==1){?>
                    <div class="rows">
                        <label>财务结算类型</label>
                        <span class="input">
                      <input type="radio" name="FinanceType" value="0" id="FinanceType_0" onClick="$('#PriceS').hide();$('#FinanceRate').show();"<?php echo $pintuan["Products_FinanceType"]==0 ? ' checked' : '';?> /><label for="FinanceType_0"> 按交易额比例</label>&nbsp;&nbsp;<input type="radio" name="FinanceType" value="1" id="FinanceType_1" onClick="$('#FinanceRate').hide();$('#PriceS').show();"<?php echo $pintuan["Products_FinanceType"]==1 ? ' checked' : '';?> /><label for="FinanceType_1"> 按供货价</label><br />
                  <span class="tips">注：若按交易额比例，则网站提成为：产品售价*比例%</span>
                  </span>
                        <div class="clear"></div>
                    </div>
                    <div class="rows" id="FinanceRate"<?php echo $pintuan["Products_FinanceType"]==1 ? ' style="display:none"' : '';?>>
                        <label>网站提成</label>
                        <span class="input">
                  <input type="text" name="FinanceRate" value="<?php echo $pintuan["Products_FinanceRate"];?>" class="form_input" size="10" /> %
                  </span>
                        <div class="clear"></div>
                    </div>
                    <div class="rows" id="PriceS"<?php echo $pintuan["Products_FinanceType"]==0 ? ' style="display:none"' : '';?>>
                        <label>供货价</label>
                        <span class="input">
                  <input type="text" name="PriceS" value="<?php echo $pintuan["Products_PriceSt"];?>" class="form_input" size="10" /> 元
                  </span>
                        <div class="clear"></div>
                    </div>
                <?php }?>
                <div class="rows">
                    <label>产品重量</label>
                    <span class="input">
                      <input type="text" name="Weight" value="<?=$pintuan["Products_Weight"]?>" notnull class="form_input" size="5" /><font class="fc_red">*</font>&nbsp;&nbsp;千克
                      </span>
                    <div class="clear"></div>
                </div>
                <?php
                $image=json_decode($pintuan['Products_JSON'],true);
                ?>
                <div class="rows">
                    <label>产品图片</label>
                    <span class="input">
                    	<span class="upload_file">
                			<div>
                                <div class="up_input">
                                  <input type="button" id="ImgUpload" value="添加图片" style="width:80px;" notnull /><font class="fc_red">*</font>
                                </div>
                                <div class="tips">共可上传<span id="pic_count">5</span>张图片，图片大小建议：640*640像素</div>
                                <div class="clear"></div>
                            </div>
                        </span>
                  		<div class="img" id="PicDetail"></div>
                    </span>
                    <div class="clear"></div>
                </div>
                <div class="rows">
                    <label>简短介绍</label>
                    <span class="input">
                  <textarea name="BriefDescription" class="briefdesc" notnull/><?php echo $pintuan['Products_BriefDescription']; ?></textarea><font class="fc_red">*</font>
                  </span>
                    <div class="clear"></div>
                </div>
                <div class="rows">
                    <label>商品属性</label>
                    <span class="input">
                          <label><input type="checkbox"  value="1" name="products_IsNew"
                                  <?=$pintuan['products_IsNew']?'checked':'' ?>>&nbsp;新品&nbsp;
                          </label>&nbsp;&nbsp;
                          <label><input type="checkbox" value="1" name="products_IsHot" <?=$pintuan['products_IsHot']?'checked':'' ?>>&nbsp;热销&nbsp;</label>&nbsp;&nbsp;
                          <label><input type="checkbox" value="1" name="products_IsRecommend" <?=$pintuan['products_IsRecommend']?'checked':'' ?>>&nbsp;促销&nbsp;</label>
                      </span>
                    <div class="clear"></div>
                </div>
                <div class="rows">
                    <label>商家信誉</label>
                    <span class="input">
                      <label><input name="Products_pinkage" type="checkbox"  value="1" <?=$pintuan["Products_pinkage"]?"checked":"" ?> />&nbsp;&nbsp;包邮&nbsp;&nbsp;</label>&nbsp;&nbsp;
                      <label><input name="Products_refund" type="checkbox"  value="1" <?=$pintuan["Products_refund"]?"checked":"" ?> />&nbsp;&nbsp;七天退款&nbsp;&nbsp;</label>
                      <label><input name="Products_compensation" type="checkbox"  value="1" <?=$pintuan["Products_compensation"]?"checked":"" ?> />&nbsp;&nbsp;假一赔十&nbsp;&nbsp;</label>
                      </span>
                    <div class="clear"></div>
                </div>
                <div class="rows">
                    <label>是否支持单购</label>
                    <span class="input">
                  <label><input type="radio" id="aa" value="1" name="Isbuy" <?php echo $pintuan["is_buy"]==1?'checked' : '';?> />&nbsp;&nbsp;既支持单购又支持团购&nbsp;</label>
                  <label><input type="radio" value="0" id="bb" name="Isbuy" <?php echo $pintuan["is_buy"]==0?'checked' : '';?> />&nbsp;&nbsp;仅支持团购&nbsp;</label>
                  </span>
                    <div class="clear"></div>
                </div>
                <div class="rows">
                    <label>拼团人数</label>
                    <span class="input">
                  <input type="text" name="Peoplenum" value="<?php echo $pintuan["people_num"]?>" class="form_input" size="5" maxlength="10" /><font class="fc_red_1">*</font> <span class="tips">&nbsp;注:拼团人数至少为2人及其以上.</span><font class="fc_red"></font>
                  </span>
                    <div class="clear"></div>
                </div>
                <div class="rows">
                    <label>是否支持抽奖</label>
                    <span class="input">
                	<table cellspacing="1" cellpadding="6" class="tb">
                    <tr>
                        <td class="tr">
                        <label>
                        <input type="radio" name="Isdraw" value="1" <?php echo $pintuan["Is_Draw"]==1?'checked':'';?> />&nbsp;&nbsp;不支持抽奖</label>
                        <label><input type="radio" name="Isdraw" value="0" <?php echo $pintuan["Is_Draw"]==0?'checked':'';?> />&nbsp;&nbsp;支持抽奖</label></td>
                    </tr>
                    <tr id="awordRule">
                        <td class="tl"><span color="f_red">抽奖规则</span></td>
                        <td class="tr">
                  <textarea name="Awardrule" class="briefdesc" /><?php echo $pintuan["Award_rule"]?></textarea>
                        </td>
                  </span>
                    </tr>
                    <tr id="allowTeams">
                        <td class="tl"><span color="f_red">允许中奖团数</span></td>
                        <td class="tr">
                            <input type="text" size="8" name="Tcount" value="<?php echo $pintuan["Team_Count"]?$pintuan["Team_Count"]:1;?>"/><font class="fc_red">*</font>
                            <input type="hidden" size="6" name="ratio" id="ratio" value="<?php echo $pintuan["Ratio"]?>"/><font class="fc_red"></font></td>
                        <td></td></span>
                    </tr>
                    </table>
                    </span>
                    <div class="clear"></div>
                </div>
                <div id="store_part" style="display:none">
                    <div class="rows">
                        <div class="clear"></div>
                    </div>
                    <div class="rows">
                        <div class="clear"></div>
                    </div>
                </div>
                <input type="hidden" name="people_once" value="1" />
                <div class="rows">
                    <label>商品库存</label>
                    <span class="input">
                      <input type="text" name="Count" value="<?php echo $pintuan["Products_Count"]?>" class="form_input" size="5" maxlength="10" /><font class="fc_red">*</font> <span class="tips">&nbsp;</span>
                      </span>
                    <font class="fc_red">*</font></span>
                    <div class="clear"></div>
                </div>
                <div class="rows">
                    <label>拼团有效时间</label>
                    <span class="input time">
                      <div class="l">
                          <div class="form-group">
                              <div class="input-group" id="reportrange" style="width:auto">
                              <input placeholder="开始时间" class="laydate-icon" name="starttime" value="<?php echo date("Y-m-d ",$pintuan['starttime']);?>" onclick="laydate()" readonly>-
                              <input placeholder="截止时间" class="laydate-icon" name="stoptime" value="<?php echo date("Y-m-d ",$pintuan['stoptime']);?>" onclick="laydate()" readonly>
                              </div>
                          </div>
                      </div>
                    </span>
                    <div class="clear"></div>
                </div>
                <div class="rows">
                    <label>详细介绍</label>
                    <span class="input">
                  <textarea class="ckeditor" name="Description" style="width:700px; height:300px;" notnull/><?php echo $pintuan["Products_Description"]?></textarea>
                  </span>
                    <div class="clear"></div>
                </div>
                <div class="rows">
                    <label></label>
                    <span class="input">
                  <input type="submit" class="btn_green" name="submit_button" value="提交保存" />
                  <a href="javascript:window.history.go(-1);" class="btn_gray">返回</a>
                  </span>
                    <div class="clear"></div>
                </div>
                <input type='hidden' value='' id='cardids' name='cardids' />
                <input type="hidden" id="UsersID" value="<?=$UsersID ?>" />
                <input type="hidden" id="ProductsID" value="0">
            </form>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function(){
        var type=$("input[name=Isdraw]").val();
        if(type=='1'){
            $("#awordRule").hide();
            $("#allowTeams").hide();
        }else{
            $("#awordRule").show();
            $("#allowTeams").show();
        }
    });
    $("input[name=Isdraw]").click(function(){
        var type=$(this).val();
        if(type=='1'){
            $("#awordRule").hide();
            $("#allowTeams").hide();
        }else{
            $("#awordRule").show();
            $("#allowTeams").show();
        }
    });


    function imagedel(o) {
        $(o).parent().remove();
        return false;
    }

    function imagedel1(i) {
        $('.imagedel' + i).remove();
        return false;
    }

    var images = eval('<?php echo json_encode($image['ImgPath']); ?>');
    for (var i = 0; i < images.length; i++) {
        $('#PicDetail').append('<div class="imagedel'+i+'"><a href="'+images[i]+'" target="_blank"><img src="'+images[i]+'" /></a><a onclick="return imagedel1('+i+');"><span>删除</span></a><input type="hidden" name="JSON[ImgPath][]" value="'+images[i]+'" /></div>');
    }

</script>
</body>
</html>