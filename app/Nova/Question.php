<?php

namespace App\Nova;

use App\Enums\QuestionStatus;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Badge;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;

class Question extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Question>
     */
    public static $model = \App\Models\Question::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'body';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'body',
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @return array<int, \Laravel\Nova\Fields\Field>
     */
    public function fields(NovaRequest $request): array
    {
        return [
            Text::make('Question body', 'body')
                ->sortable()
                ->rules('required', 'max:255')
                ->displayUsing(function ($text) {
                    return \Illuminate\Support\Str::limit($text, 80);
                }),

            Badge::make('Status', 'status')
                ->map([
                    QuestionStatus::Active->value => 'success',
                    QuestionStatus::Buzzed->value => 'warning',
                    QuestionStatus::Closed->value => 'danger',
                    'pending' => 'info',
                ])
                ->sortable(),

            BelongsTo::make('Winner', 'winner', User::class)
                ->sortable()
                ->nullable()
                ->searchable()
                ->exceptOnForms(),

            Text::make('Answer', 'answer')
                ->onlyOnDetail(),

            DateTime::make('Created At', 'created_at')
                ->sortable()
                ->exceptOnForms(),
        ];
    }

    /**
     * Get the cards available for the resource.
     *
     * @return array<int, \Laravel\Nova\Card>
     */
    public function cards(NovaRequest $request): array
    {
        return [
            new Metrics\TotalQuestions,
            new Metrics\WonQuestions,
        ];
    }

    /**
     * Get the filters available for the resource.
     *
     * @return array<int, \Laravel\Nova\Filters\Filter>
     */
    public function filters(NovaRequest $request): array
    {
        return [
            new \App\Nova\Filters\WonBy,
        ];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @return array<int, \Laravel\Nova\Lenses\Lens>
     */
    public function lenses(NovaRequest $request): array
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @return array<int, \Laravel\Nova\Actions\Action>
     */
    public function actions(NovaRequest $request): array
    {
        return [];
    }

    public static function authorizedToCreate(Request $request)
    {
        return false;
    }

    public function authorizedToUpdate(Request $request)
    {
        return false;
    }

    public function authorizedToDelete(Request $request)
    {
        return false;
    }

    public function authorizedToReplicate(Request $request)
    {
        return false;
    }
}
