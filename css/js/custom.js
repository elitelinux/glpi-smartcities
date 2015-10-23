$(document).on('focusin', '.searchbox', function(e) {
	
	//$('.searchbox').css("width","190px");
	$('.searchbox').animate({width:'145px'}, "slow", "easeOutBack");
	$('.searchbox').css("margin-right","10px");

})


$(document).on('focusout', '.searchbox', function(e) {
	
	//$('.searchbox').css("width","10px");
	$('.searchbox').animate({width:'10px'}, "slow", "easeOutBack");
	$('.searchbox').css("margin-right","70px");
	
})


/*
$(document).ready(function(){	

//var ww = $(window).width();

//if(ww > 768){
	for (i = 1; i < 9; i++) {
    $('#menux'+[i]).addClass('dropdown'); 	 
	}	

$('.dropdown').hover(function(){ 
  $('.dropdown-toggle', this).trigger('click'); 
});

//}
});




$(document).ready(function(){	

var ww = $(window).width();

if(ww > 768){
	for (i = 1; i < 9; i++) {
    $('#menu'+[i]).addClass('dropdown'); 	 
	}

    $(".dropdown").hover(            
        function() {
            $('.dropdown-menu', this).not('.in .dropdown-menu').stop( true, true ).slideDown("fast");
            $(this).removeClass('open');        
        },
        function() {
            $('.dropdown-menu', this).not('.in .dropdown-menu').stop( true, true ).slideUp("fast");
            $(this).removeClass('open');       
        }
    );
}
});
*/

/*
$(window).resize(function(){

//var ww = $('#page',$(this)).width();	
var ww = $(window).width();

//alert(ww);

if(ww < 768){
	
	for (i = 1; i < 9; i++) {	      
    $('#menu'+[i]).removeClass('dropdown');
    //$('#menu'+[i]).removeClass('open');       
	}	
		
}


//if(ww > 769){
else {	

	for (i = 1; i < 9; i++) {
    $('#menu'+[i]).addClass('dropdown'); 	 
	}
	
    $(".dropdown").hover(            
        function() {
            $('.dropdown-menu', this).not('.in .dropdown-menu').stop( true, true ).slideDown("fast");
            $(this).toggleClass('open');        
        },
        function() {
            $('.dropdown-menu', this).not('.in .dropdown-menu').stop( true, true ).slideUp("fast");
            $(this).toggleClass('open');       
        }
    );	

}

});
*/

//});

