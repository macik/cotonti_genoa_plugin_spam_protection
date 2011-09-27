
What this plugin does:
-----------------------------------

- Filters comment and forum spam with your choice of services (Akismet, Defensio, Typepad Anti-Spam). 
  Forum filtering is disabled by default and must be enabled in the plugin configuration.
- Provides an administration tool to manage spam items.

Installation:
-----------------------------------

Backup your database as a precaution.

1. Decide which spam service you wish to use. Register at the service you choose to use and obtain an API key.
	- Akismet: http://akismet.com
	- Defensio: http://defensio.com
	- Typepad Anti-Spam: http://antispam.typepad.com
2. Download, unpack and upload the spam_protection plugin folder to your plugin directory.
3. Install the plugin in the administration panel.
4. Go into the plugin's configurations in the admin panel and select your spam service and enter your API key.
5. Check to see that everything is configured to your preference.
6. The rest is up to your spam service and you can find all spam items caught in Administration -> Tools -> Spam Protection

Complete removal:
-----------------------------------

The following is to completely remove this plugin.

1. Uninstall the plugin in the Cotonti plugin administration
2. Delete the plugin folder "spam_protection" in your plugins folder
3. Either run the sql script spam_protection/sql/spam_protection.uninstall.sql or manually delete the database table 
( usually sed_spam_protection ) in phpMyAdmin or the likes

Notes:
-----------------------------------

- You can NOT enable multiple services for filtering an item at this time. You can only have one spam filtering service running.
- You can rename the spam_protection database table by defining it in your config.php:

	```$db_spam_protection = 'cot_spam_protection';```

	Note: Make sure the table exists. You must ensure you renamed your database table if you have already installed the plugin.
	This plugin creates the database table when installed through the administration panel. You can just reinstall the plugin after
	defining the new table name in the config but ensure the old table is removed as it is not needed.

- Spam filtering is disabled when NO API key is present in the plugin configuration.
- Your site will continue working as normal if your API key is not present or not valid, but spam services will not work. 
