$(function () {
	
	$('#add_column').click(function (event) {
		var li = $('<li>');
		li.append(IMPORPAYMENT_TFIELD);
		$('#columns_order').append(li);
		updateSelects();
	});


	updateSelects = function() {
		var nb_selected = $('#columns_order > li > select > option[value*=comment]:selected').length;
		var Tab = $('#columns_order > li > select');
		Tab.push($(IMPORPAYMENT_TFIELD));
		Tab.each(function(i, item) {
			var i = $(item).children('option[value*=comment]:last').val().replace('comment', '');
			console.log(item);
			i = parseInt(i) + 1;
//			console.log(i);
			$(item).append('<option value="comment'+i+'">Com '+i+'</option>');
			
		});
	};
	
	removeLine = function (obj) {
		$(obj).parent().remove();
	};

});