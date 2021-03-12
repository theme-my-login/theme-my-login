=== Theme My Login ===
Contributors: thememylogin, jfarthing84
Tags: login, register, password, branding, customize, widget, wp-login, wp-login.php
Requires at least: 5.4
Tested up to: 5.7
Stable tag: trunk

The ultimate login branding solution! Theme My Login offers matchless customization of your WordPress user experience!


== Description ==

Ever wished that your WordPress login page matched the rest of your site? Your wish has come true! Theme My Login allows you to bypass the default WordPress-branded login page that looks nothing like the rest of your site. Instead, your users will be presented with the login, registration and password recovery pages right within your theme. The best part? It works right out of the box, with no configuration necessary! Take back your login page, WordPress users!


= Features =

* Have you users log in from the frontend of your site.
* Have your users register from the frontend of your site.
* Have your users recover their password from the frontend of your site.
* Customize the slugs used for login, registration, password recovery and other pages.
* Allow your users to register with only their email.
* Allow your users to set their own passwords upon registration.
* Allow your users to log in using either their email and password, username and password or a combination of the two.
* Allow your users to be logged in automatically after registration with auto-login.


= Do More With Extensions =

Boost your user experience even more with add-on plugins from our [extensions catalog](https://thememylogin.com/extensions). Some of our extensions include:

* [Redirection](https://thememylogin.com/extensions/redirection) allows you to redirect your users on login, logout and registration based on their role.
* [Restrictions](https://thememylogin.com/extensions/restrictions) allows you to restrict posts/pages, widgets and nav menu items based on a users login status and/or role.
* [Profiles](https://thememylogin.com/extensions/profiles) lets your users edit their profile from the frontend of your site.
* [Moderation](https://thememylogin.com/extensions/moderation) allows you to moderate your users by requiring them to confirm their email or by requiring admin approval.
* [reCAPTCHA](https://thememylogin.com/extensions/recaptcha) enables Google reCAPTCHA support for your registration and login forms.
* [Social](https://thememylogin.com/extensions/social) allows you to allow your users to log in to your site using their favorite social providers.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/theme-my-login` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress


== Frequently Asked Questions ==

= Where can I find documentation? =

Documentation can be found on our [documentation site](https://docs.thememylogin.com).

= Where can I find support? =

Support can be found using our [support form](https://thememylogin.com/support).

= Where can I report a bug? =

Report bugs, suggest ideas and participate in development at [GitHub](https://github.com/theme-my-login/theme-my-login/).


== Changelog ==

= 7.1.3 =
* Fix PHP 8 notices
* Fix wp_sensitive_page_meta() deprecated notice in WP 5.7+
* Update password reset button text for WP 5.7+
* Add `lostpassword_user_data` filter

= 7.1.2 =
* Fix site crashing on Bluehost
* Add requester IP address to password reset emails
* Fix multisite settings for WP 5.5+
* Default AJAX requests to off
* Bring wp-login.php duplicated code up to date

= 7.1.1 =
* Implement option to enable/disable AJAX
* Fix AJAX not working on certain server environments
* Fix AJAX errors not displaying when the AJAX request fails
* Revert forcing actions to the Dashboard when logged in

= 7.1 =
* Implement AJAX support
* Introduce new Dashboard action
* Improve performance by reducing queries
* Require WordPress 5.4
* Remove angle brackets from password reset link in notification
* Add sensitive page meta tags to TML actions
* Add missing `lost_password` action hook
* Fix lostpassword link being rewritten on wp-login.php

= 7.0.15 =
* Fix extension update issues caused by caching
* Add `tml_script_dependencies` filter
* Add `tml_script_data` filter

= 7.0.14 =
* Fix login page error on on WP 5.2+
* Implement caching for remote extension data to speed up plugins screen
* Implement a CLI command for adding TML actions as nav menu items
* Ensure password recovery error messages match core
* Fix notice if a settings field isn't passed with an `args` parameter
* Fix permalinks being flushed on all TML admin pages
* Allow form attributes to be set in the form constructor
* Add `tml_activate_extension` action hook
* Add `tml_deactivate_extension` action hook

= 7.0.13 =
* Ensure proper retrieval of request parameters in all server configuration scenarios
* Ensure scripts and styles are loaded in the proper order on TML actions
* Ensure TML scripts are loaded in the footer
* Ensure password errors are only displayed where appropriate
* Ensure all strings not found in frontend core translation are translatable
* Implement callable usage of custom form field content
* Implement methods for manipulation of form field classes
* Add `tml_render_form` action hook
* Add `tml_render_form_field` action hook
* Add `tml_before_form` filter
* Add `tml_after_form` filter
* Add `tml_before_form_field` filter
* Add `tml_after_form_field` filter
* Add `tml_get_form_field_content` filter

= 7.0.12 =
* Ensure that styles are more likely to be applied
* Ensure checkbox labels are inline
* Ensure password errors are only applied on register action
* Pass page slug for rewrites if a matching page exists
* Ensure query args are encoded when rewriting URLs
* Ensure query args are passed for actions redirected from login
* Add license activation notice to extension update messages
* Add action links to plugins screen
* Ensure PHP 5.2 support for development
* Ensure hierarchical slugs work properly

= 7.0.11 =
* Ensure that actions use their own page
* Ensure the lostpassword action uses a TML link
* Fix undefined variable notice when recovering password
* Fix password strength meter script loading on every request
* Only show TML notices on the Dashboard or TML pages
* Fix undefined variable notices when handling an action
* Fix stomping of other plugins actions

= 7.0.10 =
* Fix admin notices displaying for non-privileged users
* Reinstate default testcookie method
* Don't allow TML actions to stomp on other content
* Don't allow TML actions to stomp on other TML actions
* Allow non-TML actions to be handled
* Include labels for custom fields if present
* Hide comments on TML pages
* Fix generation of non-pretty action links
* Apply `login_redirect` filter to auto-login registration redirect
* Fix new user notification being sent when unchecked upon creating a user

= 7.0.9 =
* Fix fatal error on PHP versions less than 5.5
* Apply `tml_get_action_tile` filter at the object level
* Apply `tml_get_action_slug` filter at the object level
* Apply `tml_get_action_url` filter at the object level

= 7.0.8 =
* Fix slow-loading extensions page
* Add dismissible notice of latest available extension
* Fix "stuck" license status by verifying when visiting the licenses page
* Ensure a form field object is returned when adding a form field
* Fix testcookie step causing a 403 error
* Fix links not being changed in emails sent from the Dashboard

= 7.0.7 =
* Fix sorting of form fields
* Fix "Remember Me" not being clickable
* Add "checked" property to form fields to allow for easy checking of checkboxes
* Add plugin textdomain to strings not found in front-end core translations
* Add `tml_send_new_user_notification` filter
* Add `tml_send_new_user_admin_notification` filter
* Add `tml_retrieve_password_email` filter

= 7.0.6 =
* Fix a fatal error when removing form fields
* Fix a 408/502 error when hosted with Namecheap
* Fix notices in widget when upgrading from 6.4.x
* Add default contextual help for extensions
* Move `after` argument for forms to after the container

= 7.0.5 =
* Allow custom actions to have custom slugs
* Show the URL below each slug setting field
* Add contextual help to plugin pages
* Implement a "user panel" within the login widget
* Add a filter to disable showing of the widget: `tml_show_widget`
* Add a filter to change the avatar size in the "user_panel": `tml_widget_avatar_size`
* Add a filter to change the links in the "user panel": `tml_widget_user_links`

= 7.0.4 =
* Fix a notice that appears when unregistering an action
* Don't fire form actions until the form is being rendered
* Set a secure cookie for sites using SSL
* Add `login_init` and `login_form_{$action}` action hooks
* Add `login_head` and `login_enqueue_scripts` action hooks
* Add `register_form`, `lostpassword_form`, and `resetpass_form` action hooks
* Add `signup_hidden_fields`, `signup_extra_fields`, and `signup_blogform` action hooks

= 7.0.3 =
* Fix an error on PHP versions less than 5.3
* Allow for description in settings API functions
* Fix compatibility with legacy shortcode
* Rewrite certain admin login links
* Remove undesired actions and filters from TML pages
* Introduce new `tml_action_{$action}` hook and use it for handlers

= 7.0.2 =
* Fix collision with some plugins which modify the nav menu edit walker
* Fix a notice in multisite
* Fix pages not using custom templates when used as TML actions
* Fix shortcode not working in certain circumstances when no action is present

= 7.0.1 =
* Fix error where WP_Query is used before expected by other plugins
* Fix existing shortcodes from pre-7 not working due to missing action
* Fix compatibility with plugins that use some legacy methods on the plugin class
* Fix registration redirection when auto-login is enabled
* Allow actions to be represented by pages if their slugs match
* Fix legacy page menu items no longer behaving as they did pre-7

= 7.0 =
* Rewrite plugin from the ground up
* Pages are no longer used to represent actions
* Actions are now represented by a class
* Actions can be added/remove on the fly
* Forms are now represented by a class
* Forms can be added/remove on the fly
* Form fields can be added/removed/modified/rearranged on the fly
* Extensions can easily be written and integrated with the plugin
* Move Custom E-mail module to a commercial extension
* Merge Custom Passwords module into core plugin
* Move Custom Redirection module to a commercial extension
* Remove Custom User Links module
* Move reCAPTCHA module to a commercial extension
* Move Security module to a commercial extension
* Move Themed Profiles module to a commercial extension
* Move User Moderation module to a commercial extension
* Add option to allow auto-login after registration

= 6.4.17 =
* Fix the version check logic in the updater
* Implement path to download 6.4.x releases only

= 6.4.16 =
* Require opt-in to update the plugin to 7

= 6.4.15 =
* Fix a bug where pages were being excluded from legacy page menus and search
* Add a notice about the impending release of 7

= 6.4.14 =
* Fix the "cookies blocked" notice that appeared upon entering invalid login credentials
* Style alert link colors to match the alert that they're in
* Tweak styling of the "Remember me" checkbox and label

= 6.4.13 =
* Implement a TML action selector for pages
* Fix error about cookies not being enabled when they are

= 6.4.12 =
* Add support for data requests
* Utilize Bootstrap 3 colors for notices

= 6.4.11 =
* Fix fatal error when attempting to rewrite login links before `init` action
* Add the test cookie functionality from wp-login.php
* Fix a notice in the postpass action handler
* Make User Moderation login type aware
* Fix display of password length requirements
* Fix a multisite error when the main site is not ID 1
* Don't allow squashing of the main instance by shortcode attribute

= 6.4.10 =
* Add weak password confirmation checkbox to password reset form
* Introduce `tml_enforce_private_site` filter
* Introduce `tml_minimum_password_length` filter
* Hide admin bar checkbox on themed profiles when admin is disabled

= 6.4.9 =
* Fix fatal error from typo in previous release

= 6.4.8 =
* Fix errors and messages not displaying anywhere except default pages
* Don't exclude TML pages from search in admin area or if not the main query

= 6.4.7 =
* Don't allow locked users to log in using their email address

= 6.4.6 =
* Fix errors and messages not displaying
* Don't add reCAPTCHA errors when adding a user via wp-admin or WP-CLI
* Improve PHP 7 compatibility
* Introduce `tml_page_id` filter
* Improve deliverability of HTML emails
* Fix disabling of User Denial email notification
* Pass locale to reCAPTCHA script allowing reCAPTCHA to be localized
* Don't allow pending users to log in using their email address
* Fix email content types from being reset

= 6.4.5 =
* Don't clear username input on login form when autofocusing
* Fix custom e-mail disable checkboxes defaulting to being checked
* Fix login type functionality
* Bring wp-login.php duplicated code up to date
* Require WordPress 4.5

= 6.4.4 =
* Fix file loading for non-standard directory setups
* Fix language files not loading properly
* Fix password reset cookie path

= 6.4.3 =
* Fix sending of custom emails when creating a user
* Fix sending of custom emails on user activation/approval
* Fix translation loading logic
* Require WordPress 4.4

= 6.4.2 =
* Fix deprecated function notices
* Deprecate "tml_user_password_changed" hook in favor of "after_password_reset"
* Deprecate "tml_new_user_registered" hook in favor of "register_new_user"

= 6.4.1 =
* Allow array of actions in Theme_My_Login::is_tml_page()
* Lost Password nav menu item will only show when not logged in
* Hide action links on Reset Password page
* Fix false password reset error caused by referer redirection
* Fix PHP strict warning about abstract class constructor compatibility

= 6.4 =
* Add option to login using either username only, email only or both
* Add option to disable user denial notification when admin approval is active
* Update reCAPTCHA module to API version 2.0
* Login and Register nav menu items only show when not logged in
* Logout and Profile nav menu items only show when logged in
* Better default stylesheet
* Fix TML pages displaying in search results
* Fix logout redirect
* Fix broken interim login when wp-login.php is disabled
* Remove AJAX module
* Require WordPress 4.3.1

= 6.3.12 =
* Fix multiple widget custom redirect error
* Add autocomplete="off" to login form password field
* Fix password reset process
* Fix SSL admin JS

= 6.3.11 =
* Fix interim login
* Fix partial translations
* Fix toolbar disappearing when updating a themed profile

= 6.3.10 =
* Fix local file include vulnerability in templating system

= 6.3.9 =
* Fix strict standards errors
* Fix deprecated function notices

= 6.3.8 =
* Fix issue where pages would redirect to profile page with Themed Profiles active

= 6.3.7 =
* Revert tml_page post type back to default WP pages
* Fix issue where SSL warnings were displayed in reCAPTCHA module
* Fix issue where a blank page resulted when 404.php didn't exist in a theme
* Fix issue where User Links couldn't be deleted
* Fix issue where "Are you sure?" would display when attempting to log out
* Fix issue where strings weren't being translated on Profile page

= 6.3.6 =
* Fix issue where all module options were set once activated
* Fix issue where template tag was not being output
* Fix issue where install failed during new blog creation on multisite
* Fix issue where error messages were duplicated on login pages

= 6.3.5 =
* Fix issue with blank pages where page.php didn't exist in a theme
* Fix issue where activating Themed Profiles resulted in a 404 for profile page
* Fix issue where options were being deleted upon upgrade
* Fix issue with AJAX module not working properly in Internet Explorer

= 6.3.4 =
* Use verbose rewrite rules for TML pages

= 6.3.3 =
* Fix issue where actions weren't being appended to page links
* Fix issue where modules weren't being installed on upgrade
* Fix fatal error in Custom E-mail module where old function name wasn't replaced
* Fix private constructor issue for PHP versions less than 5.3

= 6.3.2 =
* Fix issue where pages weren't created when upgrading from previous versions

= 6.3.1 =
* Fix multisite 404 error when using Post Name permalink structure
* Fix multisite redirect to main site for register

= 6.3 =
* Introduce tml_page post type and give each action it's own page
* Introduce AJAX module
* Implement user lock notifications for the Security module.
* Add option to hide widget when logged out
* Add option to disable wp-login.php to Security module
* Removed languages from plugin
* Use Custom E-mail's New User template when a user is added via wp-admin
* Use Custom E-mail's User Activation template when an activation is resent via wp-admin

= 6.2.3 =
* Fix static front page bug
* Remove tab indexes from forms

= 6.2.2 =
* Fix redirect loop bug
* Add visual cues for permalinks
* Fix iframe bug

= 6.2.1 =
* Add post password handling
* Don't block admin when DOING_AJAX
* Add WordPress updated message
* Replace deprecated get_userdatabylogin with get_user_by

= 6.2 =
* Fix FORCE_SSL_ADMIN logic
* Add tabindex to password fields
* Fix removal of actions from "tml_new_user_registered" action in User Moderation module
* Add %username% variable to Custom User Links module
* Add custom permalinks to core
* Add option to disable e-mail login
* Fix potential XSS attack vulnerability
* Update admin bar settings for 3.3 in Themed Profiles module
* Update multisite templates for 3.3
* Fix autofocus scripts to only load on login page
* Require 3.1+
* Fix broken login redirect logic
* Add option to require login to view site in Security module
* Don't change profile URL for non-themed roles in Themed Profiles module
* Display failed login attempts to administrators on user profiles in Security module
* Fix capability check for non-standard table prefix in User Moderation module
* Add separate profile templates per user role in Themed Profiles module
* Fix password recovery admin e-mail in Custom E-mail module
* Don't show admin options when admin is blocked in Themed Profiles module
* Treat multisite users with no role as subscribers in all modules
* Fix multisite registration bug in Themed Profiles module

= 6.1.4 =
* Don't hijack non-related form posts

= 6.1.3 =
* Fix password change error
* Update POT file

= 6.1.2 =
* Replace "self" keyword with "$this" for PHP 4

= 6.1.1 =
* Implement 3.1 password reset routine
* Add 3.1 fields to Themed Profiles
* Better default stylesheet for Themed Profiles
* Add 'nofollow' attribute to action links
* Check for SSL
* Add nofollow and noindex to login page
* Fix missing argument notices
* Fix deprecated argument notices
* Fix undefined method notices
* Fix install/uninstall routines
* Fix Custom user Links AJAX
* Fix Custom E-mail "From" filters
* Fix disabling of admin password change notification
* Fix "resent" custom activation e-mail

= 6.1 =
* Fully support multisite
* Require WordPress 3.0+
* Add Bulgarian translation
* Add (Belgian) Dutch translation
* Add Romanian translation

= 6.0.4 =
* Fix admin e-mail notification disabling
* Fix labels for login form fields
* Fix wp-login.php form action URL

= 6.0.3 =
* Fix login reauth bug in redirection module

= 6.0.2 =
* Fix Login page creation during install
* Fix template tag argument parsing

= 6.0.1 =
* Fix logout link for wp_nav_menu()
* Fix issue admin page not always being tabbed
* Fix issue of assigning multiple roles per user when using Moderation
* Add German translation
* Add Farsi (Persian) translation
* Add Hebrew translation
* Add Russian translation
* Update other languages

= 6.0 =
* Complete code rewrite
* Users can now log in with e-mail address as well as username
* Remove option to disable template tag and widget in favor of always being enabled
* Remove option to rewrite login links in favor of always being rewritten
* Custom templates can now be defined per action (login, register, etc.)
* User moderation activation e-mails can be resent on demand
* Add various new hooks to help with custom integration with other plugins
* Make custom user links sortable
* Customize every aspect of every e-mail
* Add a cool new random tip widget in the TML admin
* Use WP 3.0 functions (such as 'network_site_url') if available
* phpDoc everywhere!

= 5.1.6 =
* Fix issue with spaces in usernames

= 5.1.5 =
* Fix blank page redirect bug

= 5.1.4 =
* Fix the_title() bug fro WP versions before 3.0 (again)
* Fix undefined is_user_logged_in() bug

= 5.1.3 =
* Make Themed Profiles work properly

= 5.1.2 =
* Fix the_title() bug for WP versions before 3.0
* Fix redirection bug caused by 5.1.1 update

= 5.1.1 =
* Fix bug that blocked users from entire site once logged in
* PROPERLY display "Log Out" when page is shown in pagelist and logged in

= 5.1 =
* Display "Log Out" when page is shown in pagelist and logged in
* Forward profile.php to themed profile when module is active
* Allow for %user_id% in custom user links
* Add inline descriptions to all settings
* Various tweaks and preps for WP 3.0
* Add Italian translation
* Add Danish translation
* Add Polish translation
* Add Spanish translation

= 5.0.6 =
* Pass $theme_my_login by reference in option functions
* Remove accidental invalid characters

= 5.0.5 =
* Add 'theme-my-login-page' shortcode before 'theme-my-login' shortcode

= 5.0.4 =
* Re-introduce 'theme-my-login-page' shortcode for main login page
* Add French translation
* Fix typo in function override notice functions
* Make 2nd argument optional in 'get_pages' filter
* Remove another 'self' reference in class.php
* Fix typo in readme.txt

= 5.0.3 =
* Fix an improper fix for PHP4 style constructor in class
* Only display function override notices on TML settings page properly

= 5.0.2 =
* Fix improper function call for PHP4 style constructor in class

= 5.0.1 =
* Only display function override notices on TML settings page
* Typecast arrays as arrays (Fixes invalid datatype notices)
* Add plugin domain to all gettext calls

= 5.0 =
* Rewrite code in a modular fashion in order to speed up plugin
* Convert custom e-mails, passwords, redirection, user links and user moderation to "modules"
* Add the option to enable/disable link rewriting, widget and template tag
* Simplify/optimize admin tabs style
* Remember current admin tab after save
* When using custom passwords, allow users to set their own password upon reset
* When using custom redirection, specify redirection type per user role/per link type
* New ajax interface for user links admin
* Theme My Profile now merged into module

= 4.4 =
* Added the option to require new registrations to confirm e-mail address
* Added the option to redirect users upon log out according to their role
* Allow 'theme-my-login.css' to be loaded from current theme directory
* Cleaned up and rewrote most code
* Drop support for WP versions below 2.8

= 4.3.4 =
* Added the option to force redirect upon login

= 4.3.3 =
* Fixed a redirection bug where WordPress is installed in a sub-directory
* Add CSS style to keep "Remember Me" label inline with checkbox

= 4.3.2 =
* Added the option to redirect unapproved and/or denied users to a custom URL upon login attempt
* Fixed a bug where custom user password is lost if user moderation is enabled
* Fixed a PHP notice in the admin

= 4.3.1 =
* Fixed a MAJOR security hole that allowed anyone to login without a password!!

= 4.3 =
* Added the option to require approval for new registrations
* Added the option to enable/disable plugin stylesheet
* Removed form input fields from label tags
* Dropped support for WordPress versions older than 2.6

= 4.2.2 =
* Added the option to remove 'Register' and/or 'Lost Password' links
* Fixed a bug that sent e-mail from all plugins from this plugins setting

= 4.2.1 =
* Fixed a bug that broke other plugins e-mail format
* Fixed a bug that could break plugin upon upgrade

= 4.2 =
* Added the option to send e-mails in HTML format
* Fixed a bug that broke custom user role links if all links were deleted

= 4.1.2 =
* Added the ability to change main login page ID (Only needed for debugging)
* The login will now revert to default wp-login in the case of plugin failure

= 4.1.1 =
* Fixed a major bug dealing with saving options that broke the plugin
* Fixed a CSS bug causing interference with other interfaces that use jQuery UI Tabs

= 4.1 =
* Implemented custom user passwords
* Implemented custom e-mail from name & address
* Removed template tag & shortcode restriction on main login page

= 4.0 =
* Implemented custom links for logged in users based on role
* Implemented custom redirection upon log in based on role
* Implemented custom registration/password recovery emails
* Implemented true shortcode and template tag functionality
* Implemented true multi-instance functionality
* Implemented an easy-to-use jQuery tabbed administration menu
* Implemented both 'fresh' and 'classic' colors for administration menu

= 3.3.1 =
* Fixed a bug that broke password recovery due to the new system from WP 2.8.4

= 3.3 =
* Fixed a bug that disabled error display when GET variable 'loggedout' was set
* Added template tag access

= 3.2.8 =
* Fixed a security exploit regarding admin password reset addressed in WordPress 2.8.4

= 3.2.7 =
* Fixed a bug that determined how to create the widget

= 3.2.6 =
* Fixed a bug dealing with the version_compare() function
* Included French translation
* Included Spanish translation

= 3.2.5 =
* Fixed a bug that produced a 'headers aldready sent' error when uploading media
* Included Dutch translation

= 3.2.4 =
* Fixed the load_plugin_textdomain() call
* Added 'login_head' action hook

= 3.2.3 =
* Fixed and updated many gettext calls for internationalization

= 3.2.2 =
* Added the option to leave widget links blank for default handling

= 3.2.1 =
* Fixed a XHTML validation issue

= 3.2 =
* Added the option to allow/disallow registration and password recovery within the widget
* Fixed a bug regarding color names within the CSS file that broke validation

= 3.1.1 =
* Fixed a bug that incorrectly determined current user role

= 3.1 =
* Added the ability to specify URL's for widget 'Dashboard' and 'Profile' links per user role
* Implemented WordPress 2.8 widget control for multiple widget instances
* Fixed a bug regarding the registration complete message

= 3.0.3 =
* Fixed a bug with the widget links

= 3.0.2 =
* Fixed a bug that didn't allow custom registration message to be displayed
* Fixed a few PHP unset variable notice's with a call to isset()

= 3.0.1 =
* Fixed a bug that caused a redirection loop when trying to access wp-login.php
* Fixed a bug that broke the widget admin interface
* Added the option to show/hide login page from page list

= 3.0 =
* Added a login widget

= 2.2 =
* Removed all "bloatware"

= 2.1 =
* Implemented login redirection based on user role

= 2.0.8 =
* Fixed a bug that broke the login with permalinks

= 2.0.7 =
* Fixed a bug that broke the Featured Content plugin

= 2.0.6 =
* Added the option to turn on/off subscriber profile theming

= 2.0.5 =
* Fixed a bug with default redirection and hid the login form from logged in users

= 2.0.4 =
* Fixed a bug regarding relative URL's in redirection

= 2.0.3 =
* Fixed various reported bugs and cleaned up code

= 2.0.2 =
* Fixed a bug that broke registration and broke other plugins using the_content filter

= 2.0.1 =
* Fixed a bug that redirected users who were not yet logged in to profile page

= 2.0 =
* Completely rewrote plugin to use page template, no more specifying template files & HTML

= 1.2 =
* Added capability to customize page titles for all pages affected by plugin

= 1.1.2 =
* Updated to allow customization of text below registration form

= 1.1.1 =
* Prepared plugin for internationalization and fixed a PHP version bug

= 1.1.0 =
* Added custom profile to completely hide the back-end from subscribers

= 1.0.1 =
* Made backwards compatible to WordPress 2.5+

= 1.0.0 =
* Initial release version


== Upgrade Notice ==

= 7.1 =
Theme My Login now requires WordPress 5.4+, and by extension, PHP 5.6.20+.

= 7.0 =
Modules are no longer included with the plugin. Please consider this before you upgrade!
