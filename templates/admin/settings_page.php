<?php

	$testmode = get_option( 'stripepress_testmode' );
	$api_keys = get_option( 'stripepress_api_keys' );

	$recon_keys = array();
	
	foreach ($api_keys as $key => $value) :
		
		$recon_keys[$key] = $key . base64_decode($value);
	
	endforeach;

?><script>
	$(document).ready(function(){
		var whichOne = ($('input#stripepress_testmode')[0].checked) ? $('.testtxt') : $('.livetxt');
		whichOne.css({"color":"red"});
	});
</script>
<div class="row" id="stripepress-main">
	<div class="col s12 m8 offset-m2" id="stripepress-main-inner">
		<div class="row" id="stripepress_header"></div>
		<div class="row" id="api_keys">
			<div class="col s12">
				<div class="switch">
					<label>
						<span class="livetxt">Live Mode</span>
						  <input id="stripepress_testmode" type="checkbox" name="stripepress_testmode" <?php if ($testmode) : echo 'checked'; endif; ?> />
						  <span class="lever"></span>
						<span class="testtxt">Test Mode</span>
					</label>
				</div>
			</div>
			<div id="api_keys_wrapper" class="col s12">
				<table>
					<thead>
						<tr>
							<th></th>
							<th>Live</th>
							<th>Test</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>Public</td>
							<td class="input-field"><input class="sp_api_input" type="text" name="pk_live_" value="<?php echo $recon_keys['pk_live_']; ?>" /><label for="pk_live_"></label></td>
							<td class="input-field"><input class="sp_api_input" type="text" name="pk_test_" value="<?php echo $recon_keys['pk_test_']; ?>" /><label for="pk_test_"></label></td>
						</tr>
						<tr>
							<td>Secret</td>
							<td class="input-field"><input class="sp_api_input" type="text" name="sk_live_" value="<?php echo $recon_keys['sk_live_']; ?>" /><label for="sk_live_"></label></td>
							<td class="input-field"><input class="sp_api_input" type="text" name="sk_test_" value="<?php echo $recon_keys['sk_test_']; ?>" /><label for="sk_test_"></label></td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
<?php //print_r($recon_keys); ?>
<?php /*?>
	<button class="btn key_tester">Test keys</button>
	<div class="row sp-api-inner">
	<div class="col s1"></div>
	<div class="col s4">Public</div>
	<div class="col s1"></div>
	<div class="col s4">Secret</div>
</div>
<div class="row sp-api-inner">
	<div class="col s1"><span>Live</span></div>
	<div class="col s4"><input type="text" name="pk_live_" value="" /></div>
	<div class="col s1"></div>
	<div class="col s4"><input type="text" name="sk_live_" value="" /></div>
</div>
<div class="row sp-api-inner">
	<div class="col s1"><span>Test</span></div>
	<div class="col s4"><input type="text" name="pk_test_" value="" /></div>
	<div class="col s1"></div>
	<div class="col s4"><input type="text" name="sk_test_" value="" /></div>
</div><?php */?>