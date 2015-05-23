=== Registered Users Only ===
Contributors: Viper007Bond
Donate link: http://www.viper007bond.com/donate/
Tags: restriction, registered only, registration
Requires at least: 2.0
Stable tag: trunk

Forces all users to login before being able to view your site. Features an options page for configuration.

== Description ==

Have a private blog that you only want your friends or family to read? Then this plugin may be for you. It will redirect all users who aren't logged in to the login form where they are shown a user-friendly message.

This plugin also features a configuration page where you can easilly toggle allowing guests to access your feeds.

Also, unlike some other registered users only scripts, you can't get around this one by just visiting `index.php?blah=wp-login.php` nor does it break `wp-cron.php` or anything else.

If you need a more advanced plugin, one that redirects to somewhere other than the login form or allows access to your feeds via a unique key (rather than cookies), I suggest [Members Only](http://wordpress.org/extend/plugins/members-only/). If you just need simple guest blocking though, Registered Users Only will work perfectly.

== Installation ==

###Updgrading From A Previous Version###

To upgrade from a previous version of this plugin, delete the entire folder and files from the previous version of the plugin and then follow the installation instructions below.

###Installing The Plugin###

Extract all files from the ZIP file, making sure to keep the file structure intact, and then upload it to `/wp-content/plugins/`.

This should result in the following file structure:

`- wp-content
    - plugins
        - registered-users-only
            | registered-users-only.php
            | readme.txt
            | screenshot-1.png
            | screenshot-2.png`

Then just visit your admin area and activate the plugin.

**See Also:** ["Installing Plugins" article on the WP Codex](http://codex.wordpress.org/Managing_Plugins#Installing_Plugins)

###Using The Plugin###

Just sit back and relax! It will work without you doing anything.

You can however visit the configurations page at Settings -> Registered Only to optionally set some preferences.

== Screenshots ==

1. The login form now with the error message
2. The plugin's options page

== ChangeLog ==

**Version 1.0.3**

* Don't block XML-RPC access so the WordPress iPhone App and others can work.

**Version 1.0.2**

* Fix for WordPress 2.6.

**Version 1.0.1**

* Forgot the localizationd domain on some strings. Included the template file while I was at it.

**Version 1.0.0**

* Initial release.