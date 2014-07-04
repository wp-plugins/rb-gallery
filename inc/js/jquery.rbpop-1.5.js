jQuery(document).ready(function($) {

  // global variables for script
  var current, size;
  
  
  $('.lightboxTrigger').click(function(e) {

    
    // prevent default click event
    e.preventDefault();
    
    // grab href from clicked element
    var image_href = $(this).attr("href"); 

    // determine the index of clicked trigger
    var slideNum = $('.lightboxTrigger').index(this);
    
    // find out if #lightbox exists
    if ($('#lightbox').length > 0) {        
      // #lightbox exists
      $('#lightbox').fadeIn(300);
      $('#thumbwrapper').fadeIn(300);
      // #lightbox does not exist - create and insert (runs 1st time only)
    } else {                                
      // create HTML markup for lightbox window
      var lightbox =
          '<div id="lightbox">' +
          '<div id="lightbox-wrapper">' +
          '<div id="slideshow">' +
          '<ul></ul>' +        
          '<div class="nav">' +
          '<a href="#prev" class="prev slide-nav"></a>' +
          '<a href="#next" class="next slide-nav"></a>' +
          '</div>' +
          '</div>' +
          '</div>' +
          '</div>' +
          '<div id="thumbwrapper" class="bottom"><ul></ul></div>';
      
      //insert lightbox HTML into page
      $('body').append(lightbox);

      // fill lightbox with .lightboxTrigger hrefs in #imageSet
      $('#imageSet').find('.lightboxTrigger').each(function() {

        var $href = $(this).attr('href');
        var $title = $(this).attr('title');

        if(typeof $title == 'undefined') {
            $title = '';
        }

        // Create slideshow big
        $('#slideshow ul').append(
          '<li>' +
          '<img src="' + $href + '" >' +
          '<br /><div class="lightbox-title">' + $title + '</div>' +
          '</li>'
        );
        

      });

      // fill lightbox with thumbnail gallery .lbthumb hrefs in #imageSet
      var popjumpto = 0;
      var popjumpid = 0;

      $('#imageSet').find('.lbthumb').each(function() {

        var $img = $(this).attr('src');
        console.log( $(this).attr('src') );

        // Create thumbnail menu
        $('#thumbwrapper ul').append(
            '<li class="popjumpid_' + popjumpid++ + ' "><a href="javascript:jQuery.rbjumpto(' + popjumpto++ + ');" id="rbpopto">' +
            '<img src="' + $img + '" style="height:50px;width:50px;">' +
            '</a></li>'
          );

      });
      
    }

    //process the jumpto the correct thumbnail box when clicked
    jQuery.rbjumpto = function(id) {
       slideNum = id;
       $('#slideshow ul > li').hide();
       $('#slideshow ul > li:eq(' + slideNum + ')').show();
       current = slideNum;

       toggle_active_class_thumblist( current );
       position_thumbnail_menu( current );
    }
	
    // setting size based on number of objects in slideshow
    size = $('#slideshow ul > li').length;
    
    // hide all slide, then show the selected slide
    var hideSlide = $('#slideshow ul > li').hide();

    var showSlide = $('#slideshow ul > li:eq(' + slideNum + ')').show();

    current = slideNum;

    toggle_active_class_thumblist( current );
    position_thumbnail_menu( current );
    center_big_image( current );

  });

    function center_big_image( slidenum ) {
      //append to class #lightbox-wrapper left
      var $imgwidth = Math.floor($(window).width() * 0.4 - (slidenum * 45 + 45 * 0.4));
      var $imghigh = Math.floor($(window).height() * 0.3 - (slidenum * 50 + 50 * 0.3));

      $( "#lightbox-wrapper" ).fadeTo(0, 0.8, function() {
        $(this).css( "left",$imgwidth );
        $(this).css( "top",$imghigh );
      }).fadeTo(0, 1);
    }

    // shows active thumbnail border
    function toggle_active_class_thumblist( slidenum ) {

      var popjumid = '#thumbwrapper ul li.popjumpid_' + slidenum;
      $( '#thumbwrapper ul li' ).removeClass( "active" );
      $( popjumid ).addClass( "active" );
    }

    // Centers thumbnail menu
    function position_thumbnail_menu( slidenum ) {

      var $imgwidth = Math.floor($(window).width() * 0.5 - (slidenum * 50 + 50 * 0.5));
      $( "#thumbwrapper ul" ).fadeTo(100, 0.8, function() {
        $(this).css( "left",$imgwidth );
      }).fadeTo(100, 1);

    }

  //Click anywhere on the page to get rid of lightbox window
  $('body').on('click', '#lightbox', function() { 
    $('#lightbox').fadeOut(300);
    $('#thumbwrapper').fadeOut(300);
    $('#thumbwrapper ul li').removeClass('active');
  });
  
  // show/hide navigation when hovering over #slideshow
  $('body').on(
    { mouseenter: function() {
      $('.nav').fadeIn(300);
    }, mouseleave: function() {
      $('.nav').fadeOut(300);
    }
    },'#slideshow');
  
  // navigation prev/next
  $('body').on('click', '.slide-nav', function(e) {
    
    // prevent default click event, and prevent event bubbling to prevent lightbox from closing
    e.preventDefault();
    e.stopPropagation();
    
    var $this = $(this);
    var dest;
    
    // looking for .prev
    if ($this.hasClass('prev')) {
      dest = current - 1;
      if (dest < 0) {
        dest = size - 1;
      }
    } else {
      // in absence of .prev, assume .next
      dest = current + 1;
      if (dest > size - 1) {
        dest = 0;
      }
    }
    
    // fadeOut curent slide, FadeIn next/prev slide
    //$('#slideshow ul > li:eq(' + current + ')').fadeOut(200);
    //var CurrImg = $('#slideshow ul > li:eq(' + dest + ')').fadeIn(200);

    $('#slideshow ul > li:eq(' + current + ')').hide();
    var CurrImg = $('#slideshow ul > li:eq(' + dest + ')').show();
	
    current = dest;
    toggle_active_class_thumblist( current );
    position_thumbnail_menu( current );

  });
  
  
});