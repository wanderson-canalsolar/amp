/**** 
* AMP Framework Reset
*****/
    body{ font-family: sans-serif; font-size: 16px; line-height:1.4; overflow-y: hidden; }
    ol, ul{ list-style-position: inside }
    p, ol, ul, figure{ margin: 0 0 1em; padding: 0; }
    a, a:active, a:visited{ color:#ed1c24; text-decoration: none }
    a:hover, a:active, a:focus{}
    pre{ white-space: pre-wrap;}
    .left{float:left}
    /*.right{float:right}*/
    .hidden{ display:none }
    .clearfix{ clear:both }
    blockquote{ background: #f1f1f1; margin: 10px 0 20px 0; padding: 15px;}
    blockquote p:last-child {margin-bottom: 0;}
    .amp-wp-unknown-size img {object-fit: contain;}
    .amp-wp-enforced-sizes{ max-width: 100% }
    /* Image Alignment */
    .alignright {
        float: right;
    }
    .hide{
        display:none;
    }
    .alignleft {
        float: left;
    }
    .aligncenter {
        display: block;
        margin-left: auto;
        margin-right: auto;
    }
    amp-iframe { max-width: 100%; margin-bottom : 20px; }

    /* Captions */
    .wp-caption {
        padding: 0;
    }
    .wp-caption-text {
        font-size: 12px;
        line-height: 1.5em;
        margin: 0;
        padding: .66em 10px .75em;
        text-align: center;
    }

    /* AMP Media */
    amp-iframe,
    amp-youtube,
    amp-instagram,
    amp-vine {
        margin: 0 -16px 1.5em;
    }
    amp-carousel > amp-img > img {
        object-fit: contain;
    }

     
/****
* Container
*****/
.container {
    max-width: 600px;
    margin: 0 auto;
    padding: 8px 10px;
    height:auto;
}

/****
* AMP Sidebar
*****/
    amp-sidebar {
        width: 250px;
    }

    /* AMP Sidebar Toggle button */

    .amp-sidebar-button{
        position:relative;
        float:right;

        top: -40px;
    }
    .amp-sidebar-toggle  {
        
    }
    .amp-sidebar-toggle span  {
        display: block;
        height: 2px;
        margin-bottom: 5px;
        width: 22px;
        background: #E64F38;
    }
    .amp-sidebar-toggle span:nth-child(2){
        top: 7px;
    }
    .amp-sidebar-toggle span:nth-child(3){
        top:14px;
    }

    /* AMP Sidebar close button */
    .amp-sidebar-close{
        display: inline-block;
        padding: 5px 10px;
        font-size: 22px;
        color: #fff;
    }

/****
* AMP Navigation Menu with Dropdown Support
*****/
    .toggle-navigation ul{
        list-style-type: none;
        margin: 0;
        padding: 0;
        display: inline-block;
        width: 100%
    }
    .toggle-navigation ul li{
        font-size: 13px;
        border-bottom: 1px solid rgba(0, 0, 0, 0.11);
        padding: 11px 0px;
        width: 25%;
        float: left;
        text-align: center;
        margin-top: 6px
    }
    .toggle-navigation ul ul{
        display: none
    }
    .toggle-navigation ul li a{
        color: #eee;
        padding: 15px;
    }
    .toggle-navigation{
        display: none;
        background: #444;
    }

    /* GDPR */
    #cookie-consent-backdrop {
    width: 100%;
    height: 100%;
    z-index: 1002; /* places the modal overlay between the main page and the modal dialog*/
    background-color: #000;
    opacity: 0.5;
    position: fixed;
    top: 0;
    left: 0;
    margin: 0;
    padding: 0;
}
#cookie-consent-p{
    color:#fff;
}
#cookie-consent-ui {
    margin-left: auto;
    margin-right: auto;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 1003; /* places the modal dialog on top of everything else */
    position: fixed;
    padding: 10
px
;
    background-color: #000000b0;
    text-align: center;
}
#cookie-consent-ui h2 {
    text-align: center;
    color:#fff;
}
#scrollToTopButton{
    z-index:99;
}
/**** 
* Header
*****/
.s{
    height: 33px;
    border-radius: 4
px
;
    border: solid 1
px
 #00000040;
}
.amp-search-wrapper{
    display: inline-flex;
    margin-left:10px;
}
input[type="search"] { 
     border-top-left-radius: 4px; 
      border-bottom-left-radius: 4px;  
      font-size: 1.4rem;  
  
    }
input[type="submit"] { 
     width:2.5rem;  
     background-repeat: no-repeat; 
      background-image: url('https://192.168.15.154/site/wordpress/wp-content/uploads/2021/11/magnify.svg');  
      background-size: 1.4rem 1.4rem;  
      background-position: 50% 50%; 
       border-top-right-radius: 4px; 
        border-bottom-right-radius: 4px; 
         margin-left:-4px; 
         background-color: #E64F38;
         padding:10px 5px;
         border:none;
         fill: #fff;
        }

.amp-search-wrapper .screen-reader-text{
    display:none;
}
.amp-logo {
    width:100%;
    height:auto;
}
    .header h1{
        font-size: 1.5em;
    }
    .header .right{
        margin: 16px 5px 0px 5px;
    }
    .amp-phone, .amp-social, .amp-sidebar-button{
        display:inline-flex 
    }
    .amp-phone{
        top: 4px;
    }
    .header .amp-social{
        margin: 0px 19px;
    }
    .amp-sidebar-button{
        top: -55px;
    }


/**** 
* Loop
*****/
.wp-image-7416{
    margin-bottom: 40px;
}
.footer span{
    color:#fff;
    margin-right:10px;

}
#text-2 h4{
    color:#fff;
}
.loop-title{
    font-size: 1.2em;
    color:#000;
}
.loop-title a{
    color:#000;
}
.loop-category li{
    list-style-type: none;}
.potencias-amp{
    background-color: #EEEEEE;
    display: block;
    width: 100%;
    height: 50
px
;
    padding: 10
px
;
}
#sidebar .icon-facebook, #sidebar .icon-linkedin, #sidebar .icon-youtube-play, #sidebar .icon-instagram{
    margin-right: 10
px
;
    margin-left: 20
px
;
    margin-top: 20
px
;
}
.black-bg{
    background-color: #000;
    width:100%;
    color:#fff;
text-align:center;
padding:10px;
}
.black-bg h5{
    float:left;
    margin: 0 auto;
    margin-left:18%;
}
.spotify-div{
    height:25%;
    margin-left: 10%;
    margin-top: 10%;
    margin-bottom: 35%;
}
.div-ver-todos{
    margin-top:5px;
}
.ver-todos{
    text-align: center;
    color:#ce5b3d;
}
.h3-videos{
    text-align: center;
}
.h3-webinario{
    text-align: center;
}
.h3-entrevista{
    text-align: center;
}
.h3-papo{
    text-align: center;
}
.h3-guia{
    text-align: center;
}
.h3-artigos{
    text-align: center;
}
.h3-colunistas{
    text-align: center;
}
.h3-noticias{
    text-align: center;
}
#id-colunistas-feature{
    background-color: #f4f4f4;
}
.feat-blk #id-teste-amp-img-datahero{
    width:25%;
    border-radius: 50%;
}
#id-teste-amp-img-datahero p{
    color:#ce5b3d;

}
.nome-colunista-feature{
    color:#ce5b3d;
    margin-bottom: 0px;
    font-size:12px;
}
.texto-colunista-feature{
    color:#000 !important;
}
<!-- .loop-post1 > #id-teste-amp-img1 > .loop-img > a > #id-teste-amp-img-datahero{
    min-width:100%;
    margin-left: 1%;
} -->
#id-teste-amp-img1 > .loop-img{
    float:unset;
}
.header {
    background-color: #fff;
}
.loop-post1, .loop-post2, .loop-post3, .loop-post4{
    border-bottom: 1px solid #000;
}
.loop-img{
    float:left;
    margin-right:10px;
}
    .loops, .loops2{
        margin-bottom:10px;
        padding-bottom: 10px;
    }
    .loop-post{
        display: inline-block;
        width: 100%;
        margin: 6px 0px;
       
    }
    .loop-post .loop-img{
        float: left;
        margin-right: 15px;
    }
    .loop-post h2{
        font-size: 1.2em;
        margin: 0px 0px 8px 0px;
    }
    .loop-post p{
        font-size: 14px;
        color: #333;
        margin-bottom:6px;
    }
    .loop-post ul{
        list-style-type: none;
        display: inline-flex;
        margin: 0px;
        font-size: 14px;
        color: #666;
    }
    .loop-post ul li{
        margin-right:2px;
    }
    .loop-date{
        font-size:12px;
    }

   body > amp-sidebar{
    background-color: #56658B;
}
.amp-menu a{
    color: #C0CDD8;
    font-size:20px;
    font-weight:bold;
}

.nome-colunista{
    color: #CE5B3D;
    font-size:12px;
}
.capa-revista-amp{
    margin:auto;
}

.colunistas{
    margin:10px;
    text-align:center;
}
#carousel-colunistas5 img{
    width: 50%;
    border-radius:40%;
}
.hr{
    height:3px;
    background-color: #2D4C8C;
}
.pp-user-avatar{
    width:50%;
    height:auto;
}
.wpb_wrapper{
    margin-top:40px;
    margin-bottom:40px;
}
#canal-grid-7 > li > a > .no-lazyload{
    width:100%;
height:auto;}

#canal-grid-1315 > li > a > .no-lazyload{
    width:100%;
height:auto;}
/****
* Single
*****/
    /** Related Posts **/
    .amp-related-posts ul{
        list-style-type:none;
    }
    .amp-related-posts ul li{
        display: inline-block;
        line-height: 1.3;
        margin-bottom: 5px;
    }
    .amp-related-posts amp-img{
        float: left;
        width: 100px;
        margin: 0px 10px 0px 0px;
    }

#carousel-colunistas{
    width:30%;
}
.div-colunistas{
    width:30%;
}
#canal-grid-7{
    list-style-type:none;
}
#canal-grid-1315{
    list-style-type:none;
}
/**** 
* Comments
*****/
	.comments_list ul{
	    margin:0;
	    padding:0
	}
	.comments_list ul.children{
	    padding-bottom:10px;
		margin-left: 4%;
		width: 96%;
	}
	.comments_list ul li p{
        margin: 0;
        font-size: 14px;
        clear: both;
        padding-top: 5px;
	}
    .comments_list ul li .says{
        margin-right: 4px;
    }
	.comments_list ul li .comment-body{
	    padding: 10px 0px 15px 0px;
	}
	.comments_list li li{
	    margin: 20px 20px 10px 20px;
	    background: #f7f7f7;
	    box-shadow: none;
	    border: 1px solid #eee;
	}
	.comments_list li li li{
	    margin:20px 20px 10px 20px
	}
	.comment-author{ float:left }


/**** 
* Footer
*****/
.footer-widgets{
    background-color: #252525;
}

    .footer{
        padding: 30px 0px 20px 0px;
        font-size: 12px;
        text-align: center;
        background-color: #252525;

    }


/****
* RTL Styles
*****/
    <?php  if( is_rtl() ) {?> <?php } ?>
