# Usage

The usage of the Google Login extra can be configured in the System Settings of MODX.

## System Settings

### `googlelogin.client_id`
The client ID from the Google Cloud Platform.

### `googlelogin.client_secret`
The client secret from the Google Cloud Platform.

### `googlelogin.allow_match_by_email`
This allows users with the same email address to be matched to the same MODX user account. This is useful when a user has logged in with a username and password and then logs in with Google.

### `googlelogin.allow_signup`
This allows users to sign up with Google. If this is disabled, users will need to be created in the MODX manager.

**(Warning)** This allows anyone with a Google account to signup for a manager account. Do not enable this unless you have reviewed and configured additional settings below.

### `googlelogin.allow_signup_domains`
A comma separated list of domains that are allowed to sign up. If this is empty, any domain is allowed.

### `googlelogin.allow_signup_notify`
A comma separated list of email addresses that will be notified when a user signs up.

### `googlelogin.default_group`
The name of the user group that new users will be added to.

### `google.default_role`
The name of the role that new users will be added to. E.g. `Member`.

### `googlelogin.disable_regular_login`
This disables the regular username and password login. This is useful when you only want users to log in with Google.