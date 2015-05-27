
function audio() {

	document.getElementById('audiotag1').play();
}


function notify(titulo, texto, icone, id) {
  'use strict';

  if (!("Notification" in window)) {
   // alert("Your browser doesn't support Notifications, but I brought this ooold alert for you :)")
  } else if (Notification.permission === 'granted') {
    var notification = new Notification(titulo, {
         icon: icone,      
     		body: texto
    });
  } else {
  	
        
    Notification.requestPermission(function(permission) {
      if (!('permission' in Notification)) {
        Notification.permission = permission;
      }
      if (Notification.permission === 'granted') {
        var notification = new Notification(titulo, {
            icon: icone,     		
     			body: texto
        })       
      }
    })
  }
  

notification.onclick = function () { window.open('ticket.form.php?id='+id); }
//notification.onshow = function() { setTimeout('notification.close()', 200000); }

notification.onclose = function(){
	var d = new Date();
	if(d-notification.openedat>3990){
		// DISPLAY A NEW NOTIFICATION RECURSIVELY
	}
}   
  
  	 //audio
    //document.getElementById('audiotag1').play();  
  
};
	
	
function notify2(title1, text1){

			var unique_id = $.gritter.add({
				// (string | mandatory) the heading of the notification
				title: title1,
				// (string | mandatory) the text inside the notification
				text: text1,
				sticky: true,
				// (int | optional) the time you want it to be alive for before fading out
				time: '',
				class_name: 'my-sticky-class'
			});

			// You can have it return a unique id, this can be used to manually remove it later using
			/*

			setTimeout(function(){

				$.gritter.remove(unique_id, {
					fade: true,
					speed: 'slow'
				});

			}, 6000)
			*/

			return false;

		}	