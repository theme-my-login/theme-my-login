=== Theme My Login ===
Contributors: thememylogin, jfarthing84
Tags: login, registration, custom login, login page, frontend login
Requires at least: 5.7
Tested up to: 7.1
Stable tag: trunk
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Frontend login, registration and password reset pages that match your theme, at your own URLs, instead of the default wp-login.php screen.


== Description ==

Theme My Login replaces the default WordPress login screen (wp-login.php) with login, registration and password reset pages that live inside your theme, so they look like the rest of your site. It works as soon as you activate it: the pages appear at /login, /register and /lostpassword by default, and WordPress's own login, logout and registration links point to them.

= Features =

* Login, registration, lost password and password reset forms on the frontend of your site, styled by your theme.
* Choose the URL of each page, such as /sign-in instead of /login.
* Let users log in with their username, their email address, or either.
* Let users register with just an email address, no username needed.
* Let users choose their own password at registration, with a strength meter and a show/hide toggle.
* Log new users in automatically after they register.
* Put the forms anywhere with the `[theme-my-login]` shortcode or the login widget.
* Add login, logout and registration links to your navigation menus.
* Submit forms without a page reload by turning on AJAX.
* Works on multisite networks, including network signup.

= Do More With Extensions =

Theme My Login is free. Paid add-ons from our [extensions catalog](https://thememylogin.com/extensions/) build on it:

* [Redirection](https://thememylogin.com/extensions/redirection/): control where users land after they log in, log out or register, based on their role.
* [Moderation](https://thememylogin.com/extensions/moderation/): stop spam sign-ups by requiring email confirmation, admin approval, or both before new users can log in.
* [Restrictions](https://thememylogin.com/extensions/restrictions/): limit posts, pages, menus and widgets to logged-in users or specific roles, or make your whole site private.
* [Security](https://thememylogin.com/extensions/security/): stop brute-force attacks with IP lockouts, set password rules, and shut off wp-login.php.
* [reCAPTCHA](https://thememylogin.com/extensions/recaptcha/): block spam bots on your login, registration, lost password and comment forms with Google reCAPTCHA.
* [Profiles](https://thememylogin.com/extensions/profiles/): give users a profile page that matches your theme, and keep them out of wp-admin.
* [Notifications](https://thememylogin.com/extensions/notifications/): rewrite WordPress's account emails, send them as HTML, and create your own notifications for registration and password events.
* [2FA](https://thememylogin.com/extensions/2fa/): add authenticator-app two-factor authentication to your login, with backup codes and the option to require 2FA for specific roles.
* [Social](https://thememylogin.com/extensions/social/): let visitors log in or register with Google, Facebook or X.
* [Avatars](https://thememylogin.com/extensions/avatars/): let users upload their own profile pictures instead of depending on Gravatar.
* [Favorites](https://thememylogin.com/extensions/favorites/): let logged-in users save posts to a personal favorites page and come back to them later.
* [Mailchimp](https://thememylogin.com/extensions/mailchimp/): add new users to your Mailchimp audiences at registration, automatically or with an opt-in checkbox.

== Installation ==

1. Install the plugin from the Plugins > Add New Plugin screen in WordPress, or upload the plugin files to `/wp-content/plugins/theme-my-login`.
1. Activate it on the Plugins screen.
1. Your login page is now at /login. To change page URLs or login and registration options, go to Theme My Login > General.


== Frequently Asked Questions ==

= How do I add a login form to a page or sidebar? =

Use the `[theme-my-login]` shortcode in any post or page, or add the Theme My Login widget to a sidebar. The shortcode shows the login form by default. `[theme-my-login action="register"]` shows the registration form and `[theme-my-login action="lostpassword"]` the lost password form. See [Using the Shortcode](https://docs.thememylogin.com/article/91-using-the-shortcode).

= Can I change the login page URL? =

Yes. Go to Theme My Login > General and edit the Slugs section, for example changing `login` to `sign-in`. The default wp-login.php keeps working alongside it. To shut wp-login.php off, use the [Security](https://thememylogin.com/extensions/security/) extension.

= Why do I see "User registration is currently not allowed"? =

WordPress ships with registration turned off. Go to Settings > General, check "Anyone can register" next to Membership, and save. See [User Registration Currently Not Allowed](https://docs.thememylogin.com/article/127-user-registration-currently-not-allowed).

= Where can I find documentation? =

Documentation can be found on our [documentation site](https://docs.thememylogin.com).

= Where can I find support? =

Support can be found using our [support form](https://thememylogin.com/support).

= Where can I report a bug? =

Report bugs, suggest ideas and participate in development at [GitHub](https://github.com/theme-my-login/theme-my-login/).


== Screenshots ==

1. The login page inside your theme, at /login.
2. Registration with user-chosen passwords, a strength meter and a show/hide toggle.
3. Login and registration settings.
4. Choose the URL of every page.
5. Browse extensions from inside WordPress.


== Changelog ==

= 7.2.2 =
* Stop a lost password request from overwriting an account's password on multisite (props R3D)

= 7.2.1 =
* Stop the dashboard greeting from rendering HTML in a user's name
* Prevent invalid characters in custom action slugs from breaking site URLs
* Stop making a live request to the extension store on every admin/cron update check
* Block object injection in extension store API responses
* Restrict extension license checks to administrators
* Stop auto-login from replacing an already logged-in visitor's session
* Prevent a fatal error on the login page from bracketed query parameters
* Keep the activation confirmation page from breaking without an activation
* Enforce password validation on every registration entry point
* Keep the password show/hide button from breaking under page button styles
* Fix permalinks sometimes not flushing on activation
* Stop unconfirmed privacy-request links from disclosing request status
* Stop extension auto-updates from failing to download in wp-admin

= 7.2.0 =
* Add a filter to disable autofocus on login/register fields
* Add a show/hide toggle to password fields
* Block multisite site creation when network registration is disabled (props Jakub Herman)
* Add default field/button styling
* Update alert styling to match current WP core admin notices
* Default network admin menu items to manage_network_options

= 7.1.15 =
* Resync Multisite signup form strings with current WordPress core wording
* Fix potential stored XSS in textarea form fields
* Prevent a PHP 8.5 deprecation notice on wp-admin screens with no TML settings page
* Escape settings field attributes to prevent stored XSS from unescaped option values
* Sanitize Multisite network settings on save, matching single-site behavior
* Verify nonce on extension license activate/deactivate requests to prevent CSRF
* Fix extension icons not displaying due to unserialized API data
* Reduce extension data added to the plugins update transient
* Use a dedicated cache group when caching TML pages

= 7.1.14 =
* Ensure license nonce check only occurs on licenses page

= 7.1.13 =
* Check for proper permission when managing licenses
* Check for nonce when managing license keys

= 7.1.12 =
* Revert converting get_title() to abstract on TML extension class

= 7.1.11 =
* Reinstate missing message on lostpassword form
* Convert get_title() to an abstract function on TML extension class

= 7.1.10 =
* Tested up to 6.7.1
* Ensure settings attributes are properly wrapped in quotes
* Fix nonce check when saving multisite settings

= 7.1.9 =
* Fix multisite settings save regression caused by 7.1.8
* Mark slug settings fields as required

= 7.1.8 =
* Tested up to 6.6.1
* Add nonce check to multisite settings page

= 7.1.7 =
* Tested up to 6.6
* Update password reset handler to reflect WP core
* Update WP core strings to match the current state of WP core
* Enforce proper permissions when removing notices
* Transform TML Action nav menu items earlier to provent collisions
* Fix overriding of block theme templates

= 7.1.6 =
* Tested up to 6.3
* Ensure action page match check query is only ran once
* Fix `login_form_{$action}` action hook
* Add the `login_footer` action hook
* Update invalid email error message

= 7.1.5 =
* Tested up to 6.0.1
* Use raw $_POST value when setting custom user passwords
* Fix the login link on AJAX lost password form confirmation
* Implement fix for PATHINFO permalinks

= 7.1.4 =
* Hide form for "check your email" step of password reset
* Use retrieve_password() from WP core and deprecate compat.php
* Update strings to match core
* Add a filter to allow disabling network signup redirect
* Fix deprecated notice in password strength meter

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

= Earlier versions =
* [Changes before 7.0](https://github.com/theme-my-login/theme-my-login/blob/master/src/changelog.txt)


== Upgrade Notice ==

= 7.1 =
Theme My Login now requires WordPress 5.4+, and by extension, PHP 5.6.20+.

= 7.0 =
Modules are no longer included with the plugin. Please consider this before you upgrade!
