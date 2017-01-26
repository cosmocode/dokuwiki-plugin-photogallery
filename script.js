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
 * Add a quicklink to the media popup
 */
/*function gallery_plugin(){
    var $opts = jQuery('#media__opts');
    if(!$opts.length) return;
    if(!window.opener) return;

    var glbl = document.createElement('label');
    var glnk = document.createElement('a');
    var gbrk = document.createElement('br');
    glnk.name         = 'gallery_plugin';
    glnk.innerHTML    = LANG.plugins.gallery.addgal; //FIXME localize
    glnk.style.cursor = 'pointer';

    glnk.onclick = function(){
        var $h1 = jQuery('#media__ns');
        if(!$h1.length) return;
        var ns = $h1[0].innerHTML;
        opener.insertAtCarret('wiki__text','{{gallery>'+ns+'}}');
        if(!dw_mediamanager.keepopen) window.close();
    };

    $opts[0].appendChild(glbl);
    glbl.appendChild(glnk);
    $opts[0].appendChild(gbrk);
}*/

/**
 * Display a selected page and hide all others
 */
/*function gallery_pageselect(e){
    var galid = e.target.hash.substr(10,4);
    var $pages = jQuery('div.gallery__'+galid);
    $pages.hide();
    jQuery('#'+e.target.hash.substr(1)).show();
    return false;
}*/

/**
 * Initialize lightGallery for pg-show class
 */
function InitPgGallery(tw,th){
		jQuery("ul.pg-show").lightGallery({
				thumbnail:true,
				autoplay:true,
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
 */
jQuery(document).on('click', 'a.pg-start', function() {
	var $pgid = '#' + jQuery(this).data('pg-id');
	var $pg = jQuery($pgid);
	var $img = $pg.children('li').children('img');
//			alert("ID= " + $img.attr('src'));
	$img.trigger("click");
	return false;
});

jQuery(window).load(function(){
	// Trigger thumbnails preload
	jQuery('img.pg-preload').each(function(index) {
		jQuery(this).attr('src', jQuery(this).data('src'));
	});
	//alert("ok window");
	// Trigger images preload
	// jQuery('ul.pg-show').each(function(index) {
			// var $li = jQuery(this).children('li').first().data('src');
			// //jQuery('<img src="'.$li.'"/>');
// //					jQuery(this).attr('src', jQuery(this).data('src'));
	// });
});

// initialize the lightbox mechanism
// jQuery("a.lightbox, a[rel^='lightbox']").swipebox({
		// loopAtEnd: true
// });

// gallery_plugin();

// hide all pages except the first one
// var $pages = jQuery('div.gallery_page');
// $pages.hide();
// $pages.eq(0).show();

//		jQuery("img.lazy").lazyload();
