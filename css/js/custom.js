$(document).on('focusin', '.searchbox', function(e) {
	
	//$('.searchbox').css("width","190px");
	$('.searchbox').animate({width:'190px'}, "slow", "easeOutBack");
	$('.searchbox').css("margin-right","10px");

	/*if ($('.searchbox').hasClass('col-lg-2')) {
                $('.searchbox').removeClass('col-lg-2')
                $('.searchbox').addClass('col-lg-8')
            }*/
  //alert( "Handler for .focus() called." );
})


$(document).on('focusout', '.searchbox', function(e) {
	
	//$('.searchbox').css("width","10px");
	$('.searchbox').animate({width:'10px'}, "slow", "easeOutBack");
	$('.searchbox').css("margin-right","70px");
	
})
