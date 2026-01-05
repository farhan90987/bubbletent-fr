<?php

/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package listeo
 */

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
	<meta name="facebook-domain-verification" content="895qioskh41y5fccv2ui6c5uamaj1r" />
	 <!-- Woocommerce code snippet start  -->

	 <script async src="https://sos-de-fra-1.exo.io/cdn-adv/adv.js" data-system="wo" data-cutrid="nbco03Bly1KEX9TuPKPl3EqrF2fJhvpLLLLu" data-acccurrency="EUR" data-acctimezone="Europe/Berlin"   id="adv-script"></script>

	 <!-- Woocommerce code snippet end  -->
    <!-- Hotjar Tracking Code for  -->
<script>
    (function(h,o,t,j,a,r){
        h.hj=h.hj||function(){(h.hj.q=h.hj.q||[]).push(arguments)};
        h._hjSettings={hjid:3172787,hjsv:6};
        a=o.getElementsByTagName('head')[0];
        r=o.createElement('script');r.async=1;
        r.src=t+h._hjSettings.hjid+j+h._hjSettings.hjsv;
        a.appendChild(r);
    })(window,document,'https://static.hotjar.com/c/hotjar-','.js?sv=');
</script>

    <script>
        !function (w, d, t) {
          w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];ttq.methods=["page","track","identify","instances","debug","on","off","once","ready","alias","group","enableCookie","disableCookie"],ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);ttq.instance=function(t){for(var e=ttq._i[t]||[],n=0;n<ttq.methods.length;n++
)ttq.setAndDefer(e,ttq.methods[n]);return e},ttq.load=function(e,n){var i="https://analytics.tiktok.com/i18n/pixel/events.js";ttq._i=ttq._i||{},ttq._i[e]=[],ttq._i[e]._u=i,ttq._t=ttq._t||{},ttq._t[e]=+new Date,ttq._o=ttq._o||{},ttq._o[e]=n||{};n=document.createElement("script");n.type="text/javascript",n.async=!0,n.src=i+"?sdkid="+e+"&lib="+t;e=document.getElementsByTagName("script")[0];e.parentNode.insertBefore(n,e)};
        
          ttq.load('CJ1OPDJC77U3DHQFKL7G');
          ttq.page();
        }(window, document, 'ttq');
    </script>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="http://gmpg.org/xfn/11">
    <?php 
	global $wp;
	 $productspage= home_url( $wp->request );
	//echo site_url().'/en/products'; exit;
	if(get_post_type()=='listing' || $productspage==site_url().'/products' || $productspage==site_url().'/en/products/'  || $productspage==site_url().'/fr/produits/')
	{ ?>
 <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css?ver=1">
   <link rel="stylesheet" href="<?php echo get_stylesheet_directory_uri(); ?>/listeo-core/css/flexslider.css?ver=1">
      
	<link rel="stylesheet" href="<?php echo get_stylesheet_directory_uri(); ?>/listeo-core/css/demo.css?ver=1">
    <link rel="stylesheet" href="<?php echo get_stylesheet_directory_uri(); ?>/custom-style.css?version=7">
	
	<?php if(get_post_type()=='listing'){ ?>
	
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightgallery-js/1.4.0/css/lightgallery.min.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">
	<?php } ?>
	
	
	
<style>

.smoobu-price-display-container {
    padding-left: 10px;
    padding-right: 10px;
}

a.button.st-cashier.disabled{ background:#D9D9D9 !important; color:#000000 !important;}
a.button.st-cashier{ background:#54775E !important;  color:#ffffff !important;}

input[readonly].smoobu-calendar:not(:placeholder-shown) {
       background: #D9D9D9 !important;
    color: #000 !important;
}

#smoobu-check-availability {
    display: flex;
    justify-content: space-between;
    column-gap: 0px;
    padding: 0 33px 0 34px;
    width: 94%;
    margin-left: 12px;
}

p.smobuutext.secondparasobu {
    padding-bottom: 0px !important;
    margin-bottom: 0px !important;
    padding-left: 38px !important;
    padding-right: 20px !important;
}

#smoobu-cost-calculator-container .smoobu-calendar-button-container a.button.st-cashier
{ width:92% !important;}

input.smoobu-calendar{
	background:#54775E !important;
	color:#fff !important
}

input.smoobu-calendar:valid {
    background: #D9D9D9 !important
}

input.smoobu-calendar::placeholder
{
	color:#fff;
}

input.smoobu-calendar:hover::placeholder {
	color:#000;
}



/*input.smoobu-calendar:focus{
	background:#D9D9D9 !important;
	color:#000 !important
}*/
input.smoobu-calendar:hover{
	background:#D9D9D9 !important;
	color:#000 !important
}

.smoobu-price-display-container {
    flex-direction: column-reverse !important;
     padding-right: 0px !important;
	 text-align: center;
	 line-height:32px;
}

.smoobu-price-display-container {
    font-family: Montserrat !important;
    font-size: 34px !important;
    font-weight: 500 !important;
	color: #647867 !important;
}
.row.newsectionsidebara {
    margin-top: 40px;
    border-radius: 15px;
    padding-bottom: 40px;
	margin-bottom:40px;
}
.sidbuttonhavestyle button{ padding-left:25px; padding-right:25px;}

a.sidbuttonhavestyle img
{
	    position: absolute;
    right: 1px;
    top: -30px;
	width:44px;
    rotate: 45deg;
}
a.sidbuttonhavestyle1 img
{
	    position: absolute;
    right: 1px;
	width:44px;
    top: -20px;
    rotate: 45deg;
}

p.locationmarkanddata {
    text-align: center;
    font-size: 18px;
}
p.locationmarkanddata img {
    width: 32px;
}
p.smobuutext {
    font-size: 14px !important;
	color:#54775E !important
}
p.smobuutext img {
    width: 30px;
    margin-right: 5px;
}
.secondparasobu{ margin-top:16px;}
.smoobu-calendar-estimate { line-height:0px !important; border-bottom:none !important; margin:1.5vw 0 !important;}

p.smobuutext.secondparasobu {
    padding-bottom: 0px !important;
    margin-bottom: 0px !important;
}



@media(max-width:767px)
{
.smoobu-calendar-estimate {
    margin-bottom: 25px !important;
    margin-top: 25px !important;
}
.firstbuttonrightsec
{
	margin-top:20px;
}

.row.newsectionsidebara {
    margin-top: 40px;
    border-radius: 15px;
    padding-bottom: 40px;
    margin-left: 0px;
	    margin-bottom: 40px;
}
}

@media(min-width:1400px)
{
#smoobu-cost-calculator-container .smoobu-calendar-button-container a.button.st-cashier {
    width: 88% !important;
}
#smoobu-check-availability {
       width: 90%;
    margin-left: 20px;
}
}


@media(min-width:1024px) and (max-width:1239px)
{
#smoobu-check-availability {
    column-gap: 1vw;
    padding: 0 15px 0 10px !important;
}

.sidebarnewmodeule p {
    padding-left: 12px !important;
    padding-right: 15px !important;
    text-align: center;
}

p.smobuutext.secondparasobu {
    padding-bottom: 0px !important;
    margin-bottom: 0px !important;
    padding-left: 21px !important;
    padding-right: 15px !important;
    text-align: left;
}

#smoobu-cost-calculator-container .smoobu-calendar-button-container {
  
    padding-left: 10px !important;
    padding-right: 15px !important;
}
}





/*new css ends*/

.mainfaqbody{
	color: #606465 !important;
	}

.col-md-6.col-sm-6.col-xs-12.mainimagetop.desktopvi img {
    max-height: 450px;
	width:100%
}

.row.obileimage img {
    width: 100%;
}
.col-md-6.col-sm-6.col-xs-12.otherimagesmobile.desktopvi .topimage img {
    max-height: 233px;
}
.row.obileimage img {
    max-height: 201px;
}

.topimage img {
    min-height: 233px;
}

.row.obileimage div:first-child {
    padding-right: 7.5px;
}
.row.obileimage div:last-child {
    padding-left: 7.5px;
}
.sidebarnewmodeule p {
    text-decoration: none !important;
}
h3.heading33, h2.subheading {
    text-align: left;
}
.description-long.toplongtext p, .longtextmain p, p.middleshorttext, .middlelongtext p,
p.whyshorttext, .whylongtext p {
    color: #606465;
}

.col-md-12 .buttonpop {
    background: #54775E !important;
	font-size: 18px;
}
ul.iconspoplist li {
    display: flex;
}

.textpopupparagraph {
    color: #606465 !important;
}
ul.popuiconsss li {
    display: flex;
}
button.btn.btn-primary.btntopmain:hover {
    color: #000 !important;
}
.col-md-6.col-sm-6.col-xs-12.mainimagetop.desktopvi {
    padding-right: 0px;
}
.mainimagetop img {
    border-radius: 20px;
}
.otherimagesmobile img {
    border-radius: 20px;
}
	
	.iconstopaaa img, .iconpopssss {
    width: 32px;
    height: 32px;
}
.topiconcenter img {
    width: 32px;
    height: 32px;
}
ul.popuiconsss img {
    width: 32px;
    height: 32px;
}
	
	.icontops.topiconcenter > div {
    display: inline-block;
    /* text-align: center; */
}

.icontops.topiconcenter {
	text-align:center;
    /* text-align: center; */
}
.iconstopaaa{ margin:0 auto;}

.leftalignnn {
    float: left;
	padding-left:5px;
}

.rightalignnn {
    float: right;
	padding-right:29px;
}

	

	.mobilepop {
    display: block;
}
	
	.pricemobiless{color: #000;

font-family: Montserrat;
font-size: 16px;
font-style: normal;
font-weight: 700;
line-height: normal;}
	
	.mobilepop{display:none;}
	
	.btntopmain{
	    color: #000;
    text-align: center;
    font-family: Montserrat;
    font-size: 15px;
    font-style: normal;
    background: rgba(255, 255, 255, 0.75);
    font-weight: 600;
    line-height: normal;
    border-color: #fff;
    position: absolute;
    bottom: 16px;
    right: 28px;
    border-radius: 12px;
    padding: 7px 14px;
	}
	.btntopmain:hover{ background: rgba(255, 255, 255, 0.9) !important;}
	
	
	
	
	
	.pricelisting h3.listing-desc-headline.margin-top-70.margin-bottom-30 {
    display: none;
}

.pricelisting ul.pricing-menu-no-title li .pricing-menu-details {
    display: none;
}

.pricelisting ul.pricing-menu-no-title li:last-child {
    display: none;
}
	
	
.pricelisting	.pricing-list-container ul li {
    display: inline-block;
    align-items: center;
    text-align: center;
    width: 100%;
}



.pricelisting .pricing-list-container span {
    position: relative;
    right: auto;
    top: auto;
    /* transform: translateY(-50%); */
    display: inline-block;
}

.pricelisting .pricing-list-container .pricing-menu-no-title:first-child{ border:none;}

.pernightss{ display:none;}
.pricelisting .pernightss{ display:block;}

.pricelisting span{color: #000;
font-family: Montserrat;
font-size: 22px;
font-style: normal;
font-weight: 700;
line-height: normal;}
	
.newside div#widget_booking_listings-2 h3.widget-title.margin-bottom-35 {
    display: none;
}	

.newside .col-lg-12.coupon-widget-wrapper {
    display: none !important;
}

.newside div#widget_contact_widget_listeo-2 {
    display: none;
}

.newside div#widget_listing_owner-2, .newside div#widget_buttons_listings-2{ display:none;}

.col-lg-4.newside.col-md-4.sidebarclick.listeo-single-listing-sidebar.margin-top-75.sticky{
    margin-top: 0px !important;

}
.nwesidedetail{
	border-radius: 30px;
	background: #F5F5F5;
	padding-top: 50px;
	position:absolute;
	top:10px;
	width:100%;

}
.fixedtops{ position:fixed;}
.col-lg-4.newside.col-md-4.sidebarclick.listeo-single-listing-sidebar.margin-top-75.sticky {
    position: relative;
}

.nwesidedetail .margin-bottom-35 {
    margin-bottom: 0px !important;
}

.nwesidedetail .pricing-list-container ul li {
    padding: 0px 10px !important;
    position: relative;
}
.nwesidedetail div#widget_booking_listings-2 {
    padding-top: 10px;
}
.sidebarnewmodeule p {
    padding-left: 34px;
    padding-right: 33px;
	text-align:center;
}

.sidebarnewmodeule p {color: #000;
font-family: Montserrat;
font-size: 20px;
font-style: normal;
font-weight: 500;
line-height: normal;}

.sidebarnewmodeule a {
    padding-top: 12px !important;
    padding-bottom: 12px !important;
}

.sidebarnewmodeule p {
    padding-bottom: 12px !important;
}

.sidebarnewmodeule {
    padding-bottom: 7px;
}

.nwesidedetail div#widget_booking_listings-2 {
    background: transparent;
}

.listeo-store-browse-more {
    display: none;
}
.simple-slick-carousel.listeo-products-slider.dots-nav.slick-initialized.slick-slider {
    display: none;
}
.modal-content{ border-radius:20px !important;}


.centericonss{ text-align:center}

.row.topiconcenter .col-md-4 {
    padding-bottom: 16px;
}

.nwesidedetail .pricelisting {
    /*display: none;*/
}

.nwesidedetail div#widget_booking_listings-2 {
    display: none;
}

.verified-badge.with-tip {
    display: none;
}

.eachreview .comment-content{   background: #a9bbaf;
    border-radius: 15px;
    padding-top: 10px;
    width: 100%;
    padding-bottom: 10px;
    min-height: 238px;}
	
.row.topiconcenter .centericonss{display: flex;
    align-items: center;
    text-align: center;
    justify-content: center;}
	
	.row.topiconcenter .col-md-4{display: flex;
    align-items: center;
 }
 
 .icontext {

    padding-left: 5px;
 }
 
 .morecontent {
  display: none;
} 

.readless{ display:none;}

.mainfaqheader button{ display:flex; text-align:left;}

.col-md-4.centercolumc h4 {
    text-align: center;
}

.col-md-4.centercolumc {
    text-align: center;
}

.col-md-4.centercolumc ul.middlelist {
    text-align: left;
    margin-left: 40px;
}

div#listing-location h3.listing-desc-headline.margin-top-60.margin-bottom-30 {
    display: none;
}

ul.popuiconsss li {
    margin-bottom: 9px;
    margin-top: 9px;
}

.scrollbarnew h5.modal-title {
    text-align: center;
    width: 100%;
}


element.style {
}
#myModal8 .seacjbtn {
    top: 10px !important;
}

.description-long p {
    line-height: 25px;
}

div#myModal5 .modal-body {
    padding-right: 70px;
    text-align: left;
}



button.btn.btn-primary.btntopmain:active ,button.btn.btn-primary.btntopmain:focus {
    background: rgba(255, 255, 255, 0.75) !important;
	border:none !important;
	color:#000 !important;
	box-shadow: none !important;
}


element.style {
}
#myModal8 .seacjbtn {
    top: 10px !important;
}
#myModal8 .seacjbtn {
    /* background: #d9d9d9; */
    /* border: 1px #d9d9d9; */
    /* border-radius: 0px 20px 20px 0px; */
    /* padding: 5px; */
    position: absolute;
    right: 24px;
    top: 5px;
}
#myModal8 .seacjbtn {
    background: transparent;
    border: none;
}
button.btn.seacjbtn.btn-primary:focus, button.btn.seacjbtn.btn-primary:active {
    border: none !important;
    box-shadow: none !important;
}


@media(max-width:991px) and (min-width:768px)
{
	

div#myModal5 .modal-body {
    padding-right: 10px;
}
	.subheading {
  color: #647867;
  text-align: center;
  font-family: Montserrat;
  font-size: 25px;
	}
	
	h3.heading33 {
  padding-bottom: 20px;
  text-align: center;
  color: #647867;
  text-align: center;
  font-family: Montserrat;
  font-size: 25px;
	}
.description-long p {
  color: #54775E;
  font-family: Montserrat;
  font-size: 15px;
  font-style: normal;
}

.icontext {
  color: #54775E;
  font-family: Montserrat;
  font-size: 12px;
}

.description-long .readmore {
  color: #54775E;
  font-family: Montserrat;
  font-size: 15px;
}

.middlesection h4 {
  color: #54775E;
  font-family: Montserrat;
  font-size: 16px;
}

ul.middlelist li {
  color: #54775E;
  font-family: Montserrat;
  font-size: 15px;
  margin-bottom:6px;
}


.readmorebtn {
  color: #54775E;
  font-family: Montserrat;
  font-size: 15px;
}

.mainfaqheader button {
  color: #54775E;
  font-family: Montserrat;
  font-size: 15px;
}

div#slider ul li img {
    height: 100%;
}

}


@media(max-width:767px)
{
	
	.mainhr {
  margin-bottom: 5px !important;
  margin-top: 5px;
}

.mainhr1 {
  margin-top: 24px;
  margin-bottom: 24px;
}
	.mobilefaqalign{ text-align:center !important}
	.mobileviewcenter{ text-align:center !important;} 
	
	.btn.btn-primary.mobilepop.buttonpop {
  float: none;
  margin: 0 auto;
  border-radius: 10px;
  width: 230px;
  font-size: 15px;
  padding: 13px;
  display:block;
}
	
	.middlesections .col-xs-6 
	{
		justify-content: start !important;
		padding-left: 30px;
	}
	
	.row.icontops.topiconcenter img {
  width: 25px;
}
	.desktopviewww{ display:none !important;} 
	.mobileview{ display:block !important; }
	.mobileslider{ display:block !important;}
	.desktopvi{ display:none;} 
	
	div#slider ul li img {
    height: 100%;
}
	
	.otherimagesmobile {
    display: none;
}
	
	div#myModal5 .modal-body {
    padding-right: 10px;
}
	.subheading {
  color: #647867;
  text-align: center;
  font-family: Montserrat;
  font-size: 22px;
	}
	
	h3.heading33 {
  padding-bottom: 20px;
  text-align: center;
  color: #647867;
  text-align: center;
  font-family: Montserrat;
  font-size: 22px;
	}
.description-long p {
  color: #54775E;
  font-family: Montserrat;
  font-size: 15px;
  font-style: normal;
}

.icontext {
  color: #54775E;
  font-family: Montserrat;
  font-size: 13px;
}

.description-long .readmore {
  color: #54775E;
  font-family: Montserrat;
  font-size: 15px;
}

.middlesection h4 {
  color: #54775E;
  font-family: Montserrat;
  font-size: 16px;
}

ul.middlelist li {
  color: #54775E;
  font-family: Montserrat;
  font-size: 15px;
  margin-bottom:6px;
}


.readmorebtn {
  color: #54775E;
  font-family: Montserrat;
  font-size: 15px;
}

.mainfaqheader button {
  color: #54775E;
  font-family: Montserrat;
  font-size: 15px;
}

.col-md-4.centercolumc {
  text-align: left;
}


.col-md-4.centercolumc h4 {
  text-align: left;
}

.col-md-4.centercolumc ul.middlelist {
  text-align: left;
  margin-left: 0px;
}

.col-md-6.col-sm-6.col-xs-12.mainimagetop {
  margin-bottom: 30px;
}

.obileimage img { margin-bottom:30px; width:100%;}

.row.topiconcenter .col-md-4 {
  display: flex;
  justify-content: center;
}

.row.middlesection > div {
  margin-bottom: 18px;
}

.nwesidedetail {
  border-radius: 30px;
  background: #F5F5F5;
  padding-top: 50px;
  position: relative;
  top: 10px;
  width: 100%;
  margin-bottom: 50px;
}

.mobileshowsss{ display:block !important}
.desktopreview{ display:none !important;}

.mobilefaq > div:nth-child(3) {
  display: none;
}
.mobilefaq > div:nth-child(4) {
  display: none;
}
.mobilefaq > div:nth-child(5) {
  display: none;
}

.reviewdesktop{ display:none;}

.col-md-12.col-xs-12.mobileslider {
    margin-top: 25px;
}
div#trustbadge-container-98e3dadd90eb493088abdc5597a70810 {
    display: none;
}
}
</style>

<?php
	}
 ?>
	<?php wp_head(); ?>


</head>

<body <?php if (get_option('listeo_dark_mode')) {
			echo 'id="dark-mode"';
		} ?> <?php body_class(); ?>>

<!--  Clickcease.com tracking-->
<script type='text/javascript'>var script = document.createElement('script');
script.async = true; script.type = 'text/javascript';
var target = 'https://www.clickcease.com/monitor/stat.js';
script.src = target;var elem = document.head;elem.appendChild(script);
</script>
<noscript>
<a href='https://www.clickcease.com' rel='nofollow'><img src='https://monitor.clickcease.com/stats/stats.aspx' alt='ClickCease'/></a>
</noscript>
<!--  Clickcease.com tracking-->
		
	<?php if (!function_exists('elementor_theme_do_location') || !elementor_theme_do_location('header')) { ?>
		<?php wp_body_open(); ?>
		<!-- Wrapper -->


		<!-- Mobile Navigation -->
		<nav class="mobile-navigation-wrapper">
			<div class="mobile-nav-header">
				<div class="menu-logo">
					<?php
					$logo_transparent = get_option('pp_dashboard_logo_upload', ''); ?>
					<a href="<?php echo esc_url(home_url('/')); ?>" class="" title="<?php echo esc_attr(get_bloginfo('name', 'display')); ?>" rel="home"><img id="listeo_logo" src="<?php echo esc_url($logo_transparent); ?>" data-rjs="<?php echo esc_url($logo_transparent); ?>" alt="<?php esc_attr(bloginfo('name')); ?>" /></a>
					
				</div>
				<a href="#" class="menu-icon-toggle"></a>
			</div>

			<div class="mobile-navigation-list">
				<?php
				if (has_nav_menu('mobile')) {
					$menu_location = 'mobile';
				} else {
					$menu_location = 'primary';
				}
				wp_nav_menu(array(
					'theme_location' => $menu_location,
					'menu_id' => 'mobile-nav',
					'container' => false,
				));  ?>
			</div>

			<div class="mobile-nav-widgets">
				<?php dynamic_sidebar('mobilemenu'); ?>
			</div>
		</nav>
		<!-- Mobile Navigation / End-->



		<div id="wrapper">

			<?php

			do_action('listeo_after_wrapper');
			$header_layout = get_option('listeo_header_layout');

			$sticky = get_option('listeo_sticky_header');

			if (is_singular()) {

				$header_layout_single = get_post_meta($post->ID, 'listeo_header_layout', TRUE);

				switch ($header_layout_single) {
					case 'on':
					case 'enable':
						$header_layout = 'fullwidth';
						break;

					case 'disable':
						$header_layout = false;
						break;

					case 'use_global':
						$header_layout = get_option('listeo_header_layout');
						break;

					default:
						$header_layout = get_option('listeo_header_layout');
						break;
				}


				$sticky_single = get_post_meta($post->ID, 'listeo_sticky_header', TRUE);
				switch ($sticky_single) {
					case 'on':
					case 'enable':
						$sticky = true;
						break;

					case 'disable':
						$sticky = false;
						break;

					case 'use_global':
						$sticky = get_option('listeo_sticky_header');
						break;

					default:
						$sticky = get_option('listeo_sticky_header');
						break;
				}
				if (is_singular('listing')) {
					$sticky = false;
				}
			}


			$header_layout = apply_filters('listeo_header_layout_filter', $header_layout);
			$sticky = apply_filters('listeo_sticky_header_filter', $sticky);

			?>
			<!-- Header Container
================================================== -->
			<header id="header-container" class="<?php echo esc_attr(($sticky == true || $sticky == 1) ? "sticky-header" : ''); ?> <?php echo esc_attr($header_layout); ?>">

				<!-- Header -->
				<div id="header">
					<div class="container">
						<?php
						$logo = get_option('pp_logo_upload', '');
						$logo_transparent = get_option('pp_dashboard_logo_upload', '');
						$logo_sticky = get_option('pp_sticky_logo_upload', '');
						?>
						<!-- Left Side Content -->
						<div class="left-side">
							<div id="logo" data-logo-transparent="<?php echo esc_attr($logo_transparent); ?>" data-logo="<?php echo esc_attr($logo); ?>" data-logo-sticky="<?php echo esc_attr($logo_sticky); ?>">
								<?php
								$logo = get_option('pp_logo_upload', '');
								if ((is_page_template('template-home-search.php') || is_page_template('template-home-search-splash.php'))  && (get_option('listeo_home_transparent_header') == 'enable')) {
									$logo = get_option('pp_dashboard_logo_upload', '');
								}
								if (isset($post) && get_post_meta($post->ID, 'listeo_transparent_header', TRUE)) {
									$logo = get_option('pp_dashboard_logo_upload', '');
								}
								$logo_retina = get_option('pp_retina_logo_upload', '');
								if ($logo) {
									if (is_front_page()) { ?>
										<a href="<?php echo esc_url(home_url('/')); ?>" title="<?php echo esc_attr(get_bloginfo('name', 'display')); ?>" rel="home"><img id="listeo_logo" src="<?php echo esc_url($logo); ?>" data-rjs="<?php echo esc_url($logo_retina); ?>" alt="<?php esc_attr(bloginfo('name')); ?>" /></a>
									<?php } else { ?>
										<a href="<?php echo esc_url(home_url('/')); ?>" title="<?php echo esc_attr(get_bloginfo('name', 'display')); ?>" rel="home"><img id="listeo_logo" src="<?php echo esc_url($logo); ?>" data-rjs="<?php echo esc_url($logo_retina); ?>" alt="<?php esc_attr(bloginfo('name')); ?>" /></a>
									<?php }
								} else {
									if (is_front_page()) { ?>
										<h1><a href="<?php echo esc_url(home_url('/')); ?>" title="<?php echo esc_attr(get_bloginfo('name', 'display')); ?>" rel="home"><?php bloginfo('name'); ?></a></h1>
									<?php } else { ?>
										<h2><a href="<?php echo esc_url(home_url('/')); ?>" title="<?php echo esc_attr(get_bloginfo('name', 'display')); ?>" rel="home"><?php bloginfo('name'); ?></a></h2>
								<?php }
								}
								?>
							</div>

							<!-- Main Navigation -->
							<nav id="navigation" class="style-1">
								<?php wp_nav_menu(array(
									'theme_location' => 'primary',
									'menu_id' => 'responsive',
									'container' => false,
									'fallback_cb' => 'listeo_fallback_menu',
									'walker' => new listeo_megamenu_walker
								));  ?>

							</nav>
							<div class="clearfix"></div>
							<!-- Main Navigation / End -->

						</div>

						<!-- Left Side Content / End -->
						<?php

						$my_account_display = get_option('listeo_my_account_display', true);
						$submit_display = get_option('listeo_submit_display', true);

						if ($my_account_display != false || $submit_display != false) :	?>
							<!-- Right Side Content / End -->

							<div class="right-side">
								
								<!-- Mobile Navigation -->
								<div class="mmenu-trigger <?php if (wp_nav_menu(array('theme_location' => 'primary', 'echo' => false)) == false) { ?> hidden-burger <?php } ?>">
									<button class="hamburger hamburger--collapse" type="button">
										<span class="hamburger-box">
											<span class="hamburger-inner"></span>
										</span>
									</button>
								</div>
								
								<div class="header-widget">
									<?php get_template_part('inc/mini-cart'); ?>
									<!--end navbar-right -->
									<?php
									if (class_exists('Listeo_Core_Template_Loader')) :
										$template_loader = new Listeo_Core_Template_Loader;
										$template_loader->get_template_part('account/logged_section');
									endif;
									?>
								</div>
							</div>

							<!-- Right Side Content / End -->
						<?php endif; ?>

					</div>
				</div>
				<!-- Header / End -->

			</header>


			<!-- Header Container / End -->
		<?php } ?>
			<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=AW-16640610151">
</script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'AW-16640610151');
</script>