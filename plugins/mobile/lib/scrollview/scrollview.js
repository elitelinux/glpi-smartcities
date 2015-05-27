function ResizePageContentHeight(page)
{
   var $page = $(page);
   var $content = $page.children("#tab_content");
   var hh = $page.children(".ui-header").outerHeight(); hh = hh ? hh : 0;
   var fh = $page.children(".ui-footer").outerHeight(); fh = fh ? fh : 0;
   var pt = parseFloat($content.css("padding-top"));
   var pb = parseFloat($content.css("padding-bottom"));
   var wh = window.innerHeight;
   $content.height(wh - (hh + fh) - (pt + pb));
}

function mobileScrollView() {   
   var $page = $('[data-role=page]');

   // This code that looks for [data-scroll] will eventually be folded
   // into the jqm page processing code when scrollview support is "official"
   // instead of "experimental".

   $page.find(":jqmData(scroll)").each(function(){
      var $this = $(this);
      // XXX: Remove this check for ui-scrolllistview once we've
      //      integrated list divider support into the main scrollview class.
      if ($this.hasClass("ui-scrolllistview"))
         $this.scrolllistview();
      else
      {
         var st = $this.jqmData("scroll") + "";
         var paging = st && st.search(/^[xy]p$/) != -1;
         var dir = st && st.search(/^[xy]/) != -1 ? st.charAt(0) : null;

         var opts = {};
         if (dir)
            opts.direction = dir;
         if (paging)
            opts.pagingEnabled = true;
            
         var method = $this.jqmData("scroll-method");
         if (method)
            opts.scrollMethod = method;
         
         //seem to fix click on form controls on mobile, controls still broken on chrome desktop
         opts.delayedClickEnabled = false;

         $this.scrollview(opts);
      }
   });

   // For the demos, we want to make sure the page being shown has a content
   // area that is sized to fit completely within the viewport. This should
   // also handle the case where pages are loaded dynamically.

   ResizePageContentHeight($('[data-role=page]'));
};

$(document).live("orientationchange", function(event) {
   ResizePageContentHeight($(".ui-page"));
});
