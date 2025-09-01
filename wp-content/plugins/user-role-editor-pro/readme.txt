=== User Role Editor Pro ===
Contributors: Vladimir Garagulya (https://www.role-editor.com)
Tags: user, role, editor, security, access, permission, capability
Requires at least: 4.4
Tested up to: 6.8
Stable tag: 4.64.5
Requires PHP: 7.3
License: GPLv2 or later
License URI: https://www.role-editor.com/end-user-license-agreement/

User Role Editor Pro WordPress plugin makes user roles and capabilities changing easy. Edit/add/delete WordPress user roles and capabilities.

== Description ==

User Role Editor Pro WordPress plugin allows you to change user roles and capabilities easy.
Just turn on check boxes of capabilities you wish to add to the selected role and click "Update" button to save your changes. That's done. 
Add new roles and customize its capabilities according to your needs, from scratch of as a copy of other existing role. 
Unnecessary self-made role can be deleted if there are no users whom such role is assigned.
Role assigned every new created user by default may be changed too.
Capabilities could be assigned on per user basis. Multiple roles could be assigned to user simultaneously.
You can add new capabilities and remove unnecessary capabilities which could be left from uninstalled plugins.
Multi-site support is provided.

== Installation ==

Installation procedure:

1. Deactivate plugin if you have the previous version installed.
2. Extract "user-role-editor-pro.zip" archive content to the "/wp-content/plugins/user-role-editor-pro" directory.
3. Activate "User Role Editor Pro" plugin via 'Plugins' menu in WordPress admin menu. 
4. Go to the "Settings"-"User Role Editor" and adjust plugin options according to your needs. For WordPress multisite URE options page is located under Network Admin Settings menu.
5. Go to the "Users"-"User Role Editor" menu item and change WordPress roles and capabilities according to your needs.

In case you have a free version of User Role Editor installed: 
Pro version includes its own copy of a free version (or the core of a User Role Editor). So you should deactivate free version and can remove it before installing of a Pro version. 
The only thing that you should remember is that both versions (free and Pro) use the same place to store their settings data. 
So if you delete free version via WordPress Plugins Delete link, plugin will delete automatically its settings data. Changes made to the roles will stay unchanged.
You will have to configure lost part of the settings at the User Role Editor Pro Settings page again after that.
Right decision in this case is to delete free version folder (user-role-editor) after deactivation via FTP, not via WordPress.

== Changelog ==

= [4.64.5] 17.04.2025 =
* Core version: 4.64.5
* Update: Marked as compatible with WordPress 6.8
* Fix: PHP Deprecated:  URE_Widgets_Admin_View::get_html(): Implicitly marking parameter $user as nullable is deprecated, the explicit nullable type must be used instead in /wp-content/plugins/user-role-editor-pro/pro/includes/classes/widgets-admin-view.php on line 133
* Fix: PHP Notice:  Function _load_textdomain_just_in_time was called <strong>incorrectly</strong>. Translation loading for the <code>user-role-editor</code> domain was triggered too early. This is usually an indicator for some code in the plugin or theme running too early. Translations should be loaded at the <code>init</code> action or later. pro/includes/classes/addons-manager.php called esc_html__() from constructor. Moved to the 'init' action.
* Core version was updated to 4.64.5
* Update: Minor changes were applied to the CSS/JS loading code to minimize "Plugin Check" tool warnings.
* Plugin headers were extended at role-editor.php and readme.txt files according to wordpress.org recommendations.

= [4.64.4] 15.12.2024 =
* Core version: 4.64.4
* Core version was updated to 4.64.4
* Security Fix: Users - "Add Role", "Revoke Role" buttons: Cross-Site request forgery to privilege escalation was possible due to missed nonce validation. This issue was discovered and responsibly reported by vgo0.

= [4.64.3] 04.12.2024 =
* Core version: 4.64.3
* Update: Marked as compatible with WordPress 6.7.1
* Core version was updated to 4.64.3
* Fix: PHP Notice:  "Function _load_textdomain_just_in_time was called incorrectly. Translation loading for the <code>user-role-editor</code> domain was triggered too early." was fixed (shown only for those who used own .mo translation file installed).
* Fix: Miscellaneous translation functionality (l18n) usage enhancements were applied.

Full list of changes is available in changelog.txt file.
