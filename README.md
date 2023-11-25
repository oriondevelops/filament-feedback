# A Filament plugin to collect feedback

[![Latest Version on Packagist](https://img.shields.io/packagist/v/oriondevelops/filament-feedback.svg?style=flat-square)](https://packagist.org/packages/oriondevelops/filament-feedback)
[![Total Downloads](https://img.shields.io/packagist/dt/oriondevelops/filament-feedback.svg?style=flat-square)](https://packagist.org/packages/oriondevelops/filament-feedback)

This Filament plugin is a simple wizard that collects user feedback and forwards it to the chosen email address.

## Installation

You can install the package via composer:

```bash
composer require oriondevelops/filament-feedback
```

Next ensure you setup [a mail service](https://laravel.com/docs/master/mail).

## Usage

You need to register the plugin with your preferred Filament panel providers. This can be done inside of your `PanelProvider`, e.g. `AdminPanelProvider`.

```php
<?php

namespace App\Providers\Filament;

use Filament\Panel;
use Filament\PanelProvider;
use Orion\FilamentFeedback\FeedbackPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            // ...
            ->plugin(
                FeedbackPlugin::make()
                    ->sendResponsesTo(email: 'oriondevelops@gmail.com')
            );
    }
}
```

You can now click on the "Feedback" menu item in your Filament app to see the feedback plugin.

### Customizing visibility

Define who can view the feedback page.

```php
<?php

namespace App\Providers\Filament;

use Filament\Panel;
use Filament\PanelProvider;
use Orion\FilamentFeedback\FeedbackPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            // ...
            ->plugin(
                FeedbackPlugin::make()
                    ->visible(fn() => auth()->user()->can('view feedback page'))
            );
    }
}
```

### Customizing the navigation item

```php
<?php

namespace App\Providers\Filament;

use Filament\Panel;
use Filament\PanelProvider;
use Orion\FilamentFeedback\FeedbackPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            // ...
            ->plugin(
                FeedbackPlugin::make()
                    ->slug('feedback')
                    ->label('Feedback')
                    ->icon('heroicon-o-face-smile')
                    ->group('Help')
                    ->sort(3),
            );
    }
}
```

### Customizing the page

```php
<?php

namespace App\Providers\Filament;

use Filament\Panel;
use Filament\PanelProvider;
use Orion\FilamentFeedback\FeedbackPlugin;
use App\Filament\Pages\ExtendedFeedbackPage;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            // ...
            ->plugin(
                FeedbackPlugin::make()
                    ->page(ExtendedFeedbackPage::class),
            );
    }
}
```

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security

Please review [Security Policy](.github/SECURITY.md) on how to report security vulnerabilities.

## Credits

- [Mücahit Uğur](https://github.com/oriondevelops)
- [All Contributors](https://github.com/oriondevelops/filament-feedback/contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
