/**
 * @depend jquery.nicescroll.js
 */

var j_window = $(window),
  j_flybar  = $('#flybar');
  j_sidebar = $('.sidebar_popout'),
  j_content = $('#document');

var docs = {
  init: function() {
    this.sidebar_popout();
    this.url_rewrite();
  },

  sidebar_popout: function() {
    $('#sidebar_button').on('click', function() {
      var sidebar_hidden_class = 'sidebar_hidden',
        animation_speed = 200;
      
      if ( !j_sidebar.hasClass(sidebar_hidden_class) ) {
        j_sidebar.stop()
             .animate({
              marginLeft: -270
             }, animation_speed)
             .addClass(sidebar_hidden_class);
        j_content.stop()
             .animate({
              paddingLeft: 18
             }, animation_speed);
        j_flybar.stop()
             .animate({
              marginLeft: -282
             }, animation_speed);
        $(this).stop()
             .animate({
                marginLeft: -270
             }, animation_speed);
      }
      else {
        j_sidebar.stop()
             .animate({
              marginLeft: 0
             }, animation_speed)
             .removeClass(sidebar_hidden_class);
        j_content.stop()
             .animate({
                paddingLeft: 300
             }, animation_speed);
        j_flybar.stop()
             .animate({
              marginLeft: 0
             }, animation_speed);
        $(this).stop()
             .animate({
                marginLeft: 0
             }, animation_speed);
      }
    });
  },

  url_rewrite: function() {
    $('.sidebar .menu a').each(function() {
        var j_this = $(this),
          href = j_this.attr('href'),
          url = new RegExp(window.location.protocol + '//' + window.location.host + window.location.pathname);
      
      if (url.test(href)) {
          href = href.replace(url, '#')   // Replace domain and path
                 .replace(/\//g, '-') // Replace '/' with '-'
                 .replace(/-$/, '');  // Remove last '-'
          
          j_this.attr('href', encodeURI(href));
        }
    });
  }
}

$(function() {
  docs.init();

  /* Enable Nice Scroll on docs sidebar */
//  j_sidebar_docs.niceScroll({
//    zindex: 1,
//    cursorcolor: '#bbb',
//    cursorwidth: '7px',
//    cursorborder: '0',
//    cursorborderradius: '10px',
//    autohidemode: false,
//    railoffset: { left: 15 }
//  });

});
