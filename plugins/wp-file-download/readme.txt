=== WP File Download ===
Contributors: Joomunited
Donate link: 
Tags: file manager, files manager, document, download, folder, repository, directories, media, file category, file management
Requires at least: 4.7
Tested up to: 5.7.2
Requires PHP: 5.6
Stable tag: 5.0.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

WP File Download is a file manager that helps you to categorize, upload & update file easily. UX is AJAX powered, the easiest file manager ever created.


== Description ==

Get the easiest files manager for WordPress. No more need to go in a plugin to manage files and go back in an article or pages to insert files. You just need to: create a category of files, drag and drop files, and insert single file or categories of files in your content. With Wpfd for WordPress you’ll improve the dramatically the default media manager.

On admin part you have the possibility to create infinite number of nested categories and organise order and level with drag’n drop. On frontend part, user will be able to navigate through categories, files, download or preview files and documents.
What about displaying files in a different and nice way? responsive themes are available. Using it with file restriction access? Native WordPress native group restriction can be applied in one click. Working in all editors, backend and frontend. Perfect to work fast. Easy for final users.

[vimeo https://vimeo.com/76802705]

Features of free and pro version are listed above.
More informations available here: http://www.joomunited.com/wordpress-products/wp-file-download

**Features for free version**      
- Add a file in 3 click     
- Manage files from the text editor     
- AJAX file multi upload and reordering     
- Nice icon set by default     
- Display customization without css     
- Replace file in one click     
- Themes are expendable     
- File download count     
- Import existing files from your server     


**Features for PRO version**   
- 6 month support     
- Categories level unlimited     
- Number of categories unlimited     
- Nested & AJAX categories ordering     
- Navigate in AJAX through categories     


== Installation ==

1. Upload `WP File Download` to the `/wp-content/plugins/` directory or browse and upload zip file from WordPress standard installer
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Just open a content (page, article or any WYSIWYG editor) and use the "WP File Download" button to add and edit files.
4. Files can be managed from the left menu in admin or using the buttons "WP File Download in WYSIWYG editor

== Frequently asked questions ==

= How do I add files in my WordPress content? =

Just open you content and you'll a button named "WP File Download" at the top left of your editor.

= Can I put files into categories? =

Yes you can classified files into categories. 
In pro version nested categories are available with drag'n drop feature to order them.

== Screenshots ==

1. Main interface: left part the categories, center part the files, right part the parameters
2. Add and manage categories
3. Order categories, level and files with drag'n drop
4. Default theme with icon set
5. Insert files or category (and sub categories) of files in article
6. GDD theme, Navigate through categories in AJAX
7. Manage single file
8. Easy parameters

== Changelog ==

= 5.0.4 =
 * Fix : Cloud files not showing in backend.

= 5.0.3 =
 * Fix : Conflict with the Divi Builder stylesheet.
 * Fix : The preview button isn't showing in front-end.
 * Fix : Hide cloud categories when the Addon plugin deactivated.
 * Fix : The previewer light-box stuck in some case.

= 5.0.2 =
 * Fix : Table theme category navigation not loading
 * Fix : GGD theme sidebar not loading
 * Fix : Search limit not working

= 5.0.1 =
 * Fix : Check download/preview permission for user on multiple roles
 * Fix : Handlebars error when option display sub-categories disabled.
 * Fix : Wrong file path on importing when wordpress installed in sub-folder.
 * Fix : PHP 8 compatibility patch for LightnCandy class.
 * Fix : Support formal languages on translate module.
 * Fix : Daterange picker layout on search page.

= 5.0.0 =
 * Add : Possibility to export WP File Download files and categories from one server to another
 * Add : Possibility to import WP File Download files and categories from another server
 * Add : Possibility to import local server files and folders into WP File Download
 * Add : Possibility to import files and categories from the 3rd party plugin WP Download Manager
 * Add : Possibility to set an expiration date for WP File Download file
 * Add : Possibility to define Download files action per user role
 * Add : Possibility to define Preview files action per user role
 * Add : Add xsendfile option to improve performances
 * Fix : PHPMailer, SMTP, phpmailerException classes do not exist

= 4.9.10 =
 * Fix : Conflict style with the Heading Title block in WP Bakery Page Builder.
 * Fix : Wrong cloud category id type in shortcode generator.

= 4.9.9 =
 * Add : Implement JoomUnited document preview for cloud documents files
 * Add : Increase the number of pages in Joomunited document preview to 3
 * Fix : GGD theme error when open file in popup mode

= 4.9.8 =
 * Fix : Wrong check edit permission for categories.
 * Fix : Missing WpfdBase class when generate preview files in some case.
 * Fix : All categories shortcode with folder tree now expand by default.
 * Fix : All categories shortcode with pagination on first page init.
 * Fix : Additional notification Email address when download trigger.
 * Fix : HTML template for category on front-end not correct when insert multiple category in same place with difference settings.
 * Fix : Front-end categories order by Ordering not correct for the first category.

= 4.9.7 =
 * Fix : Divi modules conflict with other plugin.
 * Fix : Preview generator now support PPTX files.

= 4.9.6 =
 * Fix : Add missing RTF icon
 * Fix : Ajax error when set title for category
 * Fix : Wrong preview generated file path on Windows
 * Fix : WP File Download Avada builder element not render on front-end

= 4.9.5 =
 * Add : WP File Download single file module for Avada page builder
 * Add : WP File Download category module for Avada page builder
 * Add : WP File Download search module for Avada page builder

= 4.9.4 =
 * Add : WP File Download single file module for WPBakery page builder
 * Add : WP File Download category module for WPBakery page builder
 * Add : WP File Download search module for WPBakery page builder
 * Fix : Search tag not working in Japanese or that kind of language
 * Fix : Wrong search when exclude category is empty
 * Fix : Wrong pagination initialization when files loaded

= 4.9.3 =
 * Fix : File icons are not properly loaded in the WP File Download category module in Divi edition mode
 * Fix : WP File Download single file does not work in some case in Divi page builder
 * Fix : WP File Download single file widget does not work with Wordpress version 5.6.0 in Elementor page builder
 * Fix : WP File Download category widget does not work with Wordpress version 5.6.0 in Elementor page builder
 * Fix : Conflict with Polylang plugin
 * Fix : Wrong handlename in icon builder on translated site

= 4.9.2 =
 * Add : WP File Download single file module for Divi page builder
 * Add : WP File Download category module for Divi page builder
 * Add : WP File Download search module for Divi page builder

= 4.9.1 =
 * Fix : Category tree in front-end not display

= 4.9.0 =
 * Add : Introducing JoomUnited file previewer, a fast and stable document viewer
 * Add : Select either JoomUnited or Google file previewer
 * Add : File format handled: ai,csv,doc,docx,html,json,odp,ods,pdf,ppt,rtf,sketch,xd,xls,xlsx,xml
 * Add : Fallback to the Google previewer in case a file preview failed
 * Add : Fallback to the Google previewer in case preview file format is not available
 * Add : Category status inherited: sub-categories will automatically have parent category access right
 * Add : Category status inherited: default status Inherited is applied to all new categories

= 4.8.4 =
 * Fix : Wrong multiple categories file title when search on front-end
 * Fix : PHPMailer class has been moved to wp-includes/PHPMailer in WP 5.5.3

= 4.8.3 =
 * Add : New Edit permissions settings on User Roles configuration
 * Fix : Icon set not display on all categories shortcode
 * Fix : Elementor page builder hang on some case
 * Fix : Activate plugin fail on some case
 * Fix : Remove php renderer file on disk

= 4.8.2 =
 * Add : WP File Download single file widget for Elementor page builder
 * Add : WP File Download category widget for Elementor page builder
 * Add : WP File Download search widget for Elementor page builder

= 4.8.1 =
 * Fix : Category shortcode for empty folder not working
 * Fix : Search shortcode generator not update on change settings
 * Fix : Icon Builder: SVG Frame background not remove on selection other frame
 * Fix : Missing default icon for additional extension when using SVG set on single file

= 4.8.0 =
 * Add : New SVG icon builder system
 * Add : New PNG icon builder system
 * Add : Possibility to everide each svg and PNG icon
 * Add : Possibility to edit svg icon layers with CSS configration
 * Add : Single file download icon customization
 * Add : Single file download layout customization [Breaking change -- Single file display will change and css overrides made will not work anymore]
 * Add : Setting to define a default icon set for the whole website
 * Fix : Plain text search not working
 * Fix : Show empty category setting not applied in some case
 * Fix : Search results ordering
 * Fix : Preview token now generate for login user only

= 4.7.15 =
 * Add : WordPress 5.5 compatibility
 * Fix : Pagination issue on wpfd_category shortcode to show all files
 * Fix : Shortcode generator wrong category id for cloud categories
 * Fix : Support for WooCommerce description
 * Fix : Conflict with some theme search query
 * Fix : Set max-width for files container when enable folder tree

= 4.7.14 =
 * Fix : Files in multiple categories not remove when delete in target category
 * Fix : Themes pagination issue
 * Fix : Render HTML in file title in front-end
 * Fix : Wrong files ordering in front-end
 * Fix : Plain text search performance for long terms
 * Fix : Remote file size N/A for zero file size
 * Fix : File health check fail for WP Rest API in some cases

= 4.7.13 =
 * Fix : File name in UTF-8 when download
 * Fix : Remove a dot from category name in Gutenberg Categories block
 * Fix : Multiple categories file in sub-categories not found when search
 * Fix : Clear float after pagination in front-end themes
 * Fix : Remove duplicate elements id on same page
 * Fix : Widget conflict in Elementor Page Builder
 * Fix : Wrong CSS selector when clone table/ggd themes
 * Fix : URL encode on SEO URL
 * Fix : Show categories tree for all categories shortcode

= 4.7.12 =
 * Fix : Single file preview
 * Fix : Hide sub-categories in table theme not correct in table theme
 * Fix : Multiple receiver email notification problem
 * Fix : Wrong category load when reload at multiple categories in same page

= 4.7.11 =
 * Fix : Minimum php version now is 5.6
 * Fix : mb_convert_encoding warning
 * Fix : Duplicate home url in custom icon link
 * Fix : Style issue for Default theme
 * Fix : Change token lifetime to 15 minutes
 * Fix : Warning on category shortcode

= 4.7.10 =
 * Fix : User roles settings not saving
 * Fix : Some config form not showing when conflict with other plugins
 * Fix : Tags display errors
 * Fix : Wrong category id in download url of files in multiple categories

= 4.7.9 =
 * Fix : Front-end: Child categories not display after a category clicked

= 4.7.8 =
 * Fix : wpfd_category shortcode supports show_categories parameter when using on show all categories
 * Fix : Possibility to translate some text in search form
 * Fix : Search form behavior on categories selection
 * Fix : Clone table theme problem
 * Fix : Remove space before themes layout

= 4.7.7 =
 * Fix : Update to working with Addon version 4.4.1

= 4.7.6 =
 * Add : Compatibility with new addon version - OneDrive Business integration
 * Fix : Table Theme: Pagination problem when put category in hidden tab
 * Fix : Search Form: Tag filter reselect not correct on refresh page

= 4.7.5 =
 * Fix : Search form: Category sandwich button not clickable in some case.
 * Fix : Plain text search: Problem with minus (-) characters in keyword string.
 * Fix : Missing mediaTable jquery library on Beaver Builder.
 * Fix : Add wpfd_search_post_types filter

= 4.7.4 =
 * Add : New categories themes layout
 * Fix : Translatable search form text

= 4.7.3 =
 * Fix : Search page not working when using Enter in Safari, IE
 * Fix : Hide empty category: Now checking Multiple files too
 * Fix : Add scrollbar to files importer
 * Fix : Check mbstring PHP extension when install
 * Fix : Download all button not showing on first load

= 4.7.2 =
 * Add : Support for WooCommerce Addon (require Cloud Addon version v4.3.0)
 * Fix : New UX for search widget layout

= 4.7.1 =
 * Fix : Translation sharing issue in some browsers

= 4.7.0 =
 * Add : New design for file search engine
 * Add : Search engine: root category selection with quick filtering
 * Add : Search engine: file tag suggestion based on existing tag only
 * Add : Search engine: multiple calandar to select date range
 * Add : File search results with column display selection
 * Add : Search engine: Better HTML without titles and specific class
 * Fix : Responsive issue in file search results
 * Fix : Category file tree in admin better responsive display

= 4.6.13 =
 * Fix : Download/preview PDF file problem

= 4.6.12 =
 * Fix : Categories tree can not clickable on touch devices
 * Fix : Front-end pagination issue 
 * Fix : Statistics now count download only
 * Fix : Download problem on server have Gzip enabled

= 4.6.11 =
 * Fix : Responsive on mobile on page using Divi builder
 * Fix : Ordering problem on pagination
 * Fix : Publish date is not change on save

= 4.6.10 =
 * Add : Add option Display empty folders in frontend
 * Fix : Permission on download selected files
 * Fix : Date not validate when save file params
 * Fix : Front-end upload form into tabs not working
 * Fix : Support preview m4a audio file type
 * Fix : Possibility to use gtag.js to send Google Analytics event

= 4.6.9 =
 * Fix : Date range picker not working in statistics page
 * Fix : Remove arrow icon before non-children category on frontend
 * Fix : Add filter wpfd_search_tags_relation

= 4.6.8 =
 * Fix : Reponsive for theme in front-end
 * Fix : Pagination execution wrong category
 * Fix : Upload shortcode generator
 * Fix : Document parser remove too many words on PDF file
 * Add : Add blocks preview image

= 4.6.7 =
 * Fix : Responsive on plugin admin screen
 * Fix : Wrong front-end sub categories ordering
 * Fix : Order files by version on front

= 4.6.6 =
 * Fix : Update preview close notification text
 * Fix : Wrong category id for cloud category in search shortcode generator
 * Fix : Update to working with WP File Download Cloud Addon 4.2.2

= 4.6.5 =
 * Fix : Theme breadcrumb missing top category name when hide Category name
 * Fix : Table Theme: Hide tool box when Stylize menu disabled
 * Fix : Defaut Theme: Using flex wrap for files box
 * Fix : Table Theme: Download header missing download col when download link disabled

= 4.6.4 =
 * Fix : Order for categories tree in themes
 * Fix : Prevent PHP Warning when headers already send
 * Fix : Preview in new tab not working when Google Analytics enabled
 * Fix : Frontend configuration tab saving not working
 * Fix : WP File Download button in front-end editor
 * Fix : Custom icon path not correct when wp-content path changed by other plugin
 * Fix : Missing translation page title for admin menu
 * Fix : New category created not clickable
 * Fix : Update file size for remote file when update the URL
 * Fix : Statistics ordering
 * Fix : Reponsive for default theme
 * Fix : Missing option show download link for tree theme when use as global theme
 * Fix : Possibility to translate string in Gutenberg block
 * Fix : Plain text search: Possibility to index and search file description
 * Fix : Minimum require WP File Download Cloud Addon version 4.2.0

= 4.6.3 =
 * Fix : Can not download old uploaded files

= 4.6.2 =
 * Fix : Wrong file path for download file

= 4.6.1 =
 * Fix : Download button not working when option Tracking by GA enabled
 * Fix : Statistics not update when plugin updated

= 4.6.0 =
 * Add : File download statistics design
 * Add : Possibility to export statistics table to CSV
 * Add : Possibility to track user downloads (option needs to be turned On)
 * Add : Gutenberg blocks file and category now display names
 * Fix : Split Main settings to be more comfortable with Admin, Frontend and Statistics
 * Fix : Add selected category/file name in Gutenberg blocks
 * Fix : Change single file download icon
 * Fix : New tab opened when preview in lightbox

= 4.5.8 =
 * Fix : Translate calendar in search form
 * Fix : Search index check table exists when prefix have uppercase character
 * Fix : Remove unused files
 * Fix : Google Analytics push event update
 * Fix : RTL style for themes
 * Fix : Action buttons in RTL can not click
 * Fix : Conflict with plugin Membership Pro

= 4.5.7 =
 * Fix : Update PDFParser library for security fix
 * Fix : Gutenberg single file block param

= 4.5.6 =
 * Fix : Fix Jutranslation url
 * Fix : Multiple categories in one page collapsed the first one only
 * Fix : Trash and edit icon on modal to insert categories/files

= 4.5.5 =
 * Fix : Preview in lightbox not working in single file display
 * Fix : Reindex file on new version upload
 * Fix : Add action wpfd_before_index_remove and wpfd_file_indexed

= 4.5.4 =
 * Add : Possibility to exclude category from search
 * Add : Keep version and description when Copy/Move files on cloud
 * Fix : Possibility to translate tooltip and help field
 * Fix : Possibility to translate in main javascript file
 * Fix : Hide text on copy-cut-paste button on small screen
 * Fix : Multiple login form on upload shortcode
 * Fix : Single quote not display correct on title when load via handlerbars
 * Fix : Force some column in table to nowrap
 * Fix : Some style tweak

= 4.5.3 =
 * Add : Possibility to search only on one category
 * Add : Display login form when user not logged in on upload shortcode
 * Fix : Wrong file custon icon url on popup download
 * Fix : File name in shortcode missing when name params not provided
 * Fix : Tinymce problem in description field
 * Fix : Complicity with ACF
 * Fix : Possibility to translate file weight unit
 * Fix : Add more space between edit icon and scroll bar on left categories panel

= 4.5.2 =
 * Fix : Search pagination limit
 * Fix : Remove category from multi-categories problem
 * Fix : Missing file info on copy file
 * Fix : Possibility to translate file size measure
 * Fix : Automatic translate calendar field on search widget

= 4.5.1 =
 * Add : Add option new file category position
 * Fix : Download selected file in tree theme when page have more than one category
 * Fix : Possibility to translate download selected phrase
 * Fix : Update handlebars to 4.1.0
 * Fix : Scroll files list when sorting in backend
 * Fix : Multi-category on frontend for cloud file in a local category
 * Fix : Wrong app instance when search files in backend

= 4.5.0 =
 * Add : Automatic clean junks files to save disk space
 * Add : Option to set statistics life time
 * Add : Download selected file on frontend
 * Fix : WP File Download button not working on some builder at frontend
 * Fix : File permission for single user on all category shortcode
 * Fix : Sometime got 404 page on google index
 * Fix : Hide category shortcode usage text when click hide field
 * Fix : Search sub-category on multi-categories form
 * Fix : Using local Material Icons font
 * Fix : Increase sub-category level from 8 to 16

= 4.4.5 =
 * Fix : Improve some admin style
 * Fix : User perrmission on delete category
 * Fix : Drag to sort categories issue
 * Fix : Configuration style not load on other language
 * Fix : Conflict with new Divi builder

= 4.4.4 =
 * Fix : Category list in Block Editor conflict with some theme
 * Fix : Category search and select previous state in Block Editor
 * Fix : Download notification email not send to additional email
 * Fix : Preview video can not be played back when click on stop
 * Fix : Conflict switcher style with some theme
 * Fix : Column display state for admin table theme
 * Fix : Missing Show category title option for default and GGD theme
 * Fix : Single file style in front-end

= 4.4.3 =
 * Add : add WP File Download blocks for Gutenberg Block Editor
 * Fix : typo in email notification tab name
 * Fix : style for single file display in frontend
 * Fix : fatal error when using wp_doing_ajax() on wordpress < 4.7

= 4.4.2 =
 * Fix : missing toggle right column button icon
 * Fix : insert category/file button style
 * Fix : adjust scroll speed in file manager
 * Fix : insert remote file with space in file url

= 4.4.1 =
 * Fix : File order in frontend
 * Fix : remove admin notice on page belong to plugin
 * Fix : left menu padding
 * Fix : wordpress submenu now showing on hover in plugin pages
 * Fix : setup wizard

= 4.4 =
 * Add : New UX for plugin's file manager on backend
 * Add : New UX for plugin's configuration page
 * Add : Introduce Alternative - Larger theme for files list on backend
 * Add : Remember latest category open/close state on backend
 * Add : New gradients icons set for files on backend
 * Add : Add filter to allow setup default visibility for new category
 * Fix : First load ordering on frontend
 * Fix : Order by title with nature algorithm
 * Fix : Conflict with Relevanssi plugin

= 4.3.29 =
 * Fix : Download all button not displayed on first load
 * Fix : Conflict with Multisite Robotstxt Manager Plugin
 * Fix : wpfd_preview_url filter
 * Fix : Multiple single file shortcode not working
 * Fix : wpfd_category shortcode for all category not working
 * Fix : Sorting on pagination with nature order on title

= 4.3.28 =
 * Fix : Multi single file shortcode not working
 * Fix : Wrong asset url on clone themes
 * Fix : Pagination on frontend
 * Fix : New clone theme now store in /wp-content/wp-file-download/themes

= 4.3.27 =
 * Fix : Search form output returned too soon
 * Fix : Wrong files sorting action callback
 * Fix : Conflict with Gutengerg on saving post/page

= 4.3.26 =
 * Fix : Multi category using table theme error
 * Fix : File size on frontend different with backend

= 4.3.25 =
 * Add : Add actions and filters for developers
 * Add : Add new location for custom theme at /wp-content/wp-file-download/wpfd-themes
 * Add : Add support custom templates at /wp-content/wp-file-download/templates
 * Fix : Update framework to 1.0.5
 * Fix : WpfdBase error
 * Fix : Selected tags not checked after reload search form

= 4.3.24 =
 * Add : Remove download file link extension
 * Fix : WP File Download modal not open in Elementor
 * Fix : Upload fail on server have no limit upload file size
 * Fix : Tag order in checkbox mode
 * Fix : Duplicated hash in pagigation url
 * Fix : Scroll to top category block when click pagination
 * Fix : Back button not showing when not show category title in Default, GGD, Table theme

= 4.3.23 =
 * Add : Maximum file versions and purge all old versions option
 * Fix : Remove framework from themes
 * Fix : Front search not working
 * Fix : Update requirements class

= 4.3.22 =
 * Fix : Saving multi category
 * Fix : Change category order performance
 * Fix : Abort ajax in multi category/file click
 * Fix : Search by tags in checkbox mode
 * Fix : Open in new tab in table theme for preview button
 * Fix : Order by created date on search results
 * Fix : Conflict with Go7 Pricing Table plugin
 * Fix : Index file title with underscore character

= 4.3.21 =
 * Fix : Wrong title space on edit
 * Fix : Override language not working
 * Fix : Include to main query on search only
 * Fix : Multi hash when pagination on themes

= 4.3.20 =
 * Fix : Plain text search indexer
 * Fix : Loading modal after click WP File Download button on Editor
 * Fix : Single user permission check
 * Fix : File icon not showing on uppercase extension
 * Fix : Wrong URL when click on subcategory (frontend)

= 4.3.19 =
 * Fix : Warning on frontend
 * Fix : Sorting files on search results
 * Fix : Disable change date field in search form when scroll mouse over
 * Fix : Table theme css
 * Fix : Error on clone table theme
 * Fix : Calendar button over the overlay
 * Fix : Click on backdrop to cancel prompt
 * Fix : Upload on deleted category
 * Fix : Change download button background/text color not working
 * Fix : Download file not working in backend on firefox

= 4.3.18 =
 * Fix : Redirect loop on frontend download after save file
 * Fix : Pagination for multi category on frontend
 * Fix : Rewrite source code to meet Coding Standard
 * Fix : Require minimum cloud addon version 4.0.6

= 4.3.17 =
 * Fix : Additional Email notification on file changes not applied
 * Fix : Corrupt PDF file on download
 * Fix : Update PdfParser library
 * Fix : Special character cutoff in file title
 * Fix : Using file date from GMT time
 * Add : Check some requirements before activate plugin

= 4.3.16 =
 * Fix : Error on statistics page
 * Fix : Table layout theme
 * Fix : Search results list design enhancement
 * Fix : Front view not working after update
 * Fix : Right column in backend springy when select file
 * Fix : File title not fully displayed when crop title is disabled

= 4.3.15 =
 * Fix : Backend category tree
 * Fix : Backend category's file count
 * Fix : Some PHP warnings
 * Fix : Create table theme error on install

= 4.3.14 =
 * Fix : Update token creator
 * Fix : Wrong category order in left tree on front
 * Fix : Nestable2 problem in firefox
 * Fix : Wrong icon path when cloning theme

= 4.3.13 =
 * Fix : Search on widget with space character
 * Fix : Add version on load scripts and styles to avoid cahche problem
 * Fix : Add animate FadeOut when remove category with success
 * Add : Replace with nestable 2 to handle tactile devices
 * Fix : Search multiple tags at once

= 4.3.12 =
 * Fix : Upload on drag on frontend
 * Fix : Root folder tree not opened on first load
 * Fix : Scrollbar on touch device
 * Fix : Download large file function, support stream video and preview in abetter way

= 4.3.11 =
 * Add : Optimization for folder tree navigationfast loading
 * Fix : Download All button not loaded
 * Fix : Ordering files not applied
 * Fix : Allow upload files with similar file name

= 4.3.10 =
 * Fix : Download large file on category grouped download
 * Fix : Remove !important properties to allow CSS override
 * Fix : Group & fix UX category params
 * Fix : Change wpColorPicker to minicolors

= 4.3.9 =
 * Fix : Mail SMTP configuration broken
 * Fix : Bypass warning on set_time_limit is disabled
 * Fix : Include Ref file in download category
 * Fix : Upload check mimetype
 * Fix : Search condition
 * Fix : User Role name

= 4.3.8 =
 * Fix : File duplicated on drag and drop upload
 * Fix : Deleting Google Drive categories fails

= 4.3.7 =
 * Fix : Rewrite date fix on edit title, descriptions
 * Fix : Date get wrong format on save
 * Fix : Download large file return memory error

= 4.3.6 =
 * Fix : Mime type always return application/octet-stream
 * Fix : Missing AJAX URL in modal when polylang is actived
 * Fix : Date setting for files
 * Fix : Multi categories deletion
 * Fix : Single shortcode not working

= 4.3.5 =
 * Add : Include files in native WordPress search
 * Fix : For servers that do not have finfo functions
 * Fix : Navigation in table theme
 * Fix : Search ref files include in children category (filter post_title included)

= 4.3.4 =
 * Add : Possibility to run full document content index for plain text search (settings)
 * Fix : Multi upload form in frontend
 * Fix : File version change on upload
 * Fix : Full text search
 * Fix : Check mime type when uploading files
 * Fix : Navigation false in table theme

= 4.3.3 =
 * Fix : Move custom tplsingle.php file in wp-content/uploads/wpfd-themes
 * Fix : Wrong file name in notify email when upload new file
 * Fix : New category creation broken

= 4.3.2 =
 * Fix : Remote file URL not working
 * Fix : Display wrong columns in table theme
 * Fix : Multiple category issue when saving category parameters
 * Fix : JS error in the download statistics page
 * Fix : Upload load file on frontend don't work

= 4.3.1 =
 * Fix : JS error in the plugin configuration page
 * Fix : PHP 5.3 returns an error on install
 * Fix : Custom theme from configuration does not apply

= 4.3.0 =
 * Add : Full code reformating  for better performance and code comments
 * Add : Using PHPCS to make standard definitions
 * Fix : Upload large file size to server
 * Fix : Problem with automatic image crop
 * Fix : Conflict related to AJAX URL (with 3rd party plugins)
 * Fix : PHP 5.2 returns an error
 * Fix : Widgets sidebar not work in wordpress version 4.9

= 4.2.3 =
 * Fix : Bootbox conflict when used in other plugins
 * Fix : Files list shortcode
 * Fix : Language constants

= 4.2.2 =
 * Fix : Display issues in table theme
 * Fix : Multiple file category not updated on file move and delete
 * Fix : Hide download all link if there isn't any file in the category

= 4.2.1 =
 * Fix : Folder tree not properly working with multiple instance of categories
 * Fix : Download all don't work when addon plugin is not activated
 * Fix : Custom icon in sub categories display

= 4.2.0 =
 * Add : File multi-category: Load file in multiple categories, keep one original version
 * Add : New setting: Shortcode generator to load recent files by categories and custom ordering
 * Add : New setting: Shortcode generator to load recent files by type, title or description
 * Add : New setting: Shortcode generator to load recent files by size, version, date or hits
 * Add : Frontend: Add a button to download all the files from a category as a .zip
 * Add : New setting: Display file count in categories on admin
 * Add : In admin, add a file direct link and a quick copy link
 * Add : Close cross on the preview window
 * Add : Settings: Group the 3 cloud configuration tabs

= 4.1.8 =
 * Fix : Conflict with WPML plugin
 * Fix : Import files issue if filename contain special charater
 * Fix : Wrong file item in search results
 * Fix : Custom icon don't display in search results

= 4.1.7 =
 * Add : Compatibility with new addon version - OneDrive integration
 * Fix : Multiple upload form in content
 * Fix : Single file shortcode

= 4.1.6 =
 * Fix : Issue on upgrading from light version to full version

= 4.1.5 =
 * Fix : Duplicate the display of subfolders/files when click on breadcrumb navigation
 * Fix : Missing text domain for some words
 * Fix : Security issues

= 4.1.4 =
 * Fix : DIVI Builder compatibility enhancement
 * Fix : Open media file in new tab does not work in some case
 * Fix : Check access in files and categories listing
 * Fix : Can't select small image file as custom icon

= 4.1.3 =
 * Fix : Update the updater for WordPress 4.8

= 4.1.2 =
 * Fix : Empty space on bottom of page (Firefox)
 * Fix : Add mail function in configuration
 * Fix : Dropbox filename is wrong after file download in some languages
 * Fix : Wrong file type restriction on file update upload
 * Fix : Small issue when loading categories in tree theme

= 4.1.1 =
 * Fix : File settings display issue
 * Fix : CSS display of open PDF in new browser tab
 * Fix : Upload bootbox alert
 * Fix : Select category owner

= 4.1.0 =
 * Add : Email notification system: Possibility to notify users on file upload, remove, edit, download
 * Add : Notification admin user, to custom Email and file owner
 * Add : Tag for custom notification Email content: File name, file category, website URL, username
 * Add : Custom icon on files with automatic thumbnail
 * Add : File access, add an option to add multiple user access to a file
 * Add : Upload form to upload files in a predefined category
 * Add : On themes that opens popup to download files possibility to disable it

= 4.0.5 =
 * Fix : Download limitation security issue
 * Fix : Clone table theme issue
 * Fix : Open image file in search results
 * Fix : Back button not displayed on GDD theme

= 4.0.4 =
 * Fix : Missing file title on search results
 * Fix : Color setting don't apply on single file
 * Fix : Remote file adding issue

= 4.0.3 =
 * Fix : Conflict with Visual Composer theme
 * Fix : User role apply issue
 * Fix : Save file setting issue in admin
 * Fix : Open PDF link in new tab

= 4.0.2 =
 * Fix : CSS admin title is broken
 * Fix : CSS for mobile: lightbox to download and file tree for iphones
 * Fix : CSS in lightbox for xlsx files

= 4.0.1 =
 * Fix : Conflict issue with Yoast - XML sitemap
 * Fix : CSS improvement related to new themes and mobile views

= 4.0.0 =
 * Add : Admin UX: Add notification system on actions: file and categories add, edit, delete, order
 * Add : Admin UX: Panels takes more width and the the left column can be resized
 * Add : Admin UX: Search/filter engine for files
 * Add : New Copy/Cut/Paste/Uncheck buttons
 * Add : Possibility to remove several files at once whith Delete button
 * Add : Hide columns automatically when the screen goes smaller
 * Add : Frontend new design design for all themes and unique lightbox style
 * Add : File title automatic crop function (can be defined in each theme)
 * Fix : Delete file current version and file old versions

= 3.7.10 =
 * Fix : Missing loading spinner when using category short code
 * Fix : Menu link to category not working
 * Fix : Change the download method without redirect (SEO purpose)

= 3.7.9 =
 * Fix : Use default en_US language

= 3.7.8 =
 * Fix : Allow saving an empty translation override file

= 3.7.7 =
 * Add : Builtin translation tool improvements

= 3.7.6 =
 * Fix : Filename doesn't change when editing file title
 * Fix : Back to: link not working on iphone
 * Fix : Remove extra <br> tag in the download link
 * Fix : Move the cloned theme to the wp-content/uploads/wpfd-themes folder
 * Fix : Translation overwritten on update

= 3.7.5 =
 * Fix : Php 5.3 issue
 * Fix : Language override not working if language file not installed

= 3.7.4 =
 * Fix : Error on wp multisite

= 3.7.3 =
 * Add : Built in JU Translation tool including addons

= 3.7.2 =
 * Add : Built in JU Translation tool

= 3.7.1 =
 * Add : Change default pagination number to 100
 * Fix : Issue when category name has special character such as quote
 * Fix : Select multiple files on Mac OS

= 3.7.0 =
 * Add : Generate URL per category of files on frontend
 * Add : Re-open this category when the plugin is called another time
 * Add : Add shortcode for single file
 * Add : Pagination for file listing as a setting (except for tree theme)
 * Add : Open PDF in new browser tab as preview
 * Add : CSS style on admin: more compact category list display
 * Fix : Responsive display when left tree navigation is called on screens <640px
 * Fix : Remove unused or obsolete resources to get a smaller extension package

= 3.6.4 =
 * Add : Support social locker for addon
 * Fix : Category not applied on specific server configuration
 * Fix : Download URL broken on specific server configuration

= 3.6.3 =
 * Fix : User as category owner
 * Fix : Wrong automatic file title on upload
 * Fix : Wrong category title on search page
 * Fix : Clone table theme
 * Fix : Loader always displayed on table theme

= 3.6.2 =
 * Add : Optimize and clean code
 * Fix : Fix copy/cut/paste when addon is not installed
 * Fix : Choosing elements to display does not work properly
 * Fix : Cloned a Theme base on default one does not work properly

= 3.6.1 =
 * Fix : loading image  always show at footer page 

= 3.6.0 =
 * Add : Possibility to copy/cut/paste files
 * Add : Possibility to add an existing user as a category owner
 * Add : Add a loader on frontend when you navigate through categories

= 3.5.5 =
 * Fix : Show gracefull error on installation error

= 3.5.4 =
 * Fix : Settings not applied on public category

= 3.5.3 =
 * Add : WordPress 4.6 compatibility
 * Fix : Permission to view category
 * Fix : Access category for single users

= 3.5.2 =
 * Add : Shortcode [wpfd_category id='xxx'] to display files of a category
 * Fix : Missing js and css files for search widget
 * Fix : Single user access per category is not saved when disable Theme per categories

= 3.5.1 =
 * Fix : Search query broken in some cases
 * Fix : Navigation broken on table theme

= 3.5.0 =
 * Add : Add Download statistics view: graph and details
 * Add : Download file statistics by category
 * Add : Download file statistics by file
 * Add : Download file statistics by date range

= 3.4.0 =
 * Add : Possibility to duplicate an existing theme and customize it
 * Add : Custom theme won't move on plugin update
 * Add : Fall back to the custom theme if new theme is removed
 * Add : Use WP File Download with page builder: ACF
 * Add : Use WP File Download with page builder: Beaver Builder
 * Add : Use WP File Download with page builder: DIVI Builder
 * Add : Use WP File Download with page builder: Site Origine
 * Add : Use WP File Download with page builder: Themify builder
 * Add : Use WP File Download with page builder: Live composer
 * Fix : Special characters of file title removed on upload

= 3.3.1 =
 * Fix : Not displayed list of files at frontend in some case

= 3.3.0 =
 * Add : Possibility to restrict the display of a file/category per WordPress user
 * Add : Allow Delete own category action in the permission Edit own category
 * Add : WYSIWYG Editor in file description
 * Add : Generate an URL on search query (URL can be shared)
 * Add : Publication state/date for single file

= 3.2.2 =
 * Fix : File title was changed after uploaded
 * Fix : Conflict with Visual Composer plugin
 * Fix : Conflict issue when using custom role plugin

= 3.2.1 =
 * Fix : Warning in WP 3.5 and php 5.3
 * Fix : Small search engine issue

= 3.2.0 =
 * Add : Work with new addon version - Dropbox integration

= 3.1.1 =
 * Fix : wrong text box class in config
 * Fix : some css issue

= 3.1.0 =
 * Add : Advanced search and filtering
 * Add : Tag management
 * Add : Plain text search

= 3.0.1 =
 * Fix : Cloud addon single file edition fix

= 3.0.0 =
 * Add : Work with cloud addon - Google Drive double way sync
 * Fix : Private access for single file
 * Fix : Theme issue when Show folder tree option is ON

= 2.5.1 =
 * Add : Add a tracking for file preview
 * Fix : Add file extension to front download link

= 2.5.0 =
 * Add : File versionning with one click restore
 * Add : Google analytics downloawd tracking using "events"
 * Add : Default root URL for all downloads
 * Add : SEO URL for files with category and file names
 * Fix : jpg, png by default in the previewer configuration

= 2.4.1 =
 * Add : .pot file for translators
 * Fix : File preview reload

= 2.4.0 =
 * Add : File preview feature based on Google preview for docs
 * Add : Select file format you want to preview in parameter
 * Add : Implement MP3 and video player
 * Add : .pot file for translators

= 2.3.0 =
 * Add : Remote download function, to be activated as a global option
 * Fix : Get file size and in some cases

= 2.2.0 =
 * Add : Import server files on WP File download categories
 * Fix : WP Media folder JS conflict

= 2.1.0 =
 * Add : WP File Download menus to load categories of files
 * Add : Define actions per user role (edit own category of files...)
 * Add : Implement JoomUnited automatic updater
 * Fix : Remove all old icons and avoid duplicate ones in each theme folders

= 2.0.1 =
 * Add : Single image folder for all file icons
 * Add : File icon optimization using www.imagerecycle.com
 * Fix : Single file not displayed well

= 2.0.0 =
 * Add : New admin file management design with columns filters
 * Add : Admin left panel retractable
 * Add : Order files in categories on column title click or using dropdown
 * Add : Date update file ordering
 * Add : Parameter to close/open categories on admin
 * Add : Date format parameter
 * Add : Set default theme and default parameters
 * Add : Update default theme with folder navigation

= 1.2.1 =
 * Add : Better icons set for table and GDD
 * Fix : Change icon width from 60px to 50px for table theme
 * Fix : Remove the bootstrap style on right column for save button
 * Fix : JS conflict with WP Media Folder

= 1.2.0 =
 * Add :  Update default icon set
 * Add : Add theme per category
 * Add : Single file insertion design change
 * Add : Add breadcrumb navigation
 * Add : Folder tree navigation
 * Add : Add some theme config with color pickers
 * Add : Add loader in tree theme
 * Add : Add CSS admin enhancement

= 1.1.0 =
 * Add : Switch to WordPress standard custom post type and stadard taxonomy for categories
 * Add : Language files migration to WordPess standard .po/.mo files

= 1.0.3 & 1.0.4 =
 * Add : Enhanced uninstall process
 * Add : Ability to change the upload file max size
 * Fix : Admin image not showing in editor

= 1.0.2 =
 * Fix : Firefox JS problem
 * Fix : Saving parameter problem

= 1.0.1 =
 * Fix : Improve language usage

= 1.0.0 =
 * Add : Initial release



== Upgrade notice ==

Just install over the older version.
