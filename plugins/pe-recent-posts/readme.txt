=== PE Recent Posts ===
Contributors: pixelemu
Donate link: https://pixelemu.com
Tags: slides, latest post, latest posts with thumbnails, recent posts, thumbnails, widget, widgets, image, images, link, links, plugin, post, posts
Requires at least: 3.4
Tested up to: 5.5
Stable tag: 1.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The simple plugin that allows you to display image slides with title, description and read more linked to posts from selected category.

== Description ==
The simple plugin that allows you to display image slides with title, description and read more linked to posts from selected category. The slide title and description appear with slide-in animation effect.
The user may select the category or display items of all categories. 
Number of slides is unlimited and you may specify how many slides you want to be visible in column.
Image sizes available to select from the list are determined at Media Settings of Wordpress. This way the plugin do not have to scale images by itself which is more site optimization friendly solution. 

**Configuration (see screenshot of backend):**

1. Widget title.
2. Post type - enter post type name you want to display posts from.
3. Post type taxonomy - enter post type taxonomy name to restrict the data to display.
4. Taxonomy - select taxonomy items. You may select specified categories or display items from all categories. Empty taxonomy is not displayed.
5. Show archive links below items to give a possibility to read more posts from selected categories.
6. Force display sticky posts - applies to post type only, select if you want to include a sticky post to slides.
7. Number of items in a row.
8. Number of rows.
9. Make one row for a mobile devices (< 768px)
10. Number of all items - total number of slides.
11. Show or hide post titles.
12. Make post title linkable.
13. Choose header tag for titles.
14. Creation date - show/hide post creation date, the date format is taken from global settings, display date above or below the title.
15. Show or hide the author's name.
16. Show or hide post taxonomy items (a category name)
17. Readmore - show/hide read more link.
18. Order direction (ascending, descending).
19. Ordering type (date, title, most commented, most read).
20. Navigation (bullets, none).
21. Description limit - enter number of chars for each slide description.
22. Image alignment (left, right, top, bottom).
23. Show/hide thumbnail.
24. Thumbnail linkable - link the image to the full post
25. Interval in milliseconds for a sliding.
26. Enable or disable pause on hover.
27. Image size from Wordpress settings (Settings > Media). You can choose: thumbnail, medium, large
28. Grid spacing - space between items.

== Installation ==
1. Upload the 'pe-recent-posts' folder to the '/wp-content/plugins/' directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to the 'Widgets' page found under the 'Appearance' menu item
4. Drag 'PE Recent Posts' to the target widget area and choose your options

== Screenshots ==
1. The backend interface.
2. Example: 2 images per row, image alinged left, no read more
3. Example: 3 thumbnails in column aligned left.
4. Example: 2 large images in column aligned at top.
5. Example: 3 images in row, no description
6. Example: no images, latest posts from selected category

== Changelog ==

= 1.2 =
= fixed: =
* Fixed REST API and Loopback errors
= added: =
* Improved keyboard navigation according to WCAG guidelines

= 1.1.9 =
= added: =
* Added option to make one row for mobile devices

= 1.1.8 =
= fixed: =
* Fixed missing language string "more from" - new POT file generated
* Fixed displaying all posts if only some categories are selected
* Fixed Consistent Identification (AA) - 'List item used to format text' related to the navigation bullets

= 1.1.7 =
= added: =
* Taxonomy select list has been replaced by the multiselect

= 1.1.6 =
= fixed: =
* Fixed warning about create_function() for PHP 7.2+

= 1.1.5 =
= added: =
* Improvements related to indicators (bullets) to allow easy change slide/item for keyboard users

= 1.1.4 =
= added: =
* Option to enable/disable author
* Option to enable/disable post taxonomies
* Option to enable/disable 'more from category' link
= fixed: =
* Fixed displaying widget with Widget Shortcode plugin

= 1.1.3 =
= added: =
* Option to enable/disable post title
* Option to enable/disable link for post title
* Option to enable/disable link for thumbnail
= fixed: =
* Fixed linkable thumbnail for alignment left and right

= 1.1.2 =
= fixed: =
* Changed CSS plugin priority
= added: =
* Clickable thumbnails

= 1.1.1 =
= added: =
* Added additional div container into the layout

= 1.1 =
= fixed: =
* Fixed animation for arrows (up/down) navigation

= 1.0.9 =
= fixed: =
* Few CSS fixes

= added: =
* Added navigation with arrows (prev/next and up/down)

= 1.0.8 =
= fixed: =
* Remove 3 dots after desciption if description limit = 0

= added: =
* Added support for description in latin-ext languages
* Option to change interval
* Option to disable autoplay
* Option for pause on hover

= modified: =
* Alternative text for image grabbed from image's alt or from post's title if image's alt is empty

= 1.0.7 =
= fixed: =
* Fixed clearing elements in rows

= 1.0.6 =
= added: =
* Possibility to choose header tag for title
* Loading the plugin's translated strings

= fixed: =
* session_start() added as action

= 1.0.5 =
= fixed: =
* Fixed spaces between widget and container

= 1.0.4 =
= fixed: =
* Fixed error 404 for animate.css

= 1.0.3 =
= modified: =
* Removed unnecessary Bootstrap CSS

= added: =
* Option to show/hide creation date.
* Option to choose post type and post type taxonomy
* Hide widget heading when title is empty

= fixed: =
* Improving images displaying

= 1.0.2 =
= fixed: =
* Warning appeared when fields  "Number of items in row" and "Number of rows" were cleared
* Double bootstrap scripts loading. The plugin does not load bootstrap scripts if it is already loaded by the theme.
* Cleared space of grid for last items 

= added: =
* Better adjusting images to mobile devices - counting set number of images in row and dividing its number in row on small devices. If the set number of images in row is even, images are displayed as follows, for:  991px a 768px - 2 items in row, below 768px - 1 item in row. Otherwise images are decreasing in the row adjusting to the screen resolution but below 768px images are displayed 1 item in row.
* New option enable/disable loading sticky posts.
* Added separator for every row - useful when your images have different dimensions

= 1.0.1 =
= modified: =
* Extended possibilities for slides displaying. Now you may create a gallery grid by setting the number of slides per row.

= added: =
* Option to show/hide readmore button.
* Added "most read" value for ordering that allows to create galleries with latest posts.
* Option to show/hide navigation
* Option to show/hide thumbnails.
* Option to set a space between slides.