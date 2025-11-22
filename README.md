# Filament Chatflow

[![Latest Version on Packagist](https://img.shields.io/packagist/v/syofyanzuhad/filament-chatflow.svg?style=flat-square)](https://packagist.org/packages/syofyanzuhad/filament-chatflow)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/syofyanzuhad/filament-chatflow/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/syofyanzuhad/filament-chatflow/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/syofyanzuhad/filament-chatflow/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/syofyanzuhad/filament-chatflow/actions?query=workflow%3A"Fix+PHP+code+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/syofyanzuhad/filament-chatflow.svg?style=flat-square)](https://packagist.org/packages/syofyanzuhad/filament-chatflow)

A powerful conversational flow builder plugin for Filament v3. Create interactive chatbot experiences with conditional logic, multi-language support, and comprehensive analytics.

## Features

- 🎨 **Visual Flow Builder** - Build chat flows with an intuitive form-based interface
- 💬 **Floating Chat Widget** - Beautiful, responsive chat widget that works on all pages
- 🌍 **Multi-Language Support** - Built-in support for multiple languages (English & Indonesian included)
- 📊 **Analytics Dashboard** - Track conversation metrics, completion rates, and drop-off points
- 📧 **Email Transcripts** - Automatically send conversation transcripts via email
- 🎯 **Conditional Logic** - Create complex conversation flows with if-else branching
- 🔔 **Sound Notifications** - Optional sound notifications for new messages
- 🌙 **Dark Mode Support** - Full dark mode compatibility
- ⚡ **Rate Limiting** - Built-in API rate limiting for security
- 🧪 **Comprehensive Testing** - Full test coverage with Pest v4

## Requirements

- PHP 8.3 or higher
- Laravel 12.x
- Filament 3.x
- Livewire 3.x

## Installation

1. Install the package via composer:

```bash
composer require syofyanzuhad/filament-chatflow
```

2. Run the installation command:

```bash
php artisan filament-chatflow:install
```

This will publish the config file, migrations, and ask to run migrations.

Alternatively, you can publish and run the migrations manually:

```bash
php artisan vendor:publish --tag="filament-chatflow-migrations"
php artisan migrate
```

3. Register the plugin in your Filament Panel Provider:

```php
use Syofyanzuhad\FilamentChatflow\FilamentChatflowPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([
            FilamentChatflowPlugin::make(),
        ]);
}
```

## Configuration

Publish the config file (if not already published):

```bash
php artisan vendor:publish --tag="filament-chatflow-config"
```

The config file contains options for:
- Widget appearance and behavior
- Conversation expiration settings
- Email transcript configuration
- Analytics settings
- Rate limiting
- Security options
- And more...

## Usage

### Creating a Chatflow

1. Navigate to **Chatflow** in your Filament panel
2. Click **New Chatflow**
3. Fill in basic information:
   - Name
   - Description
   - Welcome message (multi-language supported)
   - Widget position (bottom-right, bottom-left, top-right, top-left)
   - Theme color
   - Email and notification settings

4. Click **Save** and you'll be redirected to the Flow Builder

### Building the Conversation Flow

The Flow Builder allows you to create conversation steps:

1. **Message Steps** - Display information to users
2. **Question Steps** - Present options for users to choose
3. **End Steps** - Conclude the conversation

Each step supports:
- Multi-language content (English & Indonesian by default)
- Nested child steps for complex flows
- Quick reply options with branching logic

Example flow structure:
```
Welcome Message
└── Question: "How can we help you?"
    ├── Option 1: "Product Inquiry"
    │   └── Message: "Visit our catalog..."
    ├── Option 2: "Technical Support"
    │   └── Question: "What issue?"
    │       ├── "Login Issue" → Solution steps
    │       └── "Performance" → Solution steps
    └── Option 3: "Other"
        └── Message: "Contact us..."
```

### Adding the Chat Widget to Your Frontend

Add the chat widget to your layout blade file:

```blade
<body>
    <!-- Your content -->

    @livewire('chatflow-widget', ['chatflow' => App\Models\Chatflow::find(1)])
</body>
```

Or use the chatflow ID directly:

```blade
@livewire('chatflow-widget', ['chatflow' => $chatflow])
```

### Viewing Conversations

Navigate to **Conversations** in your Filament panel to:
- View all conversation history
- Filter by chatflow, status, date range, or language
- See detailed message timelines
- Track user information and metadata

### Analytics Dashboard

The Analytics widget on your dashboard shows:
- Total conversations
- Active conversations
- Completed today
- Average completion rate

Access detailed analytics in the Chatflow Resource to see:
- Completion rates over time
- Drop-off points (where users abandon)
- Popular conversation paths
- Hourly distribution

### API Endpoints

The package provides API endpoints for programmatic access:

```php
// Get chatflow configuration
GET /api/chatflow/{chatflow}/config

// Start a conversation
POST /api/chatflow/{chatflow}/start

// Send a message
POST /api/chatflow/message

// End a conversation
POST /api/chatflow/end

// Get conversation history
GET /api/chatflow/history
```

### Seeding Sample Data

Run the seeder to create a sample customer support chatflow:

```bash
php artisan db:seed --class="Syofyanzuhad\\FilamentChatflow\\Database\\Seeders\\ChatflowSeeder"
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Syofyan Zuhad](https://github.com/syofyanzuhad)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
