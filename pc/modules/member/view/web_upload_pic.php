<script type="text/javascript">
	<?php if($output['type'] == 'adv' && isset($output['ap_id']) && $output['ap_id']>0){ ?>
		parent.update_adv_pic("<?php echo $output['var_name'];?>","<?php echo $output['ap_id'];?>");
	<?php }else { ?>
		parent.update_pic("<?php echo $output['var_name'];?>","<?php echo $output['pic'];?>");
	<?php } ?>
</script>