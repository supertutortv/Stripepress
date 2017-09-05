<?php
		/*\Stripe\Stripe::setApiKey(Spress()->secret_key);
		$subs = \Stripe\Subscription::all(array('limit'=>50));

		$icons = array(
			'completed' => array(
				'logo'=>'check_circle',
				'color'=>'green'
			),
			'pending' => array(
				'logo'=>'error',
				'color'=>'gold'
			)
		)*/
?>
<style type="text/css">
	.sp-sub-status {
		text-transform: uppercase
	}
	.collapsible-body {
		min-height: 4em
	}
</style>
<script>
	var spSubs = {
		sk : "",
		apiBase : "https://api.stripe.com/v1/",
		request : function(m,ep,id,params,cbSuc,cbFail) { //method, endpoint, resource id, parameters (query string), success callback, fail callback
			var deferred = new $.Deferred();
			var req = $.ajax({
				url : this.apiBase+ep+id+params,
				method : m,
				//success : function(data){},
				//error : function(xhr,status,err){},
				headers: { 'Authorization': 'Bearer <?php echo Spress()->secret_key; ?>' }
			});
			
			req.done(function(data){cbSuc(data)});
			
			req.fail(function(xhr,status,err){
				try {
					cbFail();
				} catch (e) {
					console.log(xhr);
					console.log(status);
					console.log(err);
				}
			});
			return deferred.promise();
		},
		renderSubs : function(obj,cb){
			
			$.each(obj,function(k,v){
				var theUL = $('#sp-subs-content ul');
				var liCont = $('<li/>',{})
					.append($('<div/>',{
						"class":"collapsible-header"
					}))
					.append($('<div/>',{
						"class":"collapsible-body"
					}));
					console.log(v);
					spSubs.request('GET','customers/',v.customer,'',function(d) {
						var t = new Date( v.current_period_end * 1000 );
						var liHeader = '<div class="col s1"></div>' +
										'<div class="col s4"><a href="<?php echo admin_url(); ?>user-edit.php?user_id='+d.metadata.wp_id+'">' + d.description + ' - ' + d.email + '</a></div>' +
										'<div class="col s1"><span class="sp-sub-status blue-text text-darken-1">' + v.status + '</span></div>' +
										'<div class="col s4">' + v.plan.name + '</div>' +
										'<div class="col s1"><i class="material-icons"><i class="material-icons">check_box_outline_blank</i></i></div>' +
										'<div class="col s1">' + t.toLocaleDateString("en-US") + '</div>';
						
						var liBody = '<span>Shipping address: ' + d.shipping.address.line1 + '&nbsp;' + d.shipping.address.line2 + ', ' + d.shipping.address.city + ', ' + d.shipping.address.state + '&nbsp;'+ d.shipping.address.postal_code + '&nbsp;' + d.shipping.address.country + '</span>';
						
						$('.collapsible-header',liCont).append(liHeader);
						$('.collapsible-body',liCont).append(liBody);
						
					},function(err){
						console.log(err)
					})
					
					liCont.appendTo(theUL);
			});
			if ($.isFunction(cb)) {cb()}
		},
		preloader : {
			fadein : function(cb) {
				$('#subs-content-loader').fadeIn(250,function() {if ($.isFunction(cb)) {cb()}});
			},
			fadeout : function(cb) {
				$('#subs-content-loader').fadeOut(250,function() {if ($.isFunction(cb)) {cb()}});
			}
		}
	}
	$(document).ready(function(){
		
		spSubs.request('GET','subscriptions','','?limit=50',function(data) {
			spSubs.renderSubs(data.data,spSubs.preloader.fadeout());
		})
		.promise()
		.then($('.collapsible').collapsible())
		
	});
</script>
<div id="stripepress-subscriptions">
	<div id="sp-subs-header" class="row" style="min-height:10em">
		<div class="col s12"><h1>Subscriptions</h1></div>
	</div>
	<div id="sp-subs-content" class="row" style="margin-left:2em;margin-right:20em;position:relative;min-height:50em">
		<div id="subs-content-loader" class="valign-wrapper" style="position:absolute;text-align:center;top:0;bottom:0;left:0;right:0;background-color:white">
			<div class="preloader-wrapper big active" style="margin:0 auto;">
			  <div class="spinner-layer spinner-blue">
				<div class="circle-clipper left">
				  <div class="circle"></div>
				</div><div class="gap-patch">
				  <div class="circle"></div>
				</div><div class="circle-clipper right">
				  <div class="circle"></div>
				</div>
			  </div>

			  <div class="spinner-layer spinner-red">
				<div class="circle-clipper left">
				  <div class="circle"></div>
				</div><div class="gap-patch">
				  <div class="circle"></div>
				</div><div class="circle-clipper right">
				  <div class="circle"></div>
				</div>
			  </div>

			  <div class="spinner-layer spinner-yellow">
				<div class="circle-clipper left">
				  <div class="circle"></div>
				</div><div class="gap-patch">
				  <div class="circle"></div>
				</div><div class="circle-clipper right">
				  <div class="circle"></div>
				</div>
			  </div>

			  <div class="spinner-layer spinner-green">
				<div class="circle-clipper left">
				  <div class="circle"></div>
				</div><div class="gap-patch">
				  <div class="circle"></div>
				</div><div class="circle-clipper right">
				  <div class="circle"></div>
				</div>
			  </div>
			</div>
		</div>
		<ul class="collapsible" data-collapsible="accordion">
			<li>
				<div class="collapsible-header">
					<div class="col s1"></div>
					<div class="col s4">Customer</div>
					<div class="col s1">Status</div>
					<div class="col s4">Plan</div>
					<div class="col s1"></div>
					<div class="col s1">Exp. Date</div>
				</div>
			</li>
			<?php /*
			foreach($subs['data'] as $sub){ 
				$cus = \Stripe\Customer::retrieve($sub['customer']);
			?>
				<li>
					<div class="collapsible-header"><i class="material-icons" style="color:<?php echo $icons['completed']['color']; ?>"><?php echo $icons['completed']['logo']; ?></i>Subscription</div>
					<div class="collapsible-body"><?php print_r(json_encode($cus,JSON_PRETTY_PRINT)); ?></div>
				</li>
			<?php }*/ ?>
		</ul>
	</div>
</div>