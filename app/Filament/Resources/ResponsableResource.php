<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ResponsableResource\Pages;
use App\Filament\Resources\ResponsableResource\RelationManagers;
use App\Models\Responsable;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class ResponsableResource extends Resource
{
    protected static ?string $model = Responsable::class;

    protected static ?string $navigationGroup = 'Gestión de Proveedores';
    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static ?string $modelLabel = 'Responsable';
    protected static ?string $pluralModelLabel = 'Responsables';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre')
                    ->label('Nombre completo')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('telefono')
                    ->label('Teléfono')
                    ->tel()
                    ->maxLength(20),

                Forms\Components\TextInput::make('correo')
                    ->label('Correo')
                    ->email()
                    ->maxLength(255),

                Forms\Components\TextInput::make('clave_portal')
                    ->label('Clave de acceso al portal')
                    ->password()
                    ->required()
                    ->helperText('El responsable deberá ingresar esta clave para acceder a su portal de evaluaciones.'),

                Forms\Components\TextInput::make('token_publico')
                    ->label('Token público (solo lectura)')
                    ->default(fn() => (string) Str::uuid())
                    ->disabled()
                    ->dehydrated(true),

                Forms\Components\Placeholder::make('link_publico')
                    ->label('Link público del responsable')
                    ->content(function ($record) {
                        if (!$record) return 'Se generará al crear el responsable.';
                        return url('/evaluador/' . $record->token_publico);
                    }),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')->searchable(),
                Tables\Columns\TextColumn::make('telefono'),
                Tables\Columns\TextColumn::make('correo')->searchable(),

                Tables\Columns\TextColumn::make('evaluaciones_count')
                    ->label('Evaluaciones asignadas')
                    ->counts('evaluaciones'),

                Tables\Columns\TextColumn::make('token_publico')
                    ->label('Link público')
                    ->formatStateUsing(fn($state) => url('/evaluador/' . $state))
                    ->copyable()
                    ->copyableState(fn($state) => url('/evaluador/' . $state)),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('whatsapp')
                    ->label('Enviar')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('success')
                    ->url(fn(Responsable $record) => 'https://wa.me/' . $record->telefono . '?text=' . urlencode("Hola {$record->nombre}, aquí tienes tu enlace de acceso para la evaluación de proveedores: " . url('/evaluador/' . $record->token_publico)))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListResponsables::route('/'),
            'create' => Pages\CreateResponsable::route('/create'),
            'edit' => Pages\EditResponsable::route('/{record}/edit'),
        ];
    }
}
