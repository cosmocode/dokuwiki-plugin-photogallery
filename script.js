/* DOKUWIKI:include_once lightGallery/js/lightgallery.min.js */
/* DOKUWIKI:include_once lightGallery/js/picturefill.min.js */
/* DOKUWIKI:include_once lightGallery/js/lg-fullscreen.min.js */
/* DOKUWIKI:include_once lightGallery/js/lg-thumbnail.min.js */
/* DOKUWIKI:include_once lightGallery/js/lg-video.min.js */
/* DOKUWIKI:include_once lightGallery/js/lg-autoplay.min.js */
/* DOKUWIKI:include_once lightGallery/js/lg-zoom.min.js */
/* !DOKUWIKI:include_once lightGallery/js/lg-hash.min.js */
/* !DOKUWIKI:include_once lightGallery/js/lg-pager.min.js */
/* DOKUWIKI:include_once lightGallery/js/jquery.mousewheel.min.js */
		
/**
 * Initialize lightGallery for pg-show class
 */
function InitPgGallery(tw,th,play){
		jQuery("ul.pg-show").lightGallery({
				thumbnail:true,
				autoplay:play,
				showAfterLoad:true,
				pause:4000,
				preload:1,
				mode:"lg-fade",
				thumbWidth:tw,
				thumbContHeight:th
		});
    return false;
}

//		alert("Sono qui");

/**
 * Attach click event to the poster <a> tag
   Runs when the DOM is ready
 */
jQuery(document).on('click', 'a.pg-start', function() {
	var $pgid = '#' + jQuery(this).data('pg-id');
	var $pg = jQuery($pgid);
	var $img = $pg.children('li').children('img');
//			alert("ID= " + $img.attr('src'));
	$img.trigger("click");
	return false;
});

// /**
 // * Attach click event to the poster <a> tag
	 // Runs when complete page is fully loaded, including all frames, objects and images
 // */
// jQuery(window).load(function(){
	// // Trigger thumbnails preload
	// jQuery('img.pg-preload').each(function(index) {
		// jQuery(this).attr('src', jQuery(this).data('src'));
	// });
	// //alert("ok window");
	// // Trigger images preload
	// // jQuery('ul.pg-show').each(function(index) {
			// // var $li = jQuery(this).children('li').first().data('src');
			// // //jQuery('<img src="'.$li.'"/>');
// // //					jQuery(this).attr('src', jQuery(this).data('src'));
	// // });
// });

//		jQuery("img.lazy").lazyload();
