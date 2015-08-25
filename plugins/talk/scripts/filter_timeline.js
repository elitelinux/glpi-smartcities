filter_timeline = function() {
   $(document).on("click", '.filter_timeline li a', function(event) {
      //hide all elements in timeline
      $('.h_item').addClass('h_hidden');

      //reset all elements
      if ($(this).hasClass('reset')) {
         $('.filter_timeline li a img').each(function(el2) {
            $(this).attr('src', $(this).attr('src').replace('_active', ''));
         })
         $('.h_item').removeClass('h_hidden');
         return;
      }

      //activate clicked element
      var current_el = $(this).children('img');
      $(this).toggleClass('h_active');
      if (current_el.attr('src').indexOf('active') > 0) {
         current_el.attr('src',  current_el.attr('src').replace('_active', ''));
      } else {
         current_el.attr('src', current_el.attr('src').replace(/\.(png)$/, '_active.$1'));
      }

      //find active classname
      active_classnames = [];
      $('.filter_timeline .h_active').each(function(index) {
         active_classnames.push(".h_content."+$(this).attr('class').replace(' h_active', ''));
      })

      $(active_classnames.join(', ')).each(function(index){
         $(this).parent().removeClass('h_hidden');
      })

      //show all items when no active filter 
      if (active_classnames.length == 0) {
         $('.h_item').removeClass('h_hidden');
      }
   });
}