=== Plugin Name ===
Contributors: Franco Traversaro
Donate link: http://www.eurotraining.it/insitelogin
Tags: login, logout, registration, password recovery
Requires at least: 2.5
Tested up to: 2.7
Stable tag: 0.7

A plugin that insert the standard login procedure into a page

== Description ==

This plugin insert the standard login procedure (login, logout, registration, password recovery) in a selected page of the site. This is made whith a few tricks:

* `wp-login.php` is fully processed, and his output parsed replacing all links to `wp-login.php` with links to current page
* an "init" hook redirect to login page when `wp-login.php` is invoked
* a "login_redirect" filter send to login page on login and logout

Login page can (and have to) be customized for logged in status: this is made through configuration subpanel under the Settings menu.

From 0.6 version, the page for logged-in users contain a sidebar called "insitelogin_sidebar", placed **after** the text inserted into InsiteLogin options. Note that the generated sidebar will be parsed replacing any "%%logout%%" istance, so you can leave blank the option and use only the sidebar with a Text widget.

**Pay attention:** the content of the page selected to run the plugin is *completely ignored*!

== Installation ==

1. Upload `insite_login` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Create a new page with suitable name, e.g. "login"
4. Edit preferences in "InsiteLogin" subpanel under "Settings" menu
5. Modify current theme to style the login form in order to be decent
6. Add any widget you want in `insitelogin_sidebar`, they'll be displayed in logged-in page

== Frequently Asked Questions ==

= Are you sure that this plugin will work on my site? =

Obviously **no**! I've tested it on some Apache and IIS Windows server... I'll be happy if someone will inform me about any problem.

= I've installed XYX plugin that alter the login/logout/registration process, will they work well with InsiteLogin? =

I hope yes. The mechanics I use is (in theory) fully integrated with standard login, and so any plugin altering `wp-login.php` can also alter InsiteLogin. I've tested with [Register Plus](http://wordpress.org/extend/plugins/register-plus/ "Register Plus") and works very well.

= Why require WP 2.5? Will it works with earlier version? =

I think it can work, but I've not tested... If someone can test, please report to me any inconvenience; I think this plugin can work with 2.3 versions, but I'm not sure.

== Screenshots ==

1. The configuration panel
2. The login in action on standard Kubrick theme, Italian locale

== ToDo ==

* wysiwyg in configuration
* maybe retrieve standard CSS for login box?
* customize the layout for insitelogin_sidebar
* shortcode for registration / login / retrieve pw

If you wish any other pretty feature, you've to ask me!

== Changelog ==

#### 0.7 ####

* Custom text before any form


#### 0.6 ####

* PHP4 compatibility (not tested! thanks to **Dominik Denkiewicz**!)
* Correct %%logout%% link (thanks to **Mike Malone**!)
* Dynamic sidebar in logged-in page! Now you can easily customize it with any widget.

#### 0.5 ####

* i18n
* Added Italian locale
* Corrected a bug that make malformed URLs for retrieve password and register
* Deleted Wordpress <h1> tag for better visualization in page

#### 0.4 ####

* Added notification on save options
* Added `die()` after `wp_redirect()` calls
* Added support to `redirect_to`
* Change page title on login/logout

#### 0.3 ####

* First public release!
* Used buffered execution of `wp-login.php` and few filters and actions

#### 0.2 and 0.1 ####

* Private test versions, not usable anymore...
