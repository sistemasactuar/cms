<?php

namespace App\Filament\Resources\VotacionResource\Pages;

use App\Filament\Resources\VotacionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVotacions extends ListRecords
{
    protected static string $resource = VotacionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
