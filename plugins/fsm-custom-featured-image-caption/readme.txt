=== FSM Custom Featured Image Caption ===
Contributors: fesomia
Tags: featured image, caption, images, credits, copyright
Requires at least: 4
Tested up to: 5.8
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Author URI: https://wp.fesomia.cat
Plugin URI: https://wp.fesomia.cat/plugins/fsm-custom-featured-image-caption
Donate link: https://wp.fesomia.cat/donate

Allows adding custom captions to the featured images of the posts.

== Description ==

This plugin allows adding custom captions to the featured images of the posts. It works with both Gutenberg and the classic editor.

That's how it works:

* If no caption is indicated, the plugin will display the generic caption defined in the Media Library.
* If a caption is indicated, the plugin will display this caption instead of the generic caption defined in the Media Library.
* If no caption is indicated and no legend exists in the Media Library, none will be displayed (obviously).

The plugin also allows to:

* Hide the caption, either the original from the Media Library or the custom one.
* Entirely hide the featured image in the public view, without having to de-attach it from the post.
* Configure several options to modify the styles used in the caption.
* Output the text of the caption anywhere in your theme using a custom function.
* Output the featured image with caption inside your content with a shortcode.

For maintaining a semantic code, this plugin writes the caption in a `<figcaption>` label.

**New in version 1.21**: Compatibility with Divi themes. This is an experimental feature to allow the plugin to work with Divi, as many users requested. Note that it may not work for all layouts, and that since it replaces the function divi uses to show featured images, when enabled it may potentially cause problems with older versions of Divi or if the core theme is updated. As always, it may also be necessary to add custom css to your theme to show the captions the way you want

= Format =

The caption will adopt the format specified for the `<figcaption>` element and the wp-caption-text CSS class, which is common in WordPress themes.


= Usage =

The plugin works out of the box. To start using it, all you need to do is activate it and play with the new options in your edit page. For advanced users, you will found a special settings page for fine-tuning some details.

Go to *Settings > FSM Custom Featured Image Caption* to set up the CSS and HTML parameters:

* **CSS for caption text**: Choose one of the options to modify the class/styles that will be used in the caption container (by default is wp-caption-text). Note that you can indicate more than one class separated with spaces.
* **Allow HTML code**: Check it if you want to parse the caption text as HTML if you need the browser to parse HTML tags instead of showing them. Keep in mind that incorrect HTML code or orphan tags can break your layout.
* **Allow shortcodes**: Check it if you want to parse the shortcodes present in the caption text. Note that if the shortcode returns HTML it will not be filtered, regardless of the "allow html code" option state; also, complex shortcodes output may alter your layout. When activated, you can write the shortcode you want to use in the caption box in the usual way, i.e. : `Venetian landscape [myshortcode option1="text"]`
* **Show image captions in lists**: Disabled by default. Check if you want to show the caption when the featured image appears in lists, i.e. in a category page, in a widget with latest posts, etc. Note that some themes may add containers around the image (i.e. a link tag) witch can conflict with the aspect of the caption.
* **Enable compatibility with Divi themes**: Disabled by default. Allows the plugin to work with Divi. Please read the description and the FAQ sections for more info about this option

= Shortcode =

We’ve added a shortcode that allows you to put the featured image (and it’s caption) in your page. Just write `[FSM_featured_image]` anywhere in the content edit box of your post or page to display it. You can also select the size of the image with the parameter "size". Note that defined image sizes may differ between WordPress themes. The defaults are these: thumbnail, medium, medium_large, large and full.

By using the shortcode, the plugin will try to remove the featured image from your theme’s default position to prevent it from appearing twice. Anyway, this is as this is an experimental feature and results may fluctuate. It also will not appear in lists.

Note that the usage of the shortcode is not related to the "Allow shortcodes" option in the settings page.

Usage example: `[FSM_featured_image size=thumb]`

= For developers =

The plugin comes with two public functions that allow you to get or output the featured image caption of the current post anywhere in your template.

Once the plugin is activated, use `<?php get_FSM_featured_image_caption()?>` to return a string containing the text that you can assign to a variable or `<?php the_FSM_featured_image_caption()?>` where you like the caption be displayed.

Both functions accept parameters passed inside an array with the following keys:

* **tag**: The tag (without brackets) you want to use as a container. By default is `div`. If set to false, it will remove it and will return the caption text.
* **class**: The name of the class/classes you want to use for the container. Use spaces to separate them. Empty by default.
* **style**: The CSS styles to be used in the container tag. Empty by default.
* **force_visibility**: When set to true, it ignores the hide caption option defined by the post editor. Useful for displaying your caption in a different place, but showing the featured image. Default: `false`.
* **allow_html**: like in the settings page, if set to true, allows the browser to parse the HTML code inside the caption text, else shows it as plain text. Default: `false`.
* **allow_shortcodes**: again the same behaviour than in the settings page. Default: `false`.

Usage example: `php
the_FSM_featured_image_caption( array('tag' => 'p', 'class' => 'class1 class2', 'style' => 'color: red;', 'allow_html'=> true, 'allow_shortcodes'=>true );
`

= Translations =

The plugin comes up with three complete translations:

* English (en)
* Spanish (es_ES)
* Catalan (ca)

You can contribute to translating it into more languages at https://translate.wordpress.org/projects/wp-plugins/fsm-custom-featured-image-caption/

= Coming soon =

* More options for selecting the text to be used as the caption, allowing to choose amongst image Title / Caption / Alt Text / Description and custom text.
* Options to fine-tune the shortcode.
* Have more ideas? We are open to hearing!

== Installation ==

**Automatic installation**

Log in to your WordPress dashboard, navigate to the Plugins menu, and click "Add New". In the search field type "FSM Custom Featured Image Caption", then click "Search Plugins". Once youve found it, click "Install Now". That's all.

**Manual installation**

1. Upload `fsm-custom-featured-image-caption` to the `/wp-content/plugins/` directory.
2. Activate the plugin through the "Plugins" menu in WordPress.
3. Use the plugin in the edit post page.

== Frequently Asked Questions ==

1. Does it work with any theme?
Yes, as long as the theme supports featured images/post thumbnails and uses get_the_post_thumbnail in the code.

2. Does it work with Divi?
Not by default, but you have an option in the Options page to enable the compatibility. Note that this is an experimental feature and that since Divi doesn't offer an easier way to hook the function that shows the featured image, the plugin attempts to replace it. Note that while it will be generaly safe to activate, this may result in incompatibilities or even errors on older Divi versions or if a Divi update changes that particular function. Since it involves a third party, we can't offer assistance if this option doesn't work with your layout or installation

3. I’m using the theme [X], and the caption does not appear/appears in a wrong place / has a different color/background, etc.
Note that the plugin only adds some tags to the image to show the caption, and other than the editable custom CSS for the caption text, it does not add styles to the theme. In most cases, the result would be acceptable. In others, maybe you have to modify your theme or add custom CSS styles (usually in Appearance> Customize) to make the caption and the caption container look as you need.

== Screenshots ==
1. Plugin settings page.
2. Plugin in the Post editing page.

== Changelog ==

= 1.22 =
* Bug fix: restored accidentally removed class "featured"

= 1.21 =
* New experimental setting in options page to make the plugin compatible with Divi themes.

= 1.20 =
* Added general option to allow shortcodes in the caption
* Removed notice message 
* Checked for compatibility with WP 5.6

= 1.19 =
* Added "settings" link in the wordpress plugins list page
* Some texts modified

= 1.18 =
* Added support for Gutenberg
* Code optimization

= 1.17 =
* Improved the detection of the post-id called by post_thumbnail_html to avoid wrong results on themes showing multiple featured images of different posts
* Additional condition to stop processing the featured image if there is no content
* Minor variable corrections

= 1.16 =
* Modified the way the plugin detects if called from a list for widgets and other plugins showing a list of posts from a single page
* Added option to show the caption when the featured image appears on a list

= 1.15 =
* Added a check to return an empty string if there is no caption text instead of an empty figcaption (or custom tag) to prevent weird spacing in some themes.

= 1.14 =
* Corrected identification of single pages where the caption was not appearing
* Renamed the public function names to a more specific ones in order to avoid conflicts with future versions of wordpress or other plugins. It also fixes the naming to follow wordpress conventions.
* Added shortcode to show the featured image and it's caption inside the post contents (experimental)

= 1.13 =
* Minor correction: added check to save_post hook to prevent Notices appearing in some cases while on debug mode

= 1.12 =
* Corrected: The plugin was using figure instead of figcaption in the default parameters
* Allow public functions to be used without parameters (fall back to the defaults)

= 1.11 =
* Corrected: Save the parameters on load to prevent losing them in some circumstances

= 1.10 =
* Settings page to customize styles and output
* Added public functions for advanced users to use inside the template files

= 1.01 =
* Minor text domain / localization changes
* Maintain the parameters after selecting another image

= 1.0 =
* Stable release.
