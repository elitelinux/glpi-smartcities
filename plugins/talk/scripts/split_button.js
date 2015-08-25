split_button = function() {
   var splitBtn = $('#x-split-button');

   // unfold status list
   $(document).on("click", '.x-button-drop', function(event) {
      splitBtn.toggleClass('open');
   });

   $(document).on("click", '.x-split-button', function(event) {
      event.stopPropagation();
   });

   //click on an element of status list
   $(document).on("click", '.x-button-drop-menu li', function(event) {
      if (event.target.children.length) {
         //clean old status class
         current_class = $('.x-button-drop').attr('class');
         current_class = current_class.replace('x-button x-button-drop', ''); // don't remove native classes
         current_class_arr = current_class.split(" ");
         $('.x-button-drop').removeClass(current_class_arr);

         //find status
         match = event.target.children[0].src.match(/.*\/(.*)\.png/);
         cstatus = match[1];

         //add status to dropdown button
         $('.x-button-drop').addClass(cstatus);

         //fold status list
         splitBtn.removeClass('open');
      }
   });

   //fold status list on click on document
   $(document).on("click", function(event) {
      if (splitBtn.hasClass('open')) {
         splitBtn.removeClass('open');
      }
   });
}