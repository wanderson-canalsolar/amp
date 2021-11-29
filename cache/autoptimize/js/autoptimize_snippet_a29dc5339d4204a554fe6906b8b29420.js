/*!
 * Theia Sticky Sidebar v1.7.0
 * https://github.com/WeCodePixels/theia-sticky-sidebar
 *
 * Glues your website's sidebars, making them permanently visible while scrolling.
 *
 * Copyright 2013-2016 WeCodePixels and other contributors
 * Released under the MIT license
 */
(function($){var NectarStickyState=function(){this.scrollTop=$(document).scrollTop();this.scrollLeft=$(document).scrollLeft();this.bindEvents()};NectarStickyState.prototype.bindEvents=function(){var that=this;$(document).on('scroll',function(){that.scrollTop=$(document).scrollTop();that.scrollLeft=$(document).scrollLeft();});};var nectarStickyState=new NectarStickyState();$.fn.theiaStickySidebar=function(options){var defaults={'containerSelector':'','additionalMarginTop':0,'additionalMarginBottom':0,'updateSidebarHeight':true,'minWidth':0,'disableOnResponsiveLayouts':true,'sidebarBehavior':'modern','defaultPosition':'relative','namespace':'TSS'};options=$.extend(defaults,options);options.additionalMarginTop=parseInt(options.additionalMarginTop)||0;options.additionalMarginBottom=parseInt(options.additionalMarginBottom)||0;tryInitOrHookIntoEvents(options,this);function tryInitOrHookIntoEvents(options,$that){var success=tryInit(options,$that);if(!success){console.log('TSS: Body width smaller than options.minWidth. Init is delayed.');$(document).on('scroll.'+options.namespace,function(options,$that){return function(evt){var success=tryInit(options,$that);if(success){$(this).unbind(evt);}};}(options,$that));$(window).on('resize.'+options.namespace,function(options,$that){return function(evt){var success=tryInit(options,$that);if(success){$(this).unbind(evt);}};}(options,$that))}}
function tryInit(options,$that){if(options.initialized===true){return true;}
if($('body').width()<options.minWidth){return false;}
init(options,$that);return true;}
function init(options,$that){options.initialized=true;var existingStylesheet=$('#theia-sticky-sidebar-stylesheet-'+options.namespace);if(existingStylesheet.length===0){$('head').append($('<style id="theia-sticky-sidebar-stylesheet-'+options.namespace+'">.theiaStickySidebar:after {content: ""; display: table; clear: both;}</style>'));}
$that.each(function(){var o={};o.sidebar=$(this);o.options=options||{};o.container=$(o.options.containerSelector);if(o.container.length==0){o.container=o.sidebar.parent();}
o.sidebar.parents().css('-webkit-transform','none');o.sidebar.css({'position':o.options.defaultPosition,'overflow':'visible','-webkit-box-sizing':'border-box','-moz-box-sizing':'border-box','box-sizing':'border-box'});o.stickySidebar=o.sidebar.find('.theiaStickySidebar');if(o.stickySidebar.length==0){var javaScriptMIMETypes=/(?:text|application)\/(?:x-)?(?:javascript|ecmascript)/i;o.sidebar.find('script').filter(function(index,script){return script.type.length===0||script.type.match(javaScriptMIMETypes);}).remove();o.stickySidebar=$('<div>').addClass('theiaStickySidebar').append(o.sidebar.children());o.sidebar.append(o.stickySidebar);}
o.marginBottom=parseInt(o.sidebar.css('margin-bottom'));o.paddingTop=parseInt(o.sidebar.css('padding-top'));o.paddingBottom=parseInt(o.sidebar.css('padding-bottom'));var collapsedTopHeight=o.stickySidebar.offset().top;var collapsedBottomHeight=o.stickySidebar.outerHeight();o.stickySidebar.css('padding-top',1);o.stickySidebar.css('padding-bottom',1);collapsedTopHeight-=o.stickySidebar.offset().top;collapsedBottomHeight=o.stickySidebar.outerHeight()-collapsedBottomHeight-collapsedTopHeight;if(collapsedTopHeight==0){o.stickySidebar.css('padding-top',0);o.stickySidebarPaddingTop=0;}
else{o.stickySidebarPaddingTop=1;}
if(collapsedBottomHeight==0){o.stickySidebar.css('padding-bottom',0);o.stickySidebarPaddingBottom=0;}
else{o.stickySidebarPaddingBottom=1;}
o.stickySidebarVisible=o.stickySidebar.is(":visible");o.windowHeight=$(window).height();o.previousScrollTop=null;o.fixedScrollTop=0;resetSidebar();o.onScroll=function(o){if(!o.stickySidebarVisible||$('.ocm-effect-wrap.material-ocm-open').length>0){return;}
if(o.options.disableOnResponsiveLayouts){var sidebarWidth=o.sidebar.outerWidth(o.sidebar.css('float')=='none');if(sidebarWidth+50>o.container.width()){resetSidebar();return;}}
var scrollTop=nectarStickyState.scrollTop;var position='static';var cachedOffsetTop=o.sidebar.offset().top;if(scrollTop>=cachedOffsetTop+(o.paddingTop-o.options.additionalMarginTop)){var cachedOuterHeight=o.stickySidebar.outerHeight();var offsetTop=o.paddingTop+options.additionalMarginTop;var offsetBottom=o.paddingBottom+o.marginBottom+options.additionalMarginBottom;var containerTop=cachedOffsetTop;var containerBottom=cachedOffsetTop+getClearedHeight(o.container);var windowOffsetTop=0+options.additionalMarginTop;var windowOffsetBottom;var sidebarSmallerThanWindow=(cachedOuterHeight+offsetTop+offsetBottom)<o.windowHeight;if(sidebarSmallerThanWindow){windowOffsetBottom=windowOffsetTop+cachedOuterHeight;}
else{windowOffsetBottom=o.windowHeight-o.marginBottom-o.paddingBottom-options.additionalMarginBottom;}
var staticLimitTop=containerTop-scrollTop+o.paddingTop;var staticLimitBottom=containerBottom-scrollTop-o.paddingBottom-o.marginBottom;var top=o.stickySidebar.offset().top-scrollTop;var scrollTopDiff=o.previousScrollTop-scrollTop;if(o.stickySidebar.css('position')=='fixed'){if(o.options.sidebarBehavior=='modern'){top+=scrollTopDiff;}}
if(o.options.sidebarBehavior=='stick-to-top'){top=options.additionalMarginTop;}
if(o.options.sidebarBehavior=='stick-to-bottom'){top=windowOffsetBottom-cachedOuterHeight;}
if(scrollTopDiff>0){top=Math.min(top,windowOffsetTop);}
else{top=Math.max(top,windowOffsetBottom-cachedOuterHeight);}
top=Math.max(top,staticLimitTop);top=Math.min(top,staticLimitBottom-cachedOuterHeight);var sidebarSameHeightAsContainer=o.container.height()==cachedOuterHeight;if(!sidebarSameHeightAsContainer&&top==windowOffsetTop){position='fixed';}
else if(!sidebarSameHeightAsContainer&&top==windowOffsetBottom-cachedOuterHeight){position='fixed';}
else if(scrollTop+top-cachedOffsetTop-o.paddingTop<=options.additionalMarginTop){position='static';}
else{position='absolute';}}
if(position=='fixed'){var scrollLeft=nectarStickyState.scrollLeft;o.stickySidebar.css({'position':'fixed','width':getWidthForObject(o.sidebar)+'px','transform':'translateY('+top+'px)','left':(o.sidebar.offset().left+parseInt(o.sidebar.css('padding-left'))-scrollLeft)+'px','top':'0px'});}
else if(position=='absolute'){var css={};if(o.stickySidebar.css('position')!='absolute'){css.position='absolute';css.transform='translateY('+(scrollTop+top-o.sidebar.offset().top-o.stickySidebarPaddingTop-o.stickySidebarPaddingBottom)+'px)';css.top='0px';}
css.width=getWidthForObject(o.stickySidebar)+'px';css.left='';o.stickySidebar.css(css);}
else if(position=='static'){resetSidebar();}
if(position!='static'){if(o.options.updateSidebarHeight==true){o.sidebar.css({'min-height':o.stickySidebar.outerHeight()+o.stickySidebar.offset().top-o.sidebar.offset().top+o.paddingBottom});}}
o.previousScrollTop=scrollTop;};o.onScroll(o);$(document).on('scroll.'+o.options.namespace,function(o){return function(){o.onScroll(o);};}(o));$(window).on('load.'+o.options.namespace,function(o){o.stickySidebarVisible=o.stickySidebar.is(":visible");o.windowHeight=$(window).height();}(o));$(window).on('resize.'+o.options.namespace,function(o){return function(){o.stickySidebarVisible=o.stickySidebar.is(":visible");o.windowHeight=$(window).height();o.stickySidebar.css({'position':'static'});o.onScroll(o);};}(o));if(typeof ResizeSensor!=='undefined'){new ResizeSensor(o.stickySidebar[0],function(o){return function(){o.onScroll(o);};}(o));}
function resetSidebar(){o.fixedScrollTop=0;o.sidebar.css({'min-height':'1px'});o.stickySidebar.css({'position':'static','width':'','transform':'none'});}
function getClearedHeight(e){var height=e.height();e.children().each(function(){height=Math.max(height,$(this).height());});return height;}});}
function getWidthForObject(object){var width;try{width=object[0].getBoundingClientRect().width;}
catch(err){}
if(typeof width==="undefined"){width=object.width();}
return width;}
return this;}})(jQuery);