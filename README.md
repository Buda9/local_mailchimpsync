# MailChimp Sync for Moodle

This Moodle plugin automatically synchronizes user data between Moodle and MailChimp, enabling seamless integration of your Moodle user base with MailChimp mailing lists.

## Description

The MailChimp Sync plugin for Moodle automates the process of synchronizing user data from your Moodle installation to MailChimp mailing lists, allowing for efficient email marketing and communication with your Moodle users.

## Features

- Automatic synchronization of Moodle users to MailChimp lists
- Configurable field mapping between Moodle user fields and MailChimp merge fields
- Support for custom user profile fields
- Cohort to MailChimp list mapping
- Manual and scheduled synchronization options
- Detailed logging for troubleshooting

## Installation

1. Download the plugin and extract it to the `local` directory in your Moodle installation.
2. Rename the extracted folder to `mailchimpsync`.
3. Log in as an administrator and visit the notifications page to complete the installation.

## Configuration

1. Navigate to Site Administration > Plugins > Local plugins > MailChimp Sync.
2. Enter your MailChimp API key.
3. Configure the field mapping and other settings as needed.

## Usage

Once configured, the plugin will automatically synchronize users based on the schedule set in the Moodle cron job. You can also manually trigger synchronization from the admin interface.

## Requirements

- Moodle 4.1 or higher
- PHP 7.4 or higher
- Valid MailChimp API key

## Status

This plugin is currently in beta. While it is functional, it may contain bugs or incomplete features. Use in production environments at your own risk.

## Screenshots

Admin Panel Settings:

![Screenshot_199](https://github.com/user-attachments/assets/65d205a5-9abb-453b-82f4-36c3b0c4c1d6)

Settings Page:

![Screenshot_200](https://github.com/user-attachments/assets/52a73705-88cc-4c85-bc84-93fc790ff13e)

User Field Mapping:

![Screenshot_201](https://github.com/user-attachments/assets/cdf894a4-b5c2-47b3-9f13-2e84afd907af)

Manual Sync All Users:

![Screenshot_202](https://github.com/user-attachments/assets/2be21cf5-9932-453f-897b-12007276c56c)

Privacy Policy Custom Page:

![Screenshot_203](https://github.com/user-attachments/assets/42253550-208b-4191-972b-c1b82fe9d885)


## Contributing

Pull requests are welcome! For major changes, please open an issue first to discuss what you would like to change.

## License

[GNU GPL v3 or later](https://www.gnu.org/licenses/gpl-3.0.html)

## Support

For support, please open an issue in the GitHub repository or contact the maintainer.
