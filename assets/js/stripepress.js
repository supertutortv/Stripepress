(function($) {
	
	var wto;
	$('input.sp_api_input').change(function(e){
		e.preventDefault();
		
		var t = $(this);
		
		var data = {
			key : t.attr('name'),
			value : t.val(),
			action : 'update_api_keys'
		};
		
		wto = setTimeout(function() {
			$.post(
			spAjax.ajaxURL,
			data,
			function(r) {
				if (r.success) {
					t.toggleClass("valid");
					t.siblings("label").attr("data-success","Saved");
					
						setTimeout(function(){
							t.toggleClass("valid");
							t.siblings("label").attr("data-success","Saved");
						},6000);
					console.log(r);
				} else {
					
				}
				
			}
		).fail(function(xhr,status,error){
			var l = [xhr,status,error];
			console.log(l);
		});
			
		}, 500);
		
	});
	
	$('input#stripepress_testmode').change(function(e){
		e.preventDefault();
		
		$(this).val(this.checked);
		
		if (this.checked) {
			$('.testtxt').animate({"color":"red"}, 250);
			$('.livetxt').animate({"color":"#9e9e9e"}, 250);
		} else {
			$('.livetxt').animate({"color":"red"}, 250);
			$('.testtxt').animate({"color":"#9e9e9e"}, 250);
		}
		
		var data = {
			value : $(this).val(),
			action : 'sp_test_mode'
		};
		
		$.post(
			spAjax.ajaxURL,
			data,
			function(r) {
				//$(this).siblings().css({"color":"black"});
			}
		);
		
	});
	
	$('.key_tester').click(function(e){
		e.preventDefault();
		
		var data = {
			action : 'sp_test_api_keys'
		};
		
		$.post(
			spAjax.ajaxxURL,
			data,
			function(r) {
				console.log(r);
			}
		);
		
	});
	
	
})(jQuery);