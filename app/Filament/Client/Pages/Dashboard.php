<?php

namespace App\Filament\Client\Pages;

class Dashboard extends \Filament\Pages\Dashboard
{
    public function getColumns(): int|array
    {
        return [
            'default' => 1,
            'md' => 2,
            '2xl' => 3,
        ];
    }
}
