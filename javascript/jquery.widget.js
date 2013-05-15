/**
 * Author:        websiteman@gmail.com
 * Desription:    Adds MyServices widget to the page by calling adding a placeholder div and refrencing it.
 * Use:           pvsandbox.Widgets.MyServices.init('#placeholderId');
 * Dependency:    jQuery 1.5+
 */

// Register global namespaces
window.pvsandbox = window.pvsandbox || {};
window.pvsandbox.Widgets = window.pvsandbox.Widgets || {};
window.pvsandbox.Widgets.MyServices = window.pvsandbox.Widgets.MyServices || {};

(function() {
      
   this.endpoint = 'widgets/myservices.php?jsonp=true',
   this.css = 'templates/002-redesign/css/widgets/myservices.css',

   this.getPath = function(path) {
      var protocol = 'http' + (/^https/.test(window.location.protocol)?'s':'');
      var host = window.location.hostname;
      
      // make sure the host is on *.pvsandbox.com
      if(host.indexOf('pvsandbox.com') < 0)
      {
         host = "www.pvsandbox.com";
      } 
      else 
      {
         // make sure we're not under a paricular sub domain
         // must accomodate both dev and production url maps 
         var urlFilter = ['iservices', 'partner','truerep'];
         var hostBits = window.location.hostname.split('.');

         if(jQuery.inArray(hostBits[0], urlFilter) > -1)
         {
            hostBits.shift();
            host = hostBits.join('.');
         }
         
         // prod ssl fix 
         if(host == 'pvsandbox.com')
            host = 'www.'+host;
      }
      
      return protocol +'://'+ host +'/'+ path;
   }, 
   
  
   
   this.init = function(selector) {

      var widget = this;

      jQuery("<link/>", {
         rel: "stylesheet",
         type: "text/css",
         href: widget.getPath(widget.css)
      }).appendTo("head");

      jQuery.ajax({
         dataType: "jsonp",
         url: widget.getPath(widget.endpoint),
         success: function(data){
            if(jQuery(data.view).children().length > 0) {
               jQuery(selector).hide().html(data.view).fadeIn('slow');
               widget.setup();
            }
         },
         error: function(jqXHR, textStatus, errorThrown){
            // insert debug code here
         },
         converters:{ "text html":  function(data, type){
            data = jQuery.parseJSON(data);
            return data;
         }}
         
       });
   }, // this.init

   this.setup = function() {

      var widget = this;

      var myCredits = jQuery('#myCredits');
      var myServices = jQuery('#myServices');

      var creditsContainer = jQuery('#creditsContainer');
      var servicesContainer = jQuery('#servicesContainer');

      var servicesNav = jQuery('#servicesNav');
      var servicesNavContainer = jQuery('#servicesNavContainer');

      var closeCredits = function() {
         if(myCredits.hasClass('open'))
         {
            myCredits.removeClass('open');
            creditsContainer.hide().removeClass('addShadow');
         }
         if ( myServices.length == 0 )
         {
            myCredits.removeClass('removeGradient');
            servicesNav.removeClass('addShadow');
         }
      };

      var closeServices = function() {
         if(myServices.hasClass('open'))
         {
            myServices.removeClass('open');
            servicesNav.removeClass('addShadow');
            servicesContainer.hide();
         }
      };

     // if one tab exists, float it right
     if ( myServices.length == 0 || myCredits.length == 0 )
     {
        myServices.css('float', 'right');
        myCredits.css('float', 'right');
     }

     // Adjust services container width for ie7 if only one tab exists (float fix)
     if ( jQuery.browser.msie && jQuery.browser.version < 8 && ( myServices.length == 0 || myCredits.length == 0 ) )
     {
        servicesNavContainer.css('width', '285');
     }

     myCredits.click( function() {
        if ( myServices.length == 0 )
        {
           myCredits.toggleClass('removeGradient');
           servicesNav.toggleClass('addShadow');
        }
        else
        {  
           creditsContainer.addClass('addShadow');
        }

        jQuery(myCredits).toggleClass('open');
        creditsContainer.toggle();
        closeServices();
     });

     myServices.click( function() {
        if(jQuery('#servicesContainer').length == 0) {
           return false;
        }
        jQuery(myServices).toggleClass('open');
        servicesNav.toggleClass('addShadow');  // toggle shadow first. Don't reverse the order. It crash IE7
        servicesContainer.toggle();
        closeCredits();
     });

     jQuery(document).click( function(e) {
        if ( jQuery( e.target ).parents("#servicesNavContainer").attr("id") === undefined ) {
           closeServices();
           closeCredits();
        }
     });

     jQuery('#servicesNavContainer a').click( function(e){
        e.stopPropagation();
     });

   } // this.setup

}).apply(pvsandbox.Widgets.MyServices);