=== RNWP App template config ===
Contributors: mkhaledche
Tags: mobile app, react native, rest api, android, ios
Donate link: https://www.paypal.com/paypalme/mkhaledche
Requires at least: 1.0.0
Stable tag: trunk
Tested up to: 5.9.3
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

This plugin adds search functionality through REST API over all enabled post types and adjust the maximum numbers of posts to fetch through the REST API to be more than 100.
Also through this plugin, the user can configure your app settings using RNWP app template. This can be done using the WordPress dashboard through this plugin.

== Description ==
This plugin facilitates building a mobile app for your website on both Android and IOS platforms either with the help of [RNWP template](https://www.codester.com/items/26420/rnwp-react-native-template-for-wordpress-sites?ref=mkhaledche) or through a custom template using the REST API routes and functionalities provided by this plugin.

Through this plugin, a REST API to search over the post types chosen by the user is supported. The default WordPress REST API `{post_type}` route searches only through one post type. The default `search` route doesn\'t return the post content. So this plugin registers a rest route that can search more than one post type and return the post title, content, and excerpt for all posts relevant to this search. The rest route can be accessed by going to this link: `your-website.com/wp-json/search/v1/search`. 

The search rest route has two parameters; the parameter `per_page` can be adjusted in the plugin admin dashboard. The `keyword` parameter shall be related to the search text.

As the WordPress default, REST API limits the post query to 100 post objects. If the website has more than 100 posts to download, the user can adjust the plugin to download the number of posts he wishes and the limit can be more than 100 post objects.

If the user is using Woocommerce, this plugin ensures the product categories and tags will have a REST API so that the app can access them.

This plugin shall help the user of [RNWP template](https://boostrand.gumroad.com/l/rnwp) configure his mobile app preferences without the need to write code. 

The User can adjust the main URL for his app (will help for multilingual websites), post types or taxonomies to be shown, the number of posts per page, app custom texts (e.g. screen, settings, and notification messages), light and dark mode colors, contact details and social links, Admob settings and many other settings. 

After adjusting the website preferences, the user can copy the generated code to the app template and the app would be ready to be built.


== Installation ==
You can follow this link for detailed instructions on how to use the plugin:
[Configure your App Options userConfig js file and RNWP plugin](https://boostrand.com/configure-your-app-options-userconfig-js-file-and-rnwp-plugin/)

If you have any question, you can [email me](mailto:mkhaled.che@gmail.com).
== Frequently Asked Questions ==
= Can I use this app if I don\'t have the RNWP template? =

If you need the custom search REST API or increase the REST API search query limit or add the `product_cat` and `product_tag` taxonomy archives to REST API, then this can be availed through the plugin even if you don\'t have the RNWP template.

= Should I use the plugin or keep it activated on my Website after I build the app? =
You can use the app without the plugin and you can configure the app then deactivate the plugin. However, this will have the following drawbacks:

The plugin considers a search API that searches through all posts, pages and custom post types. If you need this functionality, then you’d always need the API to be activated. Otherwise, you should change the plugin installed key to false in the userConfig.js file and choose only one post type to apply search on.
If you are using Woocommerce, this plugin ensures the product categories and tags will have a REST API so that the app can access them. If the plugin is uninstalled, they won’t show in the app.
The default limit to the number of posts to be fetched by the REST API is 100, if you wish users download your posts or custom post types which are more than 100, you can choose the maximum number of posts to download. This plugin would modify this limit to the limit you wish. If you don’t want to use the plugin but need this functionality, then you should consider adding a code doing this job on your website.


== Screenshots ==
1. Choose supported post types and taxonomies
2. Choose app colors
3. Object to be copied to RNWP app template

== Changelog ==
First version
