<?php /* Template Name: Product template */ ?>
<?php get_header();
?>

<section>
<div class="container-fluid maindivss1">

<h1 class="backlink"><a onclick="history.back()" href="#"><  <?php esc_html_e(' Zurück', 'listeo_core') ?></a></h1>

<div class="container">

	<div class="row">
    <?php if(isset($_GET['add-to-cart']) && $_GET['add-to-cart']!=""){ 
			
			$cartaddedproduct = wc_get_product($_GET['add-to-cart']);
	
	?>
            <div class="col-md-12">
            <div class="woocommerce-notices-wrapper">
            <div class="woocommerce-message" role="alert" style="display:block">
            <a href="<?php echo site_url();?>/warenkorb/" tabindex="1" class="button wc-forward"><?php esc_html_e('Warenkorb anzeigen', 'listeo_core') ?></a>
            
            <?php echo $cartaddedproduct->get_name(); ?><?php esc_html_e(' wurde deinem Warenkorb hinzugefügt.', 'listeo_core') ?>
            </div>
            </div>
            </div>
            
            
            <?php
				}
			?>
    
    <?php
	if(isset($_GET['listing_id']) && $_GET['listing_id']!="")
	{
	
	$woocommerce_prd = get_post_meta($_GET['listing_id'], '_wooproduct_id', true);
				if(!empty($woocommerce_prd))
				{
				if (str_contains($woocommerce_prd, ',')) 
				{
				
				
				$product_array = explode(",", $woocommerce_prd);
			$i=1;
	foreach($product_array as $productsss)
	{
									
	// NEW CODE:
$product_newid = trim($productsss); // Remove any whitespace
$product_id = apply_filters('wpml_object_id', $product_newid, 'product', TRUE);

// Add fallback if translation doesn't exist
if (!$product_id || !wc_get_product($product_id)) {
    $product_id = $product_newid; // Use original ID as fallback
}

$product = wc_get_product($product_id);

if ($product) {
	
	//echo "<pre>";
	//print_r($product);
   //$image_url = $product->get_image_url();
   $image = wp_get_attachment_url( get_post_thumbnail_id($product_id, 'full') );
    $title = $product->get_name();
    $description = $product->get_description();
    $price = $product->get_price_html();
	//echo $product_id;
	?>
    
    
    
        <div class="col-md-6 col-xs-12 <?php if($i==1){ ?>leftdiv <?php } else{ echo "rightdiv"; } ?>">
        <div class="productss">
        	<div class="row">
            	<div class="col-md-9 col-xs-12 mainimagepro">
              <?php if($image){?>  <img src="<?php echo $image; ?>" /><?php } ?>
                </div>
                 <div class="col-md-3  col-xs-12 thumbnailimages">
                 	<ul>
                    
		<?php 
        
        $product = new WC_product($product_id);
        $attachment_ids = $product->get_gallery_image_ids();
        if($attachment_ids)
		{
			foreach( $attachment_ids as $attachment_id ) 
			{
			
				$Original_image_url = wp_get_attachment_url( $attachment_id );
				?>
				<li><img src="<?php echo $Original_image_url; ?>" /></li>
			<?php
			}
        }
        ?>
        
        

                     
                    </ul>
                </div>
            </div>
            
            <div class="row">
            <div class="col-md-12">
            	<h2 class="productheading"><?php echo $title; ?></h2>
                <div class="proddispriction ttt">
                   
                    
                    <?php echo $description; ?>

                </div>
                <div class="productpricess">
               <?php echo $price; ?> / <?php esc_html_e(' Nacht ', 'listeo_core') ?>
                </div>
            </div>
            </div>
            
            <form action="" method="get">
            <div class="row">
            <div class="col-md-6 col-lg-4 col-xs-4">
            <input type="hidden" name="add-to-cart" value="<?php echo $product_id; ?>" />
            <input type="number" placeholder="<?php esc_html_e('Anzahl', 'listeo_core') ?>" name="quantity" requried class=" quantityfield" value="" />
            <input type="hidden" placeholder="Anzahl" name="listing_id" value="<?php echo $_GET['listing_id']; ?>" />
            
            </div>
            <div class="col-md-6 col-lg-8 col-xs-8">
            <input type="submit" class="btn btn-primary buttonaddtocart" value=" <?php esc_html_e(' Jetzt kaufen ', 'listeo_core') ?>">
                 
                
               
            </div>
            </div>
            </form>
            
            
           </div> 
            
            
        </div>
        
        <?php
		}
		?>
        
        
       <!-- second box-->
       
       <?php /*?><div class="col-md-6 col-xs-12 rightdiv">
       <div class="productss">
        	<div class="row">
            	<div class="col-md-9 col-xs-12 mainimagepro">
                <img src="https://devwebgency1.de/wp-content/themes/listeo-child/imagessss/b39d9a421d9261f8df25c99cdd856cce.png" />
                </div>
                 <div class="col-md-3  col-xs-12 thumbnailimages">
                 	<ul>
                    <li><img src="https://devwebgency1.de/wp-content/themes/listeo-child/imagessss/b39d9a421d9261f8df25c99cdd856cce.png" /></li>
                     <li><img src="https://devwebgency1.de/wp-content/themes/listeo-child/imagessss/b39d9a421d9261f8df25c99cdd856cce.png" /></li>
                      <li><img src="https://devwebgency1.de/wp-content/themes/listeo-child/imagessss/b39d9a421d9261f8df25c99cdd856cce.png" /></li>
                    </ul>
                </div>
            </div>
            
            <div class="row">
            <div class="col-md-12">
            	<h2 class="productheading">Gutschein für romantische Nacht</h2>
                <div class="proddispriction">
                    <p>Eine Übernachtung für 2 Personen (+Kind) im Bubble Tent Furtwangen, mit flexiblem Datum und 3 Jahre Gültigkeit.</p>
                    <p>Nach Abschluss deiner Bestellung bekommst Du automatisch eine Bestätigungsmail. Unmittelbar nach Zahlungseingang werden dir ALLE Gutscheine aus der Vorschau im PDF-Format an deine Mail Adresse versendet. Zum Verschenken suche Dir einen Gutschein aus und schreibe die dazugehörige Bestellnummer in das Feld (Bestellnummer/Gutscheincode) unten rechts. Die Gültigkeit erhält der Gutschein ausschließlich durch die dazugehörige Bestellnummer</p>
                    <p>Um den Gutschein einzulösen, scanne den QR-Code auf dem Gutschein. Über den Link | Gutschein einlösen | kommst Du dann direkt auf unser Buchungsportal. Dort kannst Du den Gutscheincode einlösen.</p>
                   <p>Auch, wenn es schön wäre – deinen geliebten Vierbeiner darfst du leider nicht in das Zelt mit hinein nehmen. Wenn du Fragen hast, weitere Informationen benötigst oder wir etwas anderes für dich tun können, melde dich bitte einfach bei uns.</p>


                </div>
                <div class="productpricess">
                195€ / <?php esc_html_e(' Nacht ', 'listeo_core') ?>
                </div>
            </div>
            </div>
            
            
            <div class="row">
            <div class="col-md-6 col-lg-4">
            <input type="number" placeholder="Anzahl" class=" quantityfield" />
            </div>
            <div class="col-md-6 col-lg-8">
            <a type="button" class="btn btn-primary buttonaddtocart">
                 <?php esc_html_e('Jetzt kaufen ', 'listeo_core') ?>
                </a>
            </div>
            </div>
            
            
            
            
            
        </div>
        
        </div><?php */?>
        <?php
		$i++;
	} // forecah close
	
				}
				else
				{
					//echo "defualt id ".$woocommerce_prd."<br>";
					
					
					$updatedproductid= apply_filters( 'wpml_object_id', $woocommerce_prd, 'product', TRUE  );
					//echo "updated id ".$updatedproductid; 
					$product = wc_get_product($updatedproductid);

				if ($product) {
	
	//echo "<pre>";
	//print_r($product);
   //$image_url = $product->get_image_url();
   $image = wp_get_attachment_url( get_post_thumbnail_id($updatedproductid, 'full') );
    $title = $product->get_name();
    $description = $product->get_description();
    $price = $product->get_price_html();
					
					
					?>
					<div class="offset-1 col-md-10 col-xs-12 leftdiv">
        <div class="productss1">
        	<div class="row">
            	<div class="col-md-6 col-xs-12 mainimagepro">
                <div class="row">
                
                <div class="col-md-12">
                <?php if($image){?>  <img style="max-height:100%;" src="<?php echo $image; ?>" /><?php } ?>
                </div>
                
                <div class="col-md-12  col-xs-12 thumbnailimagessingle">
                 	<ul>
                    
                    	<?php
						
						
        
        $product = new WC_product($updatedproductid);
        $attachment_ids = $product->get_gallery_image_ids();
        if($attachment_ids)
		{
			foreach( $attachment_ids as $attachment_id ) 
			{
			
				$Original_image_url = wp_get_attachment_url( $attachment_id );
				?>
				<li><img src="<?php echo $Original_image_url; ?>" /></li>
			<?php
			}
        }
        ?>
                    
                     
       
                    </ul>
                </div>
                
                </div>
                 
            </div>
            
            <div class="col-md-6 col-xs-12">
            	<h2 class="productheading hedaingb"><?php echo $title; ?></h2>
                <div class="proddispriction textfontss">
                     <?php echo $description; ?>
                </div>
                
            </div>
            </div>
            
            
            <div class="row">
            <div class="col-md-6 col-xs-12">
            <div class="productpricess aignprice">
                 <?php echo $price; ?> / <?php esc_html_e(' Nacht ', 'listeo_core') ?>
                </div>
            </div>
            <div class="col-md-6 col-lg-6  col-xs-12">
            <form method="get" action="">
            	<div class="row">
                <div class="col-md-4 col-xs-4">
            
                   <input type="hidden" name="add-to-cart" value="<?php echo $woocommerce_prd; ?>" />
            <input type="number" placeholder="<?php esc_html_e('Anzahl', 'listeo_core') ?>" name="quantity" requried class=" quantityfield" value="" />
            <input type="hidden" placeholder="Anzahl" name="listing_id" value="<?php echo $_GET['listing_id']; ?>" />
            </div>
            	<div class="col-md-8 col-xs-8">
            
                 <input type="submit" class="btn btn-primary buttonaddtocart" value=" <?php esc_html_e(' Jetzt kaufen ', 'listeo_core') ?>">
            </div>
            
            
            </div>
            </form>
            </div>
            
            </div>
            
            
           </div> 
            
            
        </div>
			
				<?php
               
				}
			    }
	} 
									
									else
									{
										echo "No product found. Please Go back to listing page and try again";
									}
									
									$_GET['listing_id'];
				$language_de=apply_filters( 'wpml_object_id', $_GET['listing_id'], 'listing', FALSE, 'de' );
				$language_fr=apply_filters( 'wpml_object_id', $_GET['listing_id'], 'listing', FALSE, 'fr' );
				$language_en=apply_filters( 'wpml_object_id', $_GET['listing_id'], 'listing', FALSE, 'en' );
				
				$siteurl_de=get_site_url()."/products/?listing_id=".$language_de;
				$siteurl_en=get_site_url()."/en/products/?listing_id=".$language_en;
				$siteurl_fr=get_site_url()."/fr/produits/?listing_id=".$language_fr;
				
				?>
                <script type="text/javascript">

jQuery(document).ready(function(){
	
	jQuery(".wpml-ls-item-de > a").attr('href','<?php echo $siteurl_de; ?>');
	jQuery(".wpml-ls-item-en > a").attr('href','<?php echo $siteurl_en; ?>');
	jQuery(".wpml-ls-item-fr > a").attr('href','<?php echo $siteurl_fr; ?>');
	
	})
</script>
                <?php
	
	}
	else
	{
		echo "No product found. Please Go back to listing page and try again";
	}
		?>
    </div>

</div>

</div>
</section>
<?php /*?><div class="woocommerce_products" id="woocommerce_products">
								<div class="woo_heading"><h3 class="listing-desc-headline margin-top-30 margin-bottom-30"><?php esc_html_e('Gutscheine', 'listeo_core') ?></h3></div> 
                                <?php 
									$woocommerce_prd = get_post_meta('27713', '_wooproduct_id', true);
									if(empty($woocommerce_prd))
									{} else
									echo do_shortcode('[products columns="2" ids="'.$woocommerce_prd.'"]'); 
									?>	
                                    </div><?php */?>
<style>

.proddispriction{color: #000;
    font-family: Montserrat;
    font-size: 13px;
    font-style: normal;
    font-weight: normal;
    line-height: normal;}


/*new css*/

.container-fluid.maindivss1.singlmain {
    padding-bottom: 75px;
}

.textfontss p{ font-size:18px !important;}

.aignprice { text-align:center !important; font-weight:700 !important;}

.hedaingb{ font-weight:700 !important;}

.thumbnailimagessingle ul li {
    list-style: none;
    width: 33%;
    float: left;
    padding-top: 25px;
    padding-left: 5px;
    padding-right: 5px;
}
.thumbnailimagessingle { margin-top:10px; margin-bottom:30px;}

.leftdiv .productss1 {
    margin-right: 0px;
}
.productss1 {
    background: #fff;
    border-radius: 40px;
    padding-top: 20px;
    padding-left: 25px !important;
    padding-right: 25px !important;
    padding-bottom: 20px;
}

/*new css*/

.backlink a {
    font-family: Montserrat;
    color: rgba(100, 120, 103, 0.80);
}
.backlink a:hover {
    font-family: Montserrat;
    color: rgba(100, 120, 103, 0.90);
}
.leftdiv .productss{ margin-right:25px;}
.rightdiv .productss{ margin-left:25px;

}
.maindivss1{background: url("<?php echo get_stylesheet_directory_uri(); ?>/imagessss/pexels-adam-lukac-1750347 1.png"), lightgray 50% / cover no-repeat;
    background-size: 100% 100%;}

.backlink{
	font-family: Montserrat;
	color: rgba(100, 120, 103, 0.80);
font-family: Inter;
font-size: 40px;
font-style: normal;
font-weight: 700;
line-height: normal;}

.buttonaddtocart {
    font-family: Montserrat !important;
    background: #74907C !important;
    color: #FFF !important;
    text-align: center;
    font-size: 18px !important;
    font-style: normal;
    padding: 10px 18px !important;
    border-radius: 37px !important;
    font-weight: 400;
    line-height: normal;
    width: 100% !important;
}

.quantityfield
{
border-radius: 25px !important;
    background: #e1e1e1 !important;
width: 97px;
color: #000 !important;
  font-family: Montserrat !important;
font-size: 14px;
font-style: normal;
font-weight: 400;
line-height: normal;
}

h1.backlink {
    margin-bottom: 40px;
    margin-top: 40px;
}

.mainimagepro img {
    width: 100%;
	border-radius:25px;
	max-height:384px;
}

.thumbnailimages ul li {
    list-style: none;
    margin-bottom: 10px;
}
.thumbnailimages li img {
    width: 100%;
	border-radius:8px;
	max-height:121px;
}
.mainimagepro {
    padding-right: 30px !important;
}
.productss {
    background: #fff;
    border-radius: 40px;
    padding-top: 20px;
    padding-left: 25px !important;
    padding-right: 60px !important;
    padding-bottom: 20px;
}
.thumbnailimages{ padding-left:5px !important;}
.thumbnailimages ul{ padding-left:0px;}

h2.productheading{
	color: #000;
font-family: Montserrat;
font-size: 22px;
font-style: normal;
font-weight: 400;
line-height: normal;
 margin-top: 15px;
 margin-bottom: 15px;}
	
	.proddispriction p{color: #000;
font-family: Montserrat;
font-size: 13px;
font-style: normal;
font-weight: normal;
line-height: normal;}

.productpricess{color: #000;
font-family: Montserrat;
font-size: 27px;
font-style: normal;
font-weight: 400;
line-height: normal; 
text-align:right;
margin-top: 23px;
    margin-bottom: 20px;}
	.container-fluid.maindivss1 {
    padding-bottom: 60px;
}
	
	@media(max-width:991px) and (min-width:768px)
	{
		.rightdiv .productss {
  margin-left: 12px;
}
.leftdiv .productss {
  margin-right: 12px;
}

.productss {
  background: #fff;
  border-radius: 40px;
  padding-top: 20px;
  padding-left: 15px !important;
  padding-right: 25px !important;
  padding-bottom: 20px;
}

.mainimagepro {
  padding-right: 28px !important;
}

.buttonaddtocart {
  font-family: Montserrat !important;
  background: #74907C !important;
  color: #FFF !important;
  text-align: center;
  font-size: 15px !important;
  font-style: normal;
  padding: 10px 7px !important;
  border-radius: 37px !important;
}
	}
	
	@media (max-width:767px)
	{
		.leftdiv .productss {
  margin-right: 0px;
}

.leftdiv .productss {
  margin-right: 0px;
}
.mainimagepro {
    padding-right: 15px !important;
}
.productss {
  background: #fff;
  border-radius: 40px;
  padding-top: 20px;
  padding-left: 15px !important;
  padding-right: 15px !important;
  padding-bottom: 20px;
}
.thumbnailimages ul li {
  width: 33%;
  float: left;
  padding-left: 10px;
  padding-right: 10px;
  padding-top: 15px;
}
.rightdiv .productss {
  margin-left: 0px;
  margin-top: 20px;
  margin-bottom:20px;
}

.backlink {
  font-family: Montserrat;
  color: rgba(100, 120, 103, 0.80);
  font-family: Inter;
  font-size: 28px;
  font-style: normal;
  font-weight: 700;
  line-height: normal;
}
	}
	
	input[type=number]::-webkit-inner-spin-button, 
input[type=number]::-webkit-outer-spin-button {  

   opacity: 1;

}

.woocommerce-Price-currencySymbol, .woocommerce-Price-amount {
    vertical-align: baseline !important;
}

.woocommerce-message a.button.wc-forward {
    float: right;
    margin-top: -6px;
    margin-right: -30px;
    right: 0;
    padding: 6px 10px;
    border-radius: 4px;
    background: #222;
    color: #fff;
}

.thumbnailimages li img:hover{ cursor:pointer;}
</style>

<script type="text/javascript">

jQuery(document).ready(function(){
	
	jQuery(".thumbnailimages li img").click(function(){
		
		
		
		var latestsrc= jQuery(this).attr('src');
		//alert(latestsrc);
		jQuery(this).parent().parent().parent().prev().find('img').attr("src",latestsrc);
		
		})
		
		jQuery(".thumbnailimagessingle li img").click(function(){
		
		
		
		var latestsrc= jQuery(this).attr('src');
		//alert(latestsrc);
		jQuery(this).parent().parent().parent().prev().find('img').attr("src",latestsrc);
		
		})
	
	})
</script>


<?php get_footer();?>