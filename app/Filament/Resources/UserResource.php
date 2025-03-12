<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Pages\page;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\EditAction;
use Filament\Support\View\Components\Modal;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationGroup = 'Administrador';

    protected static ?string $navigationLabel = 'Usuarios';

    public static function getModelLabel(): string
    {
        return 'Usuario'; // Singular
    }

    public static function getPluralModelLabel(): string
    {
        return 'Usuarios'; // Plural
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()->schema([
                    Select::make('tipoDocumento')
                        ->label('Tipo de Documento')
                        ->options(
                            \App\Models\TipoDocumento::query()
                                ->pluck('tipdoc_desc', 'tipdoc_cod')
                                ->toArray()
                        )
                        ->placeholder('Selecciona un tipo de documento')
                        ->searchable()
                        ->required(),

                    TextInput::make('numeroDocumento')
                        ->label('Número de Documento')
                        ->required(),
                    TextInput::make('nombres')
                        ->label('Nombres')
                        ->required(),
                    TextInput::make('apellidos')
                        ->label('Apellidos')
                        ->required(),
                    TextInput::make('codigo')
                        ->label('Código del Funcionario')
                        ->required()
                        ->numeric(),

                    Forms\Components\TextInput::make('name')
                        ->label('Usuario')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('email')
                        ->email()
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true),
                    Forms\Components\TextInput::make('password')
                        ->password()
                        ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                        ->dehydrated(fn (?string $state): bool => filled($state))
                        ->required(fn (Page $livewire) => ($livewire instanceof CreateUser))
                        ->maxLength(255),
                    Forms\Components\Select::make('roles')
                        ->relationship(name: 'roles', titleAttribute: 'name')
                        ->searchable()
                        ->preload(),
                    Toggle::make('activo')
                        ->label('Activo')
                        ->default(true)
                        ->required()
                        ->inline(false)
                        ->visible(fn ($record) => $record?->activo === false),

                ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tipoDocumento')->label('Tipo de Documento')->sortable(),
                Tables\Columns\TextColumn::make('numeroDocumento')->label('Número de Documento')->searchable(),
                Tables\Columns\TextColumn::make('nombres')->label('Nombres')->searchable(),
                Tables\Columns\TextColumn::make('apellidos')->label('Apellidos')->searchable(),
                Tables\Columns\TextColumn::make('codigo')->label('Código del Funcionario')->sortable(),
                Tables\Columns\TextColumn::make('name')->label('Usuario')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha Creación')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Fecha Actualización')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\BooleanColumn::make('activo')
                    ->label('Activo'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Action::make('anular')
                    ->label('Inactivar')
                    ->icon('fluentui-document-dismiss-20-o')
                    ->modalButton('Cerrar')
                    ->slideOver()
                    ->form([
                        Textarea::make('motivo')
                            ->label('Observaciones de la Inactivación')
                            ->placeholder('Ingrese el motivo de la Inactivación')
                            ->required(),
                    ])
                    ->action(function (array $data, User $record): void {
                        // Actualiza el estado del usuario y registra el motivo
                        $record->update([
                            'activo' => 0, 
                        ]);
            
                        // Registra el motivo en la tabla `motivo_inactivacions`
                        DB::table('motivo_inactivacions')->insert([
                            'idusuario' => $record->id,
                            'descripcion' => $data['motivo'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Confirmar Inactivación')
                    ->modalSubheading('¿Está seguro de que desea inactivar este usuario?')
                    ->modalSubmitActionLabel('Si, inactivar')
                    ->modalAutofocus(false),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    //Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
