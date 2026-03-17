<?php

namespace App\Filament\Resources\VotacionCandidatoResource\Pages;

use App\Filament\Resources\VotacionCandidatoResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageVotacionCandidatos extends ManageRecords
{
    protected static string $resource = VotacionCandidatoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
