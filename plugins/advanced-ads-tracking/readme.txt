=== Advanced Ads – Ad Tracking ===
Requires at least: WP 4.9
Tested up to: 5.4.2
Stable tag: 1.20.3

Track ad impressions and clicks.

== Copyright ==

Copyright 2014-2020, Thomas Maier, Advanced Ads GmbH, https://wpadvancedads.com/

This plugin is not to be distributed after purchase. Arrangements to use it in themes and plugins can be made individually.
The plugin is distributed in the hope that it will be useful,
but without any warrenty, even the implied warranty of
merchantability or fitness for a specific purpose.

== Description ==

This add-on for the Advanced Ads plugin provides tracking ad impressions and clicks.

**Tracking:**

* count impressions either on load or after the ad was displayed
* choose between 4 tracking methods
* track impressions or clicks locally or with Google Analytics
* spread impressions and clicks over a period when an expiry date is set
* enable or disable tracking for all ads by default
* enable or disable tracking for each ad individually
* track clicks of an ad with automatically or manually placed links
* choose to open ad link in a new window
* add rel="nofollow" attribute to links

**Ad Planning**

* limit ad views to a certain amount of impressions or clicks

**Stats**

* see stats of all or individual ads in your dashboard based on predefined and custom periods, grouped by day, week or, month
* display stats in a table and graph
* compare stats for ads
* compare stats with the previous or next period
* remove stats for all or single ads
* filter stats by ad groups
* public stats for a single ad – e.g. to show clients
* send email reports for all or individual ads to different emails
* combine impressions and clicks with any other metrics in Google Analytics

**Stats Management**

* export stats as csv
* import stats from csv
* remove old stats

**on load**

track impressions when the ad is prepared for output

**after frontend is loaded**

track impressions after the frontend is completely loaded

software included:

* [jqPlot](http://www.jqplot.com), GPL 2

== Installation ==

The Tracking add-on is based on the free Advanced Ads plugin, a simple and powerful ad management solution for WordPress.
Before using this plugin download, install and activate Advanced Ads for free from http://wordpress.org/plugins/advanced-ads/.
You can use Advanced Ads along any other ad management plugin and don’t need to switch completely.

== Changelog ==

= 1.20.3 =

- added deprecation notice for "track after page loaded" method

= 1.20.2 =

* marked feature for [tracking of events on external sites](https://wpadvancedads.com/manual/tracking-external-events-and-affiliate-clicks/) as deprecated
* moved certain settings to an Advanced section on the Tracking settings page
* fixes tracking of impressions in the wrong database table when an ad is used from another site in a multisite network

= 1.20.1 =

* fixed potential theme conflict. Please update to the latest Advanced Ads version as well
* fixed missing index issue

= 1.20 =

* fixed CTR on ad overview list
* fixed ad stats being summed up as "Deleted ads" on the Stats page if they are from another language as set up in the WPML plugin

= 1.19 =

* added option to track ads that have a trigger only when they show up (applies to users of the Sticky Ads and PopUp add-ons)
* prevent browsers from caching the click-tracking redirect
* decrease height of ad stats graph
* show click-through-rate on ad overview page
* fixed bug with Google Analytics tracking + Cache Busting + Lazy Load not tracking reliably
