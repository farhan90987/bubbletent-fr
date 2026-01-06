=== J Cart Upsell and Cross-sell for WooCommerce ===

Contributors: jakjako, ghlab
Donate link: https://www.buymeacoffee.com/Jakjako
Requires at least: 5.0
Tested up to: 6.3.1
Requires PHP: 7.1
Stable tag: 3.4.6
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html


Boost your WooCommerce with targeted upsells, cross-sells, gifts and a cool modal cart.


== Description ==


J Cart Upsell and Cross-sell is a WooCommerce plugin that boosts your sales trough a modern, customizable modal shopping cart.

See it in action on a demo website!

<a href="https://jcartupsell.com/" target="_blank">J Cart Upsell and Cross-sell Demo</a>

= *NEW* =

Display upsells wherever you want with the *Upsells Shortcode*

= *NEW* =

Added integrations tab where you can enable integrations with popular plugins 

- WPRocket 
- WooCommerce Price Based on Country
- FOX – Currency Switcher Professional for WooCommerce
- CURCY – Multi Currency for WooCommerce

= *NEW* =

Polylang compatible

= *NEW* =

= Multiple goals on the dynamic bar = 

Now you can set multiple goals with the dynamic bar option

= Fully customizable =

Change colors, text and plugin behaviour with the plugin customization backend.



= Upsells =

Show to your customers targeted upsell, directly in their modal cart or in the checkout recap table, with customized text.

Choose and customize the conditions that trigger the upsell ( premium only ):

- products categories and/or attributes

- a defined list of products 

and add filters like ( free and premium ):

- only if user is logged in

- only if cart has subotal greater than

= Gifts =

Give to your customers dedicated gifts when their cart meet defined conditions.

Choose and customize the conditions that trigger the gift ( premium only ):

- products categories and/or attributes

- a defined list of products 

and add filters like ( free and premium ):

- only if user is logged in

- only if cart has subotal greater than

Add a label to the archive page's products that can trigger a gift

Add a customized text on the single product page that can trigger a gift



= Dynamic Bar =

Set a goal and display on top of the modal cart a dynamic bar synchronized to the cart total

**Example 1:** set the dynamic bar goal at the same amount of your free shipping limit and let the customers know how much they need before getting that

**Example 2:** give a gift at 150 and set the dynamic bar goal to the same amount, to encourage customers to purchase more

= Premium Version =

Premium version offers more versatility about your gifts and upsells.

**Checkout upsells**

You can show your upsells directly in the checkout page

**Gifts by coupon code**

Trigger a gift only if a certain coupon code is applied to the customer's cart 

**Advanced conditions**

Create mixed and advanced conditions to trigger your upsells and/or your gifts

**Backoffice order recap helpers**

Add a column on your orders backoffice tabs that shows if the order has been pumped

= Template overrides =

You can override template files coping the template folder inside the plugin into your child theme and renaming it from templates to **wc-j-upsellator**.

= Hooks ( actions and filters ) =

`add_filter('wjufw_shipping_bar_limit', function( $limit ){
    
    // manipulate the dynamic bar limit and return it 
    // return type: float or integer   

    return $limit; 
    
}, 999, 1 );`

`add_filter('wjufw_product_cart_limit', function( $limit, $product ){
    
    // manipulate the cart limit for a specific gifted/upselled product 
    // return type: float or integer   

    return $limit;  
    
}, 999, 2 );`

`add_action('wjufw_before_single_product_gift_text', function( $product_id ){
    
    // do something **before** gift text ( if set ), on the single product page  
    
}, 999, 1 );`

`add_action('wjufw_after_single_product_gift_text', function( $product_id ){
    
    // do something **after** gift text ( if set ), on the single product page  
    
}, 999, 1 );`

`apply_filters('wjufw_cart_item_name', function( $product->get_name(), $product, $product['key'] ){
    
    // this filter is like the woocommerce one (  woocommerce_cart_item_name ):
    // you can alter how the cart item name is displayed on the modal cart
    
}, 999, 1 );`

`apply_filters('wjufw_dynamic_bar_display', function(){
    
    // accepts true or false as return
    // true - dynamic bar displayed as usual
    // false - dynamic bar removed
    
} );`

**Cover image credits**

<a href="http://www.freepik.com">Designed by pikisuperstar / Freepik</a>

**Contribute and credits**

J Cart Upsell and Cross-sell for WooCommerce is developed and mantained by GH S.R.L.

= Support =


* For the free version of the plugin, use the official forum or mail directly the author trough the **Mail the author** button.


* All premium products include premium support.



== Installation ==

**Minimum Requirements**

* PHP 7.1 or higher 

* MySQL 5.6 or higher





J Cart Upsell and Cross-sell for WooCommerce can be installed directly through your WordPress Plugin Board.

* Click "Add New" and search for "J Cart Upsell and Cross-sell for WooCommerce".

* **Install and activate.**



Alternatively you can download the plugin using the download button on this page and then upload the "wc-j-upsellator" folder to the / wp-content / plugins / directory then activate through the WordPress plugins page.


== Screenshots ==

1. J Cart Upsell and Cross-sell for WooCommerce with carousel and logo
2. Checkout Upsell
3. J Cart Upsell and Cross-sell for WooCommerce without logo 
4. J Cart Upsell and Cross-sell for WooCommerce admin options



== Frequently Asked Questions ==

= Do i need to enable something? =

In order to let *J Cart Upsell and Cross-sell for WooCommerce** work properly, be sure to enable the "Activate the Ajax add to cart buttons" in the  WooCommerce -> Settings -> Products tab

= Can i use the plugin without purchasing the premium version? =


Yes, you can use **J Cart Upsell and Cross-sell for WooCommerce** without purchasing the premium version.

= Can i have multiple upsells triggered by single product? =

You can choose between standard mode, stacked and carousel.

With the standard mode, only **one upsell at time** is offered to the customer. 
If the customer accepts that upsell, he will see the next one in the priority list.

However, you can play with the priority order of **J Cart Upsell and Cross-sell for WooCommerce**: an upsell by product list is stronger than one by category/attribute list that is stronger than one triggered by cart limit.

With **stacked** you can display how many upsells at the same time as you wish, stacked on eachother.

With **carousel**, all upsells are displayed at the same time trough a carousel.

= Is multilanguage compatible? =

Since version 3.0.0, J Cart Upsell and Cross-sell for WooCommerce is **compatible** with polylang. Be sure to have also the Polylang WooCommerce add-on installed.

= Does it works with Elementor or Divi? =

Yes, **J Cart Upsell and Cross-sell for WooCommerce** works fine with Elementor and Divi. If you are using the Elementor cart widget and you want to keep that widget because it looks cool, you need to add the **wc-j-upsellator-show-cart** class to it. This class will make that element able to trigger the modal cart.


Since 2 modal carts can't be both active at the same time, the modal cart of Elementor cart item will be disabled.


= What if my theme already has a modal cart? =

**J Cart Upsell and Cross-sell for WooCommerce** by default keeps your theme cart-fragments untouched so it will all works properly.

= Is it PHP 8.x compatible? =

Yes, it works perfectly with the new version of PHP.

= The subtotal is displayed without VAT. How can i fix? =

In the main options page, try to activate the "Cart total" option.

= What if I want more features? =

Contact me, maybe these features can be implemented directly into **J Cart Upsell and Cross-sell for WooCommerce**.


== Changelog ==

**18 of September 2023** - J Cart Upsell and Cross-sell for WooCommerce version 3.4.5

= Version - 3.4.5 =
* Add - Display product meta-data on modal cart

= Version - 3.4.3 =
* Add - Compatibility with WordPress 6.3 and WooCommerce 8.0.2

= Version - 3.4.1 =
* Add - Option to exclude gift if the same product is already in cart as upsell

= Version - 3.4.0 =
* Fix - Issue when recaculating gifts

= Version - 3.3.99 =
* Add - Compatibility with WooCommerce 7.8.0

= Version - 3.3.96 =
* Add - Blurred gifts always visible on cart

= Version - 3.3.92 =
* Add - Dynamic Bar Shortcode

= Version - 3.3.9 =
* Add - Upsells sorting

= Version - 3.3.8 =
* Fix - Reversed conditions during upsell creation

= Version - 3.3.7 =
* Fix - Mobile Carousel issue

= Version - 3.3.4 =
* Beta - Added upsells shortcode
* Beta - Added integrations page

= Version - 3.2.5 =
* Fix - Fixed displayed price when with vat/without vat set on WooCommerce 
* Add - Option to change cart products image ratio

= Version - 3.2.1 =
* Fix - Upsell issue when changing type (variable, simple, etc..) of the underlying product 

= Version - 3.2.0 =
* Add - Add negation option for in cart products and categories/attributes conditions

= Version - 3.1.8 =
* Fix - Css fix if the upsell description is higher than container's height

= Version - 3.1.2 =
* Add - Option to keep the upsell in cart (if added) even if conditions are not met anymore

= Version - 3.0.69 =
* Fix - VAT not calculated on modal cart shipping row

= Version - 3.0.6 =
* Add - Option too choose if clicking an upsell should add it to cart or redirect the customer to the product page

= Version - 3.0.3 =
* Fix - Fix dynamic bar to match woocommerce decimals

= Version - 3.0.0 =
* Add - Polylang compatibility
* Fix - Code improvement

= Version - 2.9.7 =
* Add - Added option to display coupon code input in the modal cart
* Fix - Minor bug fixes

= Version - 2.9.5 =
* Add - Empty modal and modal footer buttons are now templates, so you can override them
* Add - New display mode, in the style tabs, small, to make the modal cart smaller

= Version - 2.9.3 =
* Fix - Full cart page upsells not working when background mode is active

= Version - 2.9.2 =
* Fix - Full cart page upsells not working when only background enabled

= Version - 2.9.0 =
* Add - Official WooCommerce Product Bundles plugin compatibility
* Fix - Gifts now are displayed at the end

= Version - 2.8.8 =
* New - (Premium version) Advanced upsell stats
* Add - Hover color option for modal buttons
* Add - Advanced upsells stats (premium only)
* Add - WPC Product Bundles compatibility

= Version - 2.8.4 =
* Change - Stats page made in ajax, with more data

= Version - 2.8.2 =
* Add - Exclude gift if same product already in cart as normal product

= Version - 2.8.1 =
* Add - Max quantity option if allowed quantity change for an upsell
* Fix - Carousel not working in full cart page 

= Version - 2.8.0 =
* Add - Before submit button hook for cart page upsells
* Fix - Carousel not working in full cart page 

= Version - 2.7.8 =
* Fix - Code improvement
* Fix - Fixed bug that allowed to display upsells even if not enough stock
* Beta - Add beta option to display WC notices on modal cart, without page redirect

= Version - 2.7.5 =
* Fix - Add filter to fix urlencoded attributes in some languages

= Version - 2.7.4 =
* Fix - Full cart page upsells not working

= Version - 2.7.3 =
* Fix - Fix modal cart auto-open when saving with Elementor builder
* Add - Added wjufw_upsell_is_valid filter

= Version - 2.6.7 =
* Add - Option to exclude upsell if same product already in cart as gift
* Bug - Cart total ( for upsells and gifts ) calculated based on applied discount

= Version - 2.6.0 =
* Add - Search filter in the excluded pages section
* Add - New option for upsells: sold individually

= Version - 2.5.7 =
* Change - Recoded the excluded pages section

= Version - 2.5.2 =
* Change - Dynamic bar re-code
* Add - Dynamic bar multiple goals
* Add - More dynamic bar icons
* Add - Option to disable quantity selector on modal

= Version - 2.1.7 =
* Fix - WooCommerce 6.4 compatibility
* Fix - Dynamic Bar bug

= Version - 2.1.6 =
* Fix - Fixed discount bug when "prevent upsell discount" activated

= Version - 2.1.5 =
* Add - Full cart page upsells

= Version - 2.1.3 =
* Add - Support for WooCommerce 6.3.1 
* Add - Support for Wordpress 5.9.2

= Version - 2.1.2 =
* Add - Checkout upsell position switcher ( PRO )
* Add - Option to exclude upsell from coupons discount
* Add - Option too choose max number of upsells displayed when "stacked" selected

= Version - 2.0.1 =
* Fix - Compatibility with "Advanced product fields for WooCommerce"
* Add - Upsell quantity

= Version - 1.67.0 =
* Add - "woo-j-gift" and "woo-j-upsell" classes to cart page items when they are .. gifts and upsells!
* Add - Filter to display or not the dynamic bar - 'wjufw_dynamic_bar_display'

= Version - 1.66.0 =
* Fix - Fix dynamic bar calculation when woocommerce decimal and thousands separator edited

= Version - 1.65.5 =
* Add - Prices displayed in accord to WooCommerce settings - decimal separator, thousands separator, ..

= Version - 1.65.0 =
* Add - WordPress 5.9 compatibility
* Add - WooCommerce 6.1 compatibility

= Version - 1.62.5 =
* Add - Modal cart header css change

= Version - 1.62.0 =
* Fix - Bug in "add to cart single page" script
* Add - Compatibility with official "Woocommerce Composite Products" plugin

= Version - 1.61.1 =
* Fix - Bug in "add to cart single page" script
* Add - Option to choose between "single upsell" - "carousel" - "stacked upsells"

= Version - 1.60.1 =
* Add - WooCommerce 6.0.0 compatibility

= Version - 1.60.0 =
* Add - option to use J Cart Upsell and Cross-sell only as background logic
* Add - option to change quantity of gifted product

= Version - 1.60.0 = Premium
* Fix - fixed a bug when coupon option set and then removed

= Version - 1.58.9 =
* Add - small css changes
* Add - new dynamic bar style

= Version - 1.58.0 =
* Premium only - added option to trigger gifts only if a certain coupon is applied

= Version - 1.55.0 =
* Add Request - added option to trigger gifts/upsells only if user is logged

= Version - 1.52.0 =
* Add Request - added option to exclude virtual product to the cart count limit that triggers gifts
* thanks to Carl for the suggestion

= Version - 1.51.0 =
* Fix - fixed a bug when calculating products taxes
* Add - filter to change cart item name -- apply_filters( 'wjufw_cart_item_name', $product->get_name(), $product, $product['key']  )

= Version - 1.50.4 =
This is a major change 
* Enhancement - Code refactoring
* Add - filter to change dynamic bar limit -- apply_filters( 'wjufw_shipping_bar_limit', $limit, $limit )
* Add - Compatibility to currency exchange plugin ( WOOCS - WooCommerce Currency Switcher )
* Add - Synchronized prices with WooCommerce VAT settings: product prices with/without taxes, product displayed with/without taxes on cart

= Version - 1.30.3 =
* Add - Option to choose currency position: before or after price 
* Add - Option to change modal cart header text 
* Add - Option to show or not upsell label if condition set to "no discount"
* Fix - Fixed css input number bug for Firefox

= Version - 1.25.3 =
* Enhancement - Upsells/Gifts edit now via AJAX

= Version - 1.21.5 =
* Add - Added option to show multiple upsells at time, via carousel
* Add - Added option to display a random upsell when multiple are triggered

= Version - 1.15 =
* Add - Added CSS hook to dynamic bar price 

= Version - 1.14 =
* Fix - Issue with VAT on subtotal. Added option to change between WC()->cart->subtotal and WC()->cart->get_subtotal() in the main option page

= Version - 1.13 =
* Add - Shortcodes tab with a new shortcode for a dynamic items counter
* Fix - Issue when adding to cart products with price as zero ( free )
* Change - Changed "shipping bar" to "dynamic bar"

= Version - 1.11 =
* Add - Added modal cart custom logo upload

= Version - 1.10 =
* Fix - Decimal issue in the modal footer

= Version - 1.09 =
* Add - Added actions to better customize single product page when triggering gift
- do_action('wjufw_before_single_product_gift_text', $gift_product_id );
- do_action('wjufw_after_single_product_gift_text', $gift_product_id );

* Fix - Ignoring trashed orders from stats page
* Fix - Adjusted stats charts when viewport smaller than 1300px
* Fix - Rounded decimals on stats page

= Version - 1.08 =
* Add - Added subtotal condition as a default condition for every upsell/gift

= Version - 1.07 =
* Add - Redirect to cart page cliclikg on .wc-j-upsellator-show-cart class if plugin not loaded on that page

= Version - 1.05 =
* Add - Pause / Start on upsells and gifts
* Add - Modal cart logo theme

= Version - 1.04 =
* Fix - Fixed upsell displayed price

= Version - 1.03 =
* Add - Added go to cart button option

= Version - 1.02 =
* CSS Fix for stats page

= Version - 0.99.45 =
* Stable version

= Version - 0.99.45 =
* Enhancement - Merged attributes and categories conditions for complex filters

= Version - 0.99.2 =
* Add - Added upsell priority
* Fix - Fixed a bug when deleting a product from woocommerce used as gift
* Fix - CSS letter-spacing fix

