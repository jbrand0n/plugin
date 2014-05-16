jQuery(function($){
	$('#syncnow').click(function(){
		var data = {
			action: 'inf-member-action',
			sync: 'now'      // We pass php values differently!
		};
	
		$.ajax({
			type: "POST",
			url: inf_member_object.url,
			data: data,
			//dataType: "json",
			success: function(json){//alert(json);
			}
		});

	});
});