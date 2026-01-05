<?php /* Template Name: single Product template */ ?>
<?php get_header();?>

<section>
<div class="container-fluid maindivss1 singlmain">

<h1 class="backlink"><a onclick="history.back()" href="#">< Gutschein Kaufen</a></h1>

<div class="container">

	<div class="row">
        <div class="col-md-12 col-xs-12 leftdiv">
        <div class="productss1">
        	<div class="row">
            	<div class="col-md-6 col-xs-12 mainimagepro">
                <div class="row">
                
                <div class="col-md-12">
                <img src="https://devwebgency1.de/wp-content/themes/listeo-child/imagessss/Bildschirmfoto 2023-07-08 um 10.17 2.png" />
                </div>
                
                <div class="col-md-12  col-xs-12 thumbnailimagessingle">
                 	<ul>
                    <li><img src="https://devwebgency1.de/wp-content/themes/listeo-child/imagessss/Bildschirmfoto 2023-07-08 um 10.17 4.png" /></li>
                     <li><img src="https://devwebgency1.de/wp-content/themes/listeo-child/imagessss/Bildschirmfoto 2023-07-08 um 10.17 4.png" /></li>
                      <li><img src="https://devwebgency1.de/wp-content/themes/listeo-child/imagessss/Bildschirmfoto 2023-07-08 um 10.17 4.png" /></li>
       
                    </ul>
                </div>
                
                </div>
                 
            </div>
            
            <div class="col-md-6 col-xs-12">
            	<h2 class="productheading hedaingb">Gutschein für magische Nacht</h2>
                <div class="proddispriction textfontss">
                    <p>Eine Übernachtung für 2 Personen (+Kind) im Bubble Tent Furtwangen, mit flexiblem Datum und 3 Jahre Gültigkeit.</p>
                    <p>Nach Abschluss deiner Bestellung bekommst Du automatisch eine Bestätigungsmail. Unmittelbar nach Zahlungseingang werden dir ALLE Gutscheine aus der Vorschau im PDF-Format an deine Mail Adresse versendet. Zum Verschenken suche Dir einen Gutschein aus und schreibe die dazugehörige Bestellnummer in das Feld (Bestellnummer/Gutscheincode) unten rechts. Die Gültigkeit erhält der Gutschein ausschließlich durch die dazugehörige Bestellnummer</p>
                    <p>Um den Gutschein einzulösen, scanne den QR-Code auf dem Gutschein. Über den Link | Gutschein einlösen | kommst Du dann direkt auf unser Buchungsportal. Dort kannst Du den Gutscheincode einlösen.</p>
                    <p>Auch, wenn es schön wäre – deinen geliebten Vierbeiner darfst du leider nicht in das Zelt mit hinein nehmen. Wenn du Fragen hast, weitere Informationen benötigst oder wir etwas anderes für dich tun können, melde dich bitte einfach bei uns.</p>

                </div>
                
            </div>
            </div>
            
            
            <div class="row">
            <div class="col-md-6 col-xs-12">
            <div class="productpricess aignprice">
                185€ / Nacht
                </div>
            </div>
            <div class="col-md-6 col-lg-6  col-xs-12">
            	<div class="row">
                <div class="col-md-4">
            <input type="number" placeholder="Anzahl" class=" quantityfield" />
            </div>
            	<div class="col-md-8">
            <a type="button" class="btn btn-primary buttonaddtocart">
                 Jetzt kaufen
                </a>
            </div>
            
            </div>
            </div>
            
            </div>
            
            
           </div> 
            
            
        </div>
        
        
       <!-- second box-->
       
       
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
.rightdiv .productss{ margin-left:37px;

}
.maindivss1{background: url("https://devwebgency1.de/wp-content/themes/listeo-child/imagessss/pexels-adam-lukac-1750347 1.png"), lightgray 50% / cover no-repeat;
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
    margin-bottom: 60px;
    margin-top: 40px;
}

.mainimagepro img {
    width: 100%;
	border-radius:25px;
}

.thumbnailimages ul li {
    list-style: none;
    margin-bottom: 10px;
}
.thumbnailimages li img {
    width: 100%;
	border-radius:8px;
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
font-size: 25px;
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
font-size: 30px;
font-style: normal;
font-weight: 400;
line-height: normal; 
text-align:right;
margin-top: 23px;
    margin-bottom: 20px;}
	.container-fluid.maindivss1 {
    padding-bottom: 30px;
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

</style>


<?php get_footer();?>