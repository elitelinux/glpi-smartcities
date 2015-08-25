read_more = function() {
   $(document).on("click", ".long_text .read_more a", function(event) {
      $(this).parents('.long_text').removeClass('long_text');
      $(this).parent('.read_more').remove();
      return false;
   });
}
