=== Awin - Advertiser Tracking for WooCommerce ===
 
Contributors:      awinglobal
Plugin Name:       Awin Advertiser Tracking
Plugin URI:        https://wordpress.org/plugins/awin-advertiser-tracking
Tags:              awin, affiliate, zanox, affiliate window, advertiser, tracking
Author URI:        https://profiles.wordpress.org/awinglobal/
Author:            awinglobal
Requires at least: 3.5
Tested up to:      6.8.2
Stable tag:        2.0.2
Version:           2.0.2
Requires PHP:      7.3


== Description ==
Awin is a global affiliate network with over 200,000 contributing publishers and 29,500 advertisers, connecting customers with brands in over 180 countries around the globe. The Awin Tracking extension allows for seamless integration of our core Advertiser Tracking Suite within WooCommerce.

= About =

The extension works by appending our sale attribution technology onto your shop website and populating the relevant dynamic variables required to record conversions for your affiliate programme. Use of the Awin extension will not change the appearance of your e-commerce site.

To use this extension you must have an Awin advertiser account, please contact our sales department for further information if you wish to join as an advertiser. Additional fees apply.

= Features =

<li style="margin: 0px; padding: 0px;">
**Enhanced Sale Attribution** Available within the extension is our core tracking technology, which directly feeds into our reporting suite at Awin. These transactions are transparently displayed in the Awin interface. Within this package, we provide accessibility to our MasterTag and Server-to-Server solution which allows for enhanced and accurate conversion tracking.
</li>
<li style="margin: 0px; padding: 0px;">
**Voucher Code Tracking** Voucher/Coupon code tracking is an effective way for advertisers to monitor the vouchers used in a transaction. The data is clearly highlighted to advertisers through the Awin interface and you can choose to accept/decline commission based on the usage of non-compliant voucher codes.
</li>
<li style="margin: 0px; padding: 0px;">
**MultiCurrency Support** The currency used by your customer at checkout will be automatically handled and converted to your invoicing currency on the Awin platform.
</li>
<li style="margin: 0px; padding: 0px;">
**Product Level Tracking (PLT)** PLT enables an advertiser to produce more in-depth reporting where the performance of individual products can easily be measured. Monitor the effectiveness of different product promotions and their impact on consumers' buying behaviour in the affiliate channel.
</li>

= Privacy =
For the purpose of attributing sales and commissions to publishers, Awin will process checkout information including the order reference. This data will be sent and processed on the dwin1.com and awin1.com domains. Any queries regarding the processing of this data can be directed to your dedicated integration contact. Please also see our [privacy policy.](https://www.awin.com/gb/legal/privacy-policy-gb)

== Installation ==
1. Install the plugin
2. Go to settings -> Awin Advertiser Tracking
3. Set your Awin Advertiser ID (Provided by your Integration/Account Contact)
4. Set the Awin Authorization Bearer token ("OAuth2 Token" in Awin dashboard). You can find this token in the API credentials section of your Awin dashboard.
5. Set the Approval Delay (Days). This is used to check if the order status is complete and whether its completed state is greater than the specified number of days.
6. Done.

== Changelog ==

= 2.0.2 =
* Tested with WordPress 6.8.2

= 2.0.1 =
* Added nonce to feed generation to prevent CSRF attacks.
* Added rawurlencode to basket item name

= 2.0.0 =
* Added aw_deep_link field.
* Fixed issue in checkout where calls were firing multiple times

= 1.3.1 =
* Added cron job for generating product feed.
* Fixed bug with Fall-back conversion pixel not firing due to lazy loading of images
* Improved cookie handling
* Tested with WordPress 6.7.1

= 1.2.0 =
* Added customerAcquisition parameter to conversion tags
* Implemented Approve, decline, amend batch transactions for a given advertiser
* Improved Code Quality.
* Tested with WordPress 6.4

= 1.1.5 =
* Added support for WP multisite installations, tested with Wordpress 6.3

= 1.1.4 =
* Added product categories to basket items

= 1.1.3 =
* Fixes get_used_coupons issue, tested with WordPress 6.1.1 / WooCommerce 7.3.0

= 1.1.2 =
* Fixes bug when viewing historical order data

= 1.1.1 =
* tested with WordPress 6.0.1 

= 1.1.0 =
* tested with WordPress 5.9.2 

= 1.0.9 =
* tested with WordPress 5.6.1 

= 1.0.8 =
* adds check for undefined url parameters 

= 1.0.7 =
* fixes a bug that the script was not rendered in the footer
* adds CDATA to MasterTag script 

= 1.0.6 =
* fixes with Server-to-Server and empty voucher codes
* fixes bug with Product Level Tracking (PLT) values

= 1.0.4 =
*  optimises javascript on checkout

= 1.0.3 = 
* fixes bug with Server-to-Server

= 1.0.2 = 
* changes order number

= 1.0.1 = 
* minor bugfix

= 1.0.0 = 
* first release