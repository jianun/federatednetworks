/*  Collapse Functions, version 2.0
 *
 *--------------------------------------------------------------------------*/
String.prototype.trim = function() {
  return this.replace(/^\s+|\s+$/g,"");
}

function createCookie(name,value,days) {
  if (days) {
    var date = new Date();
    date.setTime(date.getTime()+(days*24*60*60*1000));
    var expires = "; expires="+date.toGMTString();
  } else {
    var expires = "";
  }
  document.cookie = name+"="+value+expires+"; path=/";
}

function readCookie(name) {
  var nameEQ = name + "=";
  var ca = document.cookie.split(';');
  for(var i=0;i < ca.length;i++) {
    var c = ca[i];
    while (c.charAt(0)==' ') {
      c = c.substring(1,c.length);
    }
    if (c.indexOf(nameEQ) == 0) {
      return c.substring(nameEQ.length,c.length);
    }
  }
  return null;
}

function eraseCookie(name) {
  createCookie(name,"",-1);
}

function addExpandCollapse(id, expandSym, collapseSym, accordion) {
  jQuery('#' + id + ' span.expand').live('click', function() {
    if (accordion==1) {
      var theDiv = jQuery(this).parent().parent().find('span.collapse').parent().children('div');
      jQuery(theDiv).hide('normal');
      jQuery(this).parent().parent().find('span.collapse').removeClass('collapse').addClass('expand');
      createCookie(theDiv.attr('id'), 0, 7);
      jQuery(this).parent().parent().find('span.sym').html(expandSym);
      //jQuery('#' + id + ' .expand .sym').html(expandSym);
    }
    expandArch(this, expandSym, collapseSym);
    return false;
  });
  jQuery('#' + id + ' span.collapse').live('click', function() {
    collapseArch(this, expandSym, collapseSym);
    return false;
  });
}

function expandArch(symbol, expandSym, collapseSym) {
    var theDiv = jQuery(symbol).parent().children('div');
    try {
      if (collapsItems[jQuery(theDiv).attr('id')]!='') {
        jQuery(theDiv).html(collapsItems[jQuery(theDiv).attr('id')]).show('normal');
      } else {
        jQuery(theDiv).show('normal');
      }
    } catch (err) {
      jQuery(theDiv).show('normal');
    }
    jQuery(symbol).removeClass('expand').addClass('collapse');
    jQuery(symbol).children('.sym').html(collapseSym);
  createCookie(theDiv.attr('id'), 1, 7);
}
function collapseArch(symbol, expandSym, collapseSym) {
    var theDiv = jQuery(symbol).parent().children('div');
    jQuery(theDiv).hide('normal');
    jQuery(symbol).removeClass('collapse').addClass('expand');
    jQuery(symbol).children('.sym').html(expandSym);
  createCookie(theDiv.attr('id'), 0, 7);
}
