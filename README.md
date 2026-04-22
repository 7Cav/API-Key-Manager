# Cav7 API Key Manager

A XenForo 2.3+ add-on that allows users to generate and manage personal API keys for external API integrations.

## Features

- Per-user API key generation with secure hashed storage
- Key prefix display for easy identification
- Activate/deactivate keys without deleting them
- Usage tracking via `last_used_date`
- Read-only scope enforcement
- Admin panel for viewing and revoking all keys

## Requirements

- XenForo 2.3.0 or later
- PHP 8.0+

## Installation

1. Upload the contents of the `upload/` directory to your XenForo root.
2. In the Admin Control Panel, go to **Add-ons** and install `Cav7/ApiKeyManager`.
3. The installer will create the required `xf_cav7_api_key` database table automatically.

## Usage

### Users
Users can generate and manage their API key from their **Account** page. Only one key is allowed per user. Keys can be toggled active/inactive at any time.

### Admins
Admins can view and revoke any user's API key via the ACP at **Admin > Cav7 API Keys**.

### API Authentication
Include the API key in requests to authenticate. Keys are validated directly against the database.

This add-on is specifically designed to work with the [7Cav API](https://github.com/7Cav/api).

## License

MIT License — see [LICENSE.md](LICENSE.md) for details.

Copyright (c) 2026 7Cav
