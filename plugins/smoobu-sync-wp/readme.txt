=== SmoobuSyncWP ===

Contributors: Sagacitas Technologies Private Limited
Tags: smoobu, property-sync, availability-sync, woocommerce, booking, calendar
Requires at least: 5.3
Tested up to: 6.4.3
Stable tag: 1.2.2
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Effortlessly automate your Smoobu property management directly from your WordPress site.

Key Features:

-   **Seamless availability sync:** Keep your WordPress calendar and Smoobu availability in perfect harmony.
-   **Streamlined booking:** Accept new bookings through WooCommerce's secure and efficient **checkout page (not rendered by blocks, using the  `[woocommerce_checkout]`  shortcode)**.
-   **Simple setup:** Configure your plugin in minutes with your Smoobu API key and easy-to-follow instructions.
-   **Enhanced control:** Manage availability, bookings, and settings directly from your WordPress dashboard.

== Installation ==

1.  **Download:** Find SmoobuSyncWP in the WordPress plugin repository or download it directly.
2.  **Upload:** Upload the plugin files to the `/wp-content/plugins/smoobu-sync-wp` directory.
3.  **Activate:** From your WordPress dashboard, navigate to Plugins and activate SmoobuSyncWP.
4.  **Configure:** Go to SmoobuSyncWP > Settings and enter your Smoobu API key along with your desired synchronization options.
5.  **Start managing:** Enjoy effortless synchronization and booking management for your Smoobu properties!

== Frequently Asked Questions ==

= How do I get started? =

2.  Download and install the plugin.
4.  Enter your Smoobu API key and configure synchronization settings.
6.  Save your settings and let SmoobuSyncWP handle the rest.

= Can I sync property availability with WordPress? =

Absolutely! SmoobuSyncWP ensures your WordPress calendar always reflects up-to-date availability information from Smoobu.

= Can I register bookings through WooCommerce? =

Yes! SmoobuSyncWP integrates seamlessly with WooCommerce, allowing guests to book your properties directly through your **single-page checkout page (not rendered by blocks, using the `[woocommerce_checkout]` shortcode)**.

== Additional Information ==


-   **Requirements:** This plugin requires WooCommerce and Cashier plugins.
-   **Smoobu Calendar Shortcode:** Use `[smoobu_calendar]` with these parameters:

    -   `property_id`: Your Smoobu property ID.
    -   `layout`: Format (height x width) of the calendar.
    -   `link`: Generated WooCommerce Cashier link for checkout.

-   **WooCommerce Cashier Setup:**

    2.  Go to WooCommerce > Cashier.
    4.  Select a product in "Products to add to cart" and a checkout page in "Redirect to page".
    6.  Copy the generated link and use it as the `link` parameter in the `smoobu_calendar` shortcode.

-   **Addon Tax Class:** Create an Addon tax class in WooCommerce > Settings > Tax.
-   **Smoobu User Details:**

    1.  Go to Smoobu Calendar > General Settings.
    2.  Paste your API key and click "Check Connection".
    3.  Then click Save.

-   **Availability & Property List Sync:**

    2.  Go to SmoobuSyncWP > Data Renewal.
    4.  Click "Save Availability" and "Save Property List".

-   **Webhook URL:**

    2.  Go to Smoobu Calendar > Webhook.
    4.  Copy the link and paste it in SmoobuSyncWP Settings > For Developers > Webhook URLs.


== Screenshots ==

Plugin settings page.
WooCommerce checkout integration.
Property availability calendar.

== Changelog ==

= 1.2.2 =

Fixed undefined indexes and variables notices.

== Upgrade Notice ==