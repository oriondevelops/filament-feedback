<?php

namespace Orion\FilamentFeedback;

use Closure;
use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Support\Concerns\EvaluatesClosures;
use Orion\FilamentFeedback\Pages\Feedback;

class FeedbackPlugin implements Plugin
{
    use EvaluatesClosures;

    protected bool | Closure $isHidden = false;

    protected bool | Closure $isVisible = true;

    protected ?string $navigationLabel = null;

    protected ?string $navigationIcon = 'heroicon-o-chat-bubble-bottom-center-text';

    protected ?string $navigationGroup = null;

    protected ?int $navigationSort = null;

    protected ?string $slug = 'feedback';

    protected ?string $email = null;

    protected string $page = Feedback::class;

    public function getId(): string
    {
        return 'feedback';
    }

    public function page(string $page): static
    {
        $this->page = $page;

        return $this;
    }

    public function hidden(bool | Closure $condition = true): static
    {
        $this->isHidden = $condition;

        return $this;
    }

    public function visible(bool | Closure $condition = true): static
    {
        $this->isVisible = $condition;

        return $this;
    }

    public function label(string $label = null): static
    {
        $this->navigationLabel = $label;

        return $this;
    }

    public function icon(string $icon = 'heroicon-o-chat-bubble-bottom-center-text'): static
    {
        $this->navigationIcon = $icon;

        return $this;
    }

    public function group(string $group = null): static
    {
        $this->navigationGroup = $group;

        return $this;
    }

    public function sort(int $sort = null): static
    {
        $this->navigationSort = $sort;

        return $this;
    }

    public function slug(string $slug = null): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function sendResponsesTo(string $email = null): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPage(): string
    {
        return $this->page;
    }

    public function getLabel(): ?string
    {
        return $this->navigationLabel;
    }

    public function getIcon(): ?string
    {
        return $this->navigationIcon;
    }

    public function getGroup(): ?string
    {
        return $this->navigationGroup;
    }

    public function getSort(): ?int
    {
        return $this->navigationSort;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function isHidden(): bool
    {
        if ($this->evaluate($this->isHidden)) {
            return true;
        }

        return ! $this->evaluate($this->isVisible);
    }

    public function isVisible(): bool
    {
        return ! $this->isHidden();
    }

    public function register(Panel $panel): void
    {
        $panel->pages([$this->getPage()]);
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }
}
