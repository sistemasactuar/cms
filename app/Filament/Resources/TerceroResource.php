<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TerceroResource\Pages;
use App\Filament\Resources\TerceroResource\RelationManagers;
use App\Models\Tercero;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TerceroResource extends Resource
{
    protected static ?string $model = Tercero::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Integraciones ERP';
    protected static ?string $navigationLabel = 'Terceros';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
            return $form->schema([
                Forms\Components\Card::make('Información personal')->schema([
                    Forms\Components\TextInput::make('nombre_tercero')
                        ->label('Nombre completo')
                        ->required(),

                    Forms\Components\TextInput::make('tipo_id')
                        ->label('Tipo de ID'),

                    Forms\Components\TextInput::make('digito_verificacion')
                        ->label('DV')
                        ->maxLength(2),

                    Forms\Components\Select::make('estado_asociado')
                        ->options([
                            'Activo' => 'Activo',
                            'Inactivo' => 'Inactivo',
                            'Retirado' => 'Retirado',
                        ])
                        ->label('Estado'),

                    Forms\Components\DatePicker::make('fecha_nacimiento')
                        ->label('Fecha de nacimiento'),

                    Forms\Components\TextInput::make('profesion')
                        ->label('Profesión'),
                ])->columns(2),

                Forms\Components\Card::make('Contacto')->schema([
                    Forms\Components\TextInput::make('direccion')
                        ->label('Dirección'),

                    Forms\Components\TextInput::make('barrio')
                        ->label('Barrio'),

                    Forms\Components\TextInput::make('telefono_fijo')
                        ->label('Teléfono'),

                    Forms\Components\TextInput::make('celular')
                        ->label('Celular'),

                    Forms\Components\TextInput::make('correo')
                        ->label('Correo electrónico')
                        ->email(),
                ])->columns(2),

                Forms\Components\Card::make('Laboral')->schema([
                    Forms\Components\DatePicker::make('fecha_ingreso_empresa')
                        ->label('Fecha de ingreso'),

                    Forms\Components\TextInput::make('tipo_contrato')
                        ->label('Tipo de contrato'),

                    Forms\Components\TextInput::make('sueldo_basico')
                        ->label('Sueldo básico')
                        ->numeric()
                        ->prefix('$'),

                    Forms\Components\TextInput::make('otros_ingresos_mes')
                        ->label('Otros ingresos/mes')
                        ->numeric()
                        ->prefix('$'),
                ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre_tercero')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tipo_id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('digito_verificacion')
                    ->searchable(),
                Tables\Columns\TextColumn::make('naturaleza')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sexo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('estado_civil')
                    ->searchable(),
                Tables\Columns\TextColumn::make('fecha_nacimiento')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nivel_educativo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('numero_hijos')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('numero_dependientes')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('direccion')
                    ->searchable(),
                Tables\Columns\TextColumn::make('barrio')
                    ->searchable(),
                Tables\Columns\TextColumn::make('telefono_fijo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('celular')
                    ->searchable(),
                Tables\Columns\TextColumn::make('correo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tipo_asociado')
                    ->searchable(),
                Tables\Columns\TextColumn::make('profesion')
                    ->searchable(),
                Tables\Columns\TextColumn::make('fecha_ingreso_empresa')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sueldo_basico')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('otros_ingresos_mes')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tipo_contrato')
                    ->searchable(),
                Tables\Columns\TextColumn::make('pais_nacimiento')
                    ->searchable(),
                Tables\Columns\TextColumn::make('departamento_nacimiento')
                    ->searchable(),
                Tables\Columns\TextColumn::make('ciudad_nacimiento')
                    ->searchable(),
                Tables\Columns\TextColumn::make('estado_asociado')
                    ->searchable(),
                Tables\Columns\TextColumn::make('fecha_ultima_actualizacion')
                    ->date()
                    ->sortable(),
                Tables\Columns\IconColumn::make('aut_notifi')
                    ->boolean(),
                Tables\Columns\IconColumn::make('aut_cons_cen_ries')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListTerceros::route('/'),
            'create' => Pages\CreateTercero::route('/create'),
            'edit' => Pages\EditTercero::route('/{record}/edit'),
        ];
    }
}
