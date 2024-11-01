=== Wordpress Volunteer Project Manager ===
Contributors: digiacom, meitar
Donate link: http://www.lioneltarot.com/tip-jar
Tags: nonprofit, volunteer, project, manager
Requires at least: 3.8.1
Tested up to: 3.9
Stable tag: 0.3.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==

Display and manage volunteer projects for any purpose.

* Registers custom post type `wp-vpm-projects`
* Registers metaboxes to the editing screen for project meta fields

**This plugin is in beta, and is not yet appropriate for a production site.**

Important features like post ordering and administrative settings are absent.

That said, perhaps you will find it useful!

This plugin is in use over at [Volunteer Wild](http://www.volunteerwild.org "Conservation stewardship in Durango, Colorado")

== Installation ==

1. Upload the `wp-vpmanager` zip file to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. To get basic functionality, place the [wp-vpm-status] and [wp-vpm-joinbutton] shortcodes in a new project.

= You also need to install the following plugins =

This plugin depends on the following plugins for some core functionality:

* [Waitlists for WordPress](https://wordpress.org/plugins/wp-waitlist/)

Please make sure all of these plugins are installed and activated before installing WordPress Volunteer Project Manager.

== Frequently Asked Questions ==

= Will you add custom functionality for me? =

Maybe! Contact me and I'll see what I can do.

= Something doesn't work. Can you fix it? =

I am still learning, and theres a good chance you'll be able to fix it as well as I. That said, I'll do my best.

== Screenshots ==

1. Project listed with the shortcode functions placed above and below the content.
2. Admin metabox. Currently, meta options are hardcoded.

== Changelog ==

= 0.3.1 =

* Enhancement: Use Scope and Difficulty taxonomies by default. You may need to manually deactivate and then re-activate this plugin to popualte the default taxonomy values.
* Bugfix: If dependencies are not met, deactivate the plugin and issue a notice only when an admin logs in.

= 0.3 =

* Enhancement: Plugin forms now use `<label>`s for improved accessibility.
* Bugfix: Remember project settings after a page reload.
* Remove shortcodes as CSS is preferred for styling.

= 0.2b =
* Better future localization support.
* Better join/waitlist button behaviour.

= 0.2a =
* Localised CSS to make compatible with WordPress plugin directory.

= 0.2 =
* Join/waitlist functionality implemented.
* Improved plugin structure.

= 0.1 =
* Basic plugin sketched out.

== Upgrade Notice ==

= 0.2 =
Massive usability compared to previous version.
