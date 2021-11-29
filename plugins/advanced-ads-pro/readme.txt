=== Advanced Ads Pro ===
Requires at least: WP 4.4
Tested up to: 5.5
Requires PHP: 5.6
Stable tag: 2.10.0

Advanced Ads Pro is for those who want to perform magic on their ads.

== Copyright ==

Copyright 2014-2020, Thomas Maier, Advanced Ads GmbH, https://wpadvancedads.com/

This plugin is distributed by Advanced Ads GmbH. Arrangements to use it in themes and plugins can be made individually.

== Description ==

Advanced Ads Pro extends the free version of Advanced Ads with additional features that help to increase revenue from ads.

Features:

* check delivered ads within the admin bar in the frontend
* cache-busting to lazy load ads on cached pages
* test placements against each other
* option to limit an ad to be displayed only once per page
* refresh ads without reloading the page
* flash ad type with fallback
* select ad-related user role for users
* inject ads into any content which uses a filter hook
* click fraud protection
* alternative ads for ad block users
* lazy loading
* place custom code after an ad
* disable all ads by post type
* serve ads on other websites

placements

* use display and visitor conditions in placements
* pick any position for the ad in your frontend
* inject ads between posts on posts lists, e.g. home, archive, category
* inject ads based on images, tables, containers, quotes and any headline level in the content
* ads on random positions in posts (fighting ad blindness)
* ads above the main post headline
* ads in the middle of a post
* background / skin ads
* set a minimum content length before content injections are happending
* set a minimum amount of words between ads injected into the content
* dedicated placements for bbPress
* dedicated placements for BuddyPress
* show ads from another blog in a multisite
* repeat content placement injections
* allow Post List placement in any loop on static pages
* ad server to embed ads on other websites

display and visitor conditions:

* display ads based on where the user comes from (referrer)
* display ads based on the user agent (browser)
* display ads based on url parameters (request uri)
* display ads based on user capability
* display ads based on the browser language
* display ads based on number of previous page impressions
* display ads based on number of ad impressions per period
* display ads to new or recurring visitors only
* display ads based on a set cookie
* display ads based on page template
* display ads based on post meta data
* display ads based on post parent
* display ads based on the day of the week
* display ads based on language of the page set with WPML

== Installation ==

Advanced Ads Pro is based on the free Advanced Ads plugin, a simple and powerful ad management solution for WordPress.
You can use Advanced Ads along any other ad management plugin and don’t need to switch completely.

== Changelog ==

= 2.10.0 =

- auto hide all ads after Click Fraud Protection is triggered
- Click Fraud Protection: use module-wide or individual ad click limit, whichever is more strict
- prevented displaying some warnings by amp validator
- integrate with TCF 2.0 compatible consent management platforms

= 2.9 =

- added more string compare options to the Cookie visitor condition
- added BuddyBoss placement to inject ads into the activity stream
- switched element picker for Custom Position placement when using Advanced Ads 1.19
- auto-save placement page after parent element was selected for Custom Position

= 2.8.2 =

- backend UI improvements to module activation and date fields
- prepare for Advanced Ads 1.19
- removed unneeded debug line from Browser Console
- fixing incorrect symbols in numeric fields automatically

= 2.8.1 =

* open ads loaded through the Ad Server automatically in a new window to prevent loading the target page in an iframe
* changed behavior of injection based on img tags to look for any images in the content except within tables
* Cache busting: made possible to use html attributes that contain JSON strings
* fixed error that happened when applying Random Paragraph placement to one-paragraph text
* don't take into account the "Words Between Ads" setting when inserting a first ad

= 2.8 =

* New: Ad Server placement to embed ads on other websites
* New: show Post List placement on archive pages created by the AMP for WP plugin
* made placements of type other than "Header Code" work with "Thrive Theme Builder" theme
* shift ads from bottom when "repeat the position" and "words between ads" settings are in use
* marked Flash module as deprecated. New users can no longer enable it. Find the schedule [here](https://wpadvancedads.com/manual/deprecated-features/#Pro_%3E_Flash_ad_type)
* removed legacy code for URL Parameter visitor conditions since it moved to display conditions in 2016
* removed legacy code for minimum content length option as set before 2016 in the main plugin settings
* disallowed ad insertion into the header of the WP File Manager's admin page

= 2.7.1 =

* Group Refresh feature: prevented impression tracking when it is disabled in the Tracking add-on
* fixed Custom Position placement showing in the footer when selector does not exist
* fixed broken link in the description of the User Agent condition

= 2.7 =

* use Display and Visitor Conditions in placements
* allow content injection based on iframe tag
* set minimum amount of words between ads injected into the content
* show the link to duplicate an ad only when the ad was already saved once
* moved output of "Custom Code" outside the link
* fixed clearfix option of Custom Position placement
* fixed wide 'select' elements in conditions that broke layout
* fixed possible bug that prevented Pro settings from being saved

= 2.6.2 =

* added `advanced_ads_pro_output_custom_code` filter to manipulate the Custom Code option
* prevented returning default language in the WPML plugin when AJAX cache-busting is used
* prevented reset of the "Disable ads for post types" option when saving Pro settings
* fix "Disable ads for post types" option when using AJAX cache-busting
* fixed possible PHP warning

= 2.6.1 =

* fixed a minify-related bug that prevented some Custom Position placement from working

= 2.6 =

* new feature: duplicate ads
* load group name to Cache Busting code as per request by a customer
