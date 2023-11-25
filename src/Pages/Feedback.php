<?php

namespace Orion\FilamentFeedback\Pages;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\HtmlString;
use Livewire\Attributes\Validate;
use Orion\FilamentFeedback\FeedbackPlugin;
use Orion\FilamentFeedback\Mail\FeedbackMail;

class Feedback extends Page
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-ellipsis';

    protected static string $view = 'feedback::pages.feedback';

    public ?string $name = null;

    public ?string $email = null;

    public ?bool $isAnonymous = false;

    #[Validate('required')]
    public ?string $type = 'feature';

    #[Validate('required', message: 'Please provide a description.')]
    public ?string $description = '';

    #[Validate('required_if:type,feature')]
    public ?string $reason = '';

    public ?string $additionalNotes = '';

    #[Validate('required_if:type,bug')]
    public ?string $expectedBehavior = '';

    #[Validate('required_if:type,bug')]
    public ?string $stepsToReproduce = '';

    #[Validate('required_if:type,other')]
    public ?string $feedbackNature = '';

    public ?array $checklist = [];

    public ?array $media = [];

    public function messages(): array
    {
        return [
            'reason.required' => 'Please provide a reason.',
            'expectedBehavior.required' => 'Please provide the expected behavior.',
            'stepsToReproduce.required' => 'Please provide steps to reproduce.',
            'feedbackNature.required' => 'Please provide the nature of feedback.',
        ];
    }

    public static function getNavigationLabel(): string
    {
        return FeedBackPlugin::get()->getLabel() ?? __('Feedback');
    }

    public static function getNavigationIcon(): ?string
    {
        return FeedBackPlugin::get()->getIcon();
    }

    public static function getNavigationGroup(): ?string
    {
        return FeedBackPlugin::get()->getGroup();
    }

    public static function getNavigationSort(): ?int
    {
        return FeedBackPlugin::get()->getSort();
    }

    public static function getSlug(): string
    {
        return FeedBackPlugin::get()->getSlug();
    }

    public function getTitle(): string | Htmlable
    {
        return __('Feedback');
    }

    public function mount(): void
    {
        $this->form->fill();
        $this->name = auth()->user()->name;
        $this->email = auth()->user()->email;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Wizard\Step::make('Type')
                        ->icon('heroicon-m-check-badge')
                        ->description('Select feedback type')
                        ->schema([
                            Radio::make('type')
                                ->hiddenLabel()
                                ->default($this->type)
                                ->options([
                                    'feature' => 'Feature Request',
                                    'bug' => 'Bug Report',
                                    'other' => 'Other',
                                ])
                                ->descriptions([
                                    'feature' => 'Submit a proposal for a new functionality or enhancement to existing features.',
                                    'bug' => "Inform us of any malfunctions, errors, or unexpected behaviors you've experienced.",
                                    'other' => 'Provide feedback or comments that do not fit into the aforementioned categories.',
                                ]),
                        ]),
                    Wizard\Step::make('Information')
                        ->icon('heroicon-m-light-bulb')
                        ->description('Provide information')
                        ->schema([
                            ...$this->getInformationSchema($this->type),
                        ]),
                    Wizard\Step::make('Review & Submit')
                        ->icon('heroicon-m-rocket-launch')
                        ->description('Submit your feedback')
                        ->columns()
                        ->schema($this->reviewSchema()),
                ])
                    ->lazy()
                    //->persistStepInQueryString()
                    ->submitAction(new HtmlString(Blade::render(
                        <<<'BLADE'
                            <x-filament::button
                                type="submit"
                                size="md"
                            >
                                Submit
                            </x-filament::button>
                        BLADE
                    ))),
            ]);
    }

    public function submit(): void
    {
        $validated = $this->validate();

        if (! $this->isAnonymous) {
            $validated['name'] = $this->name;
            $validated['email'] = $this->email;
        }

        Mail::to($this->plugin()->getEmail())->send(new FeedbackMail($validated));

        Notification::make()
            ->title('Received successfully. Thank you for your feedback!')
            ->success()
            ->send();

        $this->redirect($this::getUrl(), navigate: true);
    }

    public function getInformationSchema(string $type): array
    {
        return match ($type) {
            'feature' => $this->featureSchema(),
            'bug' => $this->bugSchema(),
            'other' => $this->otherSchema(),
        };
    }

    public function featureSchema(): array
    {
        return [
            Textarea::make('description')
                ->label('Feature Description')
                ->rows(6)
                ->placeholder('Ex: I would like a functionality where I can bulk update stock quantities for multiple products at once.')
                ->required(),
            Textarea::make('reason')
                ->label('Reason for Feature')
                ->rows(6)
                ->placeholder('Ex: This would streamline the inventory management process and save time, especially when receiving large shipments.')
                ->required(),
            FileUpload::make('media')
                ->label('Supporting Media')
                ->multiple()
                ->maxSize(2048)
                ->hint('Maximum file size: 2MB')
                ->helperText('Upload any mockups, diagrams, or examples that might help illustrate your feature request.'),
            Textarea::make('additionalNotes')
                ->label('Additional Notes')
                ->rows(6)
                ->placeholder('Ex: Ideally, there would also be an undo function in case of mistakes in the bulk update.'),
        ];
    }

    public function bugSchema(): array
    {
        return [
            Textarea::make('description')
                ->label('Problem Description')
                ->rows(6)
                ->placeholder('Ex: When attempting to update the stock quantity for a product, the application displays an error message that says update failed.')
                ->required(),
            Textarea::make('expectedBehavior')
                ->label('Expected Behavior')
                ->rows(6)
                ->placeholder('Ex: After entering the new stock quantity for a product and clicking the Update Stock button, the application should successfully update the stock count without any error messages.')
                ->required(),
            Textarea::make('stepsToReproduce')
                ->label('Steps to Reproduce')
                ->rows(12)
                ->placeholder('Ex:
1. Log into the application using appropriate credentials.
2. Navigate to the designated section.
3. Locate and select the desired item using relevant criteria.
4. Access the editing or update feature for the selected item.
5. Make the necessary changes or inputs.
6. Confirm or save changes by clicking the appropriate action button.
7. Observe any unexpected responses or error messages from the application.')
                ->required(),
            FileUpload::make('media')
                ->label('Supporting Media')
                ->image()
                ->multiple()
                ->maxSize(2048)
                ->hint('Maximum file size: 2MB')
                ->helperText('Kindly upload relevant screenshots or screen recordings'),
            // description of the issue, expected behavior, steps to reproduce
        ];
    }

    public function otherSchema(): array
    {
        return [
            Radio::make('feedbackNature')
                ->label('Nature of Feedback')
                ->options([
                    'suggestion' => 'Suggestion',
                    'praise' => 'Praise',
                    'concern' => 'Concern',
                    'comment' => 'General Comment',
                ])
                ->descriptions([
                    'suggestion' => 'Provide a suggestion for improving the application.',
                    'praise' => 'Share something you particularly liked about the application.',
                    'concern' => 'Raise any concerns or areas of discomfort while using the application.',
                    'comment' => 'Any general observations or remarks.',
                ])
                ->default('praise')
                ->required(),
            Textarea::make('description')
                ->label('Feedback Description')
                ->rows(6)
                ->placeholder('Ex: I find the user interface very intuitive and user-friendly. Great job!')
                ->required(),
            FileUpload::make('media')
                ->label('Supporting Media')
                ->image()
                ->multiple()
                ->maxSize(2048)
                ->hint('Maximum file size: 2MB')
                ->helperText('Upload any screenshots or examples to further illustrate your feedback, if applicable.'),
            Textarea::make('additionalNotes')
                ->label('Additional Notes')
                ->rows(6)
                ->placeholder('Ex: I would love to see more customization options in the dashboard.'),
        ];
    }

    public function reviewSchema(): array
    {
        $reviewComponents = [
            Placeholder::make('name')
                ->hidden(fn (Get $get) => $get('isAnonymous'))
                ->content(auth()->user()->name),
            Placeholder::make('email')
                ->hidden(fn (Get $get) => $get('isAnonymous'))
                ->content(auth()->user()->email),
            Placeholder::make('type')->content(ucfirst($this->type)),
            Placeholder::make('description')->content($this->description),
        ];

        // Add fields for 'feature' type feedback
        if ($this->type === 'feature') {
            $reviewComponents[] = Placeholder::make('reason')->content($this->reason);
        }

        // Add fields for 'bug' type feedback
        if ($this->type === 'bug') {
            $reviewComponents[] = Placeholder::make('expectedBehavior')->content($this->expectedBehavior);
            $reviewComponents[] = Placeholder::make('stepsToReproduce')->content($this->stepsToReproduce);
        }

        // Add fields for 'other' type feedback
        if ($this->type === 'other') {
            $reviewComponents[] = Placeholder::make('feedbackNature')
                ->label('Feedback Nature')
                ->content(ucfirst($this->feedbackNature));
        }

        // Add media information
        $reviewComponents[] = Placeholder::make('media')
            ->content($this->media ? 'Files attached' : 'No files attached');

        // Add additional notes if available
        if ($this->additionalNotes) {
            $reviewComponents[] = Placeholder::make('additionalNotes')
                ->content(fn (Get $get) => $get('additionalNotes'));
        }

        $checklistItems = $this->getChecklistForType($this->type);
        if ($checklistItems) {
            $reviewComponents[] = Fieldset::make('checkboxes')
                ->label('Before submitting, please confirm the following:')
                ->columns(1)
                ->schema([CheckboxList::make('checklist')
                    ->hiddenLabel()
                    ->required()
                    ->options($checklistItems),
                ]);
        }

        $reviewComponents[] = Toggle::make('isAnonymous')
            ->live()
            ->columnSpanFull()
            ->label('Submit anonymously');

        return $reviewComponents;
    }

    protected function getChecklistForType(string $type): array
    {
        $checklists = [
            'feature' => [
                // ...
            ],
            'bug' => [
                'I have cleared my browser cache and cookies.',
                'I tried replicating the issue on a different browser or device.',
            ],
            'other' => [
                // ...
            ],
        ];

        return $checklists[$type] ?? [];
    }

    public function plugin(): FeedbackPlugin
    {
        return FeedbackPlugin::get();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return FeedbackPlugin::get()->isVisible();
    }
}
