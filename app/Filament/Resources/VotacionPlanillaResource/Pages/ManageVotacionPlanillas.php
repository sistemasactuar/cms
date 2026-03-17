<?php

namespace App\Filament\Resources\VotacionPlanillaResource\Pages;

use App\Filament\Resources\VotacionPlanillaResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageVotacionPlanillas extends ManageRecords
{
    protected static string $resource = VotacionPlanillaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
