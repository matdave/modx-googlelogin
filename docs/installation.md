# Installation and Setup

## Requirements

- MODX Revolution 3.0.0 or later
- PHP 7.4 or later
- A valid Google Cloud Platform project

## Installation

This package is installable via the MODX Revolution package manager.

## Setup

### Google Cloud Platform

1. Create a new project in the [Google Cloud Platform Console](https://console.cloud.google.com/apis/dashboard).
2. Click on the Oauth consent screen.
3. If you are only using this within your organization, select Internal. If you are using this for a website with multiple users on different domains, select External.
4. Fill in the required fields and save.
  - **App name**: The name of your app.
  - **User support email**: The email address of the person responsible for the app.
  - **App logo**: The logo of your app (optional)
  - **Application home page**: The URL of your website.
  - **Application privacy policy link**: The URL of your privacy policy.
  - **Application terms of service link**: The URL of your terms of service.
  - **Authorized domains**: The domain of your website(s). Make sure to include any staging or test domains.
  - **Developer contact information**: The email address of the person responsible for the app.
5. Scopes can be left empty.
6. (if you selected External) Add test users.
7. Click on Credentials in the left menu.
8. Click on Create Credentials and select OAuth client ID.
9. Select Web application.
10. Fill in the required fields and save.
  - **Name**: The name of your app.
  - **Authorized redirect URIs**: The URL of your website(s) followed by `/assets/components/googlelogin/callback.php`. For example: `https://example.com/assets/components/googlelogin/callback.php`.
11. Save the client ID and client secret for the next step.

### MODX

1. Go to the System Settings in the MODX manager.
2. Choose the namespace `googlelogin`.
3. Fill in the client ID and client secret from the Google Cloud Platform.

Additional useage instructions can be found in the [usage guide](usage.md).