jQuery(document).ready(function($){
	//focus on the nearest text input when a radio is checked
	$("input[type=radio]").on ('change',function() {
		if ($(this).is(':checked')) {
			$(this).closest('p').find('input[type=text], textarea').focus();
	}});
	
});
