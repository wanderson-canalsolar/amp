=== Authors List ===
Contributors:      WPKube
Tags:              author, authors, list, grid, authors list, authors grid
Requires at least: 4.7.0
Tested up to:      5.6
Requires PHP:      5.4
Stable tag:        1.2.4

Use a shortcode to display a list or grid of post authors and links to their post archives page.

== Description ==

Easily display a list or grid of post authors and links to their post archives page using a shortcode.

The shortcode is [authors_list] and accepts the following attributes.

* style (1,2,3)
* columns (2,3,4)
* columns_direction ( horizontal, vertical )
* avatar_size (any number, example 500)
* amount (any number, no limit by default)
* show_title (yes,no)
* show_count (yes,no)
* show_bio (yes,no)
* show_link (yes,no)
* orderby ( post_count, post_date, ID, login, nicename, email, url, registered, display_name, first_name, last_name )
* order ( ASC, DESC )
* skip_empty ( yes, no )
* minimum_posts_count ( any number )
* bio_word_trim ( any number, leave empty for no trim )
* only_authors ( yes, no )
* exclude ( user IDs separated by comma, example 1,3,4 )
* include ( user IDs separated by comma, example 1,3,4 )
* roles ( roles separated by comma, example administrator,editor )
* latest_post_after ( for example if set to 7 it will only show authors that have posts published in the past 7 days )
* name_starts_with ( limit to authors whose display name starts with specific characters )
* categories ( category IDs separated by comma, example 1,3,4 )
* terms ( term IDs separate by comma, example 1,3,4 )
* taxonomy ( name of a taxonomy, for example post_tag )
* title_element (the element that wraps the name of the user/author, defaults to div, can be any element, for example h2)

Example usage with attributes: 
<pre>[authors_list style="2" columns="2" amount="4" show_count="no"]</pre>

There are additional attributes for custom content before and after each element:

* before_avatar
* before_title
* before_count
* before_bio
* before_link
* after_avatar
* after_title
* after_count
* after_bio
* after_link

Example usage with attributes:
<pre>[authors_list before_title="My custom content"]</pre>

These before/after attributes also support dynamic user/author meta fields using <pre>{al:INSERT_FIELD_NAME}</pre>

Example usage:
<pre>[authors_list before_title="My first name is {al:first_name}"]</pre>

They also support some dynamic output functionality. Currently supports displaying posts links and social links (Yoast SEO needed):

Example usage for posts links:
<pre>[authors_list after_title="{alf:posts}"]</pre>
<pre>[authors_list after_title="{alf:posts type=plain amount=5}"]</pre>

Example usage for social links:
<pre>[authors_list after_title="{alf:social}"]</pre>

Example usage for roles:
<pre>[authors_list after_title="{alf:role}"]</pre>

Example usage for link (the "to" parameter accepts foollowing values: archive bbpress_profile buddypress_profile):
<pre>[authors_list after_title="{alf:link to=bbpress_profile}"]</pre>

Social Icons SVGs by <a href="https://fontawesome.com/license">FontAwesome</a> are licensed under CC BY 4.0

== Installation ==

- **WordPress Plugins Directory**: Navigate to *Plugins* → *Add New* in the WordPress admin and search “Authors List”. Click *Install* and then *Activate*.
- **Zip Upload**: Navigate to *Plugins* → *Add New* → *Upload Plugin* in the WordPress admin. Browse to the .zip file containing the plugin on your computer and upload, then activate.
- **Manual FTP Upload**: Upload the plugin folder to `/wp-content/plugins/`. Navigate to *Plugins* in the WordPress admin and activate.

== Changelog ==
= 1.2.4 (January 26th, 2021 ) =
* New parameter for {alf:posts} function to show post date above the title. Example after_title="{alf:posts show_date=yes}"
* New parameter for {alf:posts} function to show posts of specific category. Example after_title="{alf:posts show_date=yes categories=5}"

= 1.2.3 (January 10th, 2021 ) =
* New perameter for {alf:social} function to switch social icons to FontAwesome font. Example: after_title="{alf:social type=fontawesome-v5}"
* New dynamic output functon added for showing the user's latest post date. Example: after_title="{alf:latest_post_date}"

= 1.2.2 ( December 25th, 2020 ) =
* Issue with amount of authors shown (when specifically defined) conflicting with other parameters

= 1.2.1 ( December 23rd, 2020 ) =
* The "orderby" parameter now accepts the value "all_post_count" which orders by the count of all post types set in the "post_types" parameter

= 1.2.0 ( October 27th, 2020 ) =
* Parameter "count_text" now supports zero, singular and plural options

= 1.1.9 ( October 17th, 2020 ) =
* New parameter "count_text" that allows changing the text shown for "show_count"

= 1.1.8 ( September 30th, 2020 ) =
* Alt attribute added to avatar image
* New parameter "avatar_meta_key" that allows getting the image URL from user meta instead of using Gravatar

= 1.1.7 ( September 12th, 2020 ) =
* New parameter "title_element" that allows changing the div that wraps the author's name/title to an h2, h3, h4...  (title_element="h2")
* New dynamic output functon added for showing the user's role (after_title="{alf:role}")
* New dynamic output function added for showing a link to post archive, bbpress profile or buddypress profile (after_title="{alf:link to=archive}")

= 1.1.6 ( July 20th, 2020 ) =
* alf:social now shows the personal website URL as well
* New parameter "minimum_posts_count" added to limit the authors shown to only those that have at least that amount of posts
* New parameter "bp_member_types" that allows showing only specific BuddyPress member types

= 1.1.5 ( July 2nd, 2020 ) =
* The "orderby" parameter now accepts the value "comment_count" which order by the comment count what posts made by that author received
* The "link_to" parameter now accepts linking to buddypress profile (link_to="buddypress_profile")
* The "link_to" parameter now accepts linking to custom meta (link_to="meta" link_to_meta_key="meta_key")
* New dynamic output function added for showing follow/unfollow button from BuddyPress Followers plugin (after_title="{alf:buddypress_follow"})

= 1.1.4 ( June 12th, 2020 ) =
* The "orderby" parameter now accepts the value "rand" to have a random order
* New parameter "link_to" added to allow linking to bbPress profile ( link_to="bbpress_profile" )
* New parameter "pagination" added, set to "yes" to enable pagination

= 1.1.3 ( May 30th, 2020 ) =
* New shortcode attribute to allow only showing authors by the first letter (or more letters) of their display name

= 1.1.2 ( April 21st, 2020 ) =
* New shortcode attributes to allow including only authors with posts in specific taxonomy and terms
* New orderby attribute value "post_date" to order authors by the date of their latest post

= 1.1.1 ( April 6th, 2020 ) =
* Fix for skip_empty option
* Added link for "submit support request" in the plugin action links
* Bump up WordPress compatibility to 5.4

= 1.1.0 ( March 18th, 2020 ) =
* Option to include only authors who have posts in specific categories

= 1.0.9 ( February 12th, 2020 ) =
* Improvements for output of dynamic user/author meta fields

= 1.0.8 ( January 28th, 2020 ) =
* Links to social profiles for authors

= 1.0.7 (January 14, 2020) =
* Load CSS only on pages where the shortcode is used
* Option to set to show only authors of specific post types
* Option for a vertical direction column layout (like a newspaper layout)
* Option to show only authors that have posts published today

= 1.0.6 (December 16, 2019) =
* Option to limit visibility by author's post date
* Option to order by first name
* Option to order by last name

= 1.0.5 (December 2, 2019) =
* Option to include specific users by ID
* Option to include specific user roles
* Option to set posts list to be plain ( divs ) instead of unordered list items
* Compatibility with custom avatar plugins
* Responsive fixes

= 1.0.4 (November 23, 2019 ) =
* Option to exclude specific users/authors
* Ability to display posts of the specific author

= 1.0.3 (November 12th, 2019) =
* Option to include all users, not just authors

= 1.0.2 (October 30th, 2019) =
* Added biography words trim option
* Added ability to add custom content before/after each element
* Added ability to display custom field values
* Added option to also show authors that do not have any posts
* Fixed issue where some authors are not shown

= 1.0.1 (October 21st, 2019) =
* Added plugin URI (for "view details" link in the WordPress admin)
* Options to change the authors order
* Fixed issue with layout if images not the same height

= 1.0.0 =
* Initial Release