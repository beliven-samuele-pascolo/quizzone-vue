<?php

namespace App\Nova\Dashboards;

use App\Nova\Metrics;
use Laravel\Nova\Dashboards\Main as Dashboard;

class Main extends Dashboard
{
    /**
     * Get the cards for the dashboard.
     *
     * @return array<int, \Laravel\Nova\Card>
     */
    public function cards(): array
    {
        return [
            new Metrics\TotalPlayers,
            new Metrics\TotalQuestions,
            new Metrics\WonQuestions,
        ];
    }

    public function name()
    {
        return 'Dashboard';
    }
}
