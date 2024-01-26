# Cloudflare Access SSO for WordPress

Cloudflare Access SSO (Single Sign On) is a plugin to facilitate auto-login to WordPress. The plugin relies on authorisation by Cloudflare, so it's important that you follow this setup guide carefully to ensure your site remains secure. For further guidance, refer to [Cloudflare Documentation: Add Site to Cloudflare](https://developers.cloudflare.com/fundamentals/get-started/setup/add-site/).

> Note: If you don't currently use Cloudflare and don't plan to, this plugin probably isn't suitable for your site.

### Cloudflare Access Setup

In order to use Cloudflare Access for SSO, you must create an application that covers `wp-login.php` on the site you wish to protect. No other URLs are required to be protected for this to function, but for better security you may wish to include others. Note that (as of June, 2023) it is not possible to define more than one path in a single application; for now multiple applications are required if you additionally wish to protect `/wp-admin`.

Follow this guide to create a [Cloudflare Access Application: Self Hosted Applications](https://developers.cloudflare.com/cloudflare-one/applications/configure-apps/self-hosted-apps/)

### Plugin Configuration

Two constants are required to be set in `wp-config.php` (note: a settings page will be added in a future release):

`CF_ACCESS_TEAM_NAME` The Cloudflare Access Team Name
To get the Team Name:
- Open the Zero Trust Dashboard (see above)
- Navigate to "Settings" in the left sidebar menu, then click "General Settings"
- Edit the Team domain - the editable component is the Team Name (ignore `.cloudflareaccess.com` that follows)
	- e.g. if the value once saved is `mysite.cloudflareaccess.com`, the Team Name is `mysite`.

Example for `wp-config.php`: `define( 'CF_ACCESS_TEAM_NAME', 'mysite' );`

`CF_ACCESS_AUD` The Application Audience (AUD) Tag for the Cloudflare Access application
To get the Application Audience (AUD) Tag
- Open the Zero Trust Dashboard (see above)
- Navigate to Access => Applications
- Select the Application, then click "Configure" in the overlaid modal
- On the application page, navigate to the "Overview" tab
- Copy the "Application Audience (AUD) Tag" value

Example for `wp-config.php`: `define( 'CF_ACCESS_AUD', '12345-67890-12345-67890-12345-67890' );`

> Note: If you have multiple Cloudflare Access Applications, ensure the AUD covers `wp-login.php` - if it doesn't, SSO will not function correctly.

`CF_ACCESS_AUD` accepts a single string (per example above) or an array of strings, like this:

```
define( 'CF_ACCESS_AUD',
	[
		'12345-67890-12345-67890-12345-67890',
		'54321-12345-54321-12345-54321-12345',
	]
);
```

Optionally, four additional constants can also be set:

`CF_ACCESS_ATTEMPTS` The number of attempts to login via Cloudflare Access.

Default: (int) `3` if not set.

`CF_ACCESS_LEEWAY` The number of seconds leeway allowed in the authorisation headers.

Default (int) `60` if not set.

`CF_ACCESS_CREATE_ACCOUNT` Whether an account should be created for a (Cloudflare) authenticated user if it doesn't exist
Note: This is dependent on the settings for your Cloudflare Access application; if you only allow "internal" users, "external" users won't be able to access the site at all.

Default: (bool) `false` if not set.

`CF_ACCESS_NEW_USER_ROLE` The role for user accounts created. Requires `CF_ACCESS_CREATE_ACCOUNT` to be true (is otherwise ignored).

Default: (string) `subscriber`

> **Note:** Where the application is not configured correctly (authorisation header is not set, or the team name / AUD are incorrect), SSO is **silently disabled**. You can check the cookies section of inspector tools to confirm whether the cookie has been set.

### Disclaimer
This plugin is not affiliated with nor developed by Cloudflare. All trademarks, service marks and company names are the property of their respective owners.
