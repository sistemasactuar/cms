<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ObligacionResource\Pages;
use App\Models\Obligacion;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;

class ObligacionResource extends Resource
{
    protected static ?string $model = Obligacion::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationGroup = 'ERP';
    protected static ?string $navigationLabel = 'Obligaciones';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Card::make('Información Obligación')->schema([
                    TextInput::make('Obligacion')->required()->maxLength(20),
                    TextInput::make('Cedula_Cliente')->required()->maxLength(20),
                    TextInput::make('Cedula_Prom')->required()->maxLength(20),
                    TextInput::make('Calificacion')->maxLength(10),
                    TextInput::make('Monto')->numeric(),
                    TextInput::make('Saldo_Actual')->numeric(),
                    TextInput::make('Saldo_total')->numeric(),
                    TextInput::make('Estado')->maxLength(20),
                    TextInput::make('Tipo_Obl')->required()->maxLength(20),
                    TextInput::make('Monto_Solicitado')->numeric(),
                    TextInput::make('CA_Valor_Cuota')->numeric(),
                    TextInput::make('CA_Valor_Vencido_Capitalizacion')->numeric(),
                    TextInput::make('CA_Valor_Vencido_Capital')->numeric(),
                    TextInput::make('CA_Valor_Vencido_Interes')->numeric(),
                    TextInput::make('CA_Valor_Vencido_Mora')->numeric(),
                    TextInput::make('CA_Valor_Vencido_Seg_Vida')->numeric(),
                    TextInput::make('CA_Valor_Vencido_Seg_Patrimonial')->numeric(),
                    TextInput::make('CA_Valor_Vencido_Otros_Conceptos')->numeric(),
                    TextInput::make('CA_Valor_Proximo_Vencimiento')->numeric(),
                    DatePicker::make('CA_Fecha_Cuota'),
                    TextInput::make('CA_Dias_Vencidos')->numeric(),
                    TextInput::make('CA_Saldo_Capital')->numeric(),
                    TextInput::make('CA_Codigo_Modalidad')->maxLength(20),
                    TextInput::make('Linea_de_credito')->maxLength(50),
                    TextInput::make('Destinacion')->maxLength(100),
                    TextInput::make('Medio_de_pago')->maxLength(50),
                    TextInput::make('Medio_de_pago_Obligacion')->maxLength(50),
                    TextInput::make('Sld_Aportes')->numeric(),
                    TextInput::make('Tasa_Col_NAMV')->numeric(),
                    TextInput::make('Tasa_Periodo_NAMV')->numeric(),
                    DatePicker::make('Fec_Aprobacion'),
                    DatePicker::make('Fec_Prestamo'),
                    DatePicker::make('Fec_Liquidacion'),
                    TextInput::make('No_Cuotas')->numeric(),
                    TextInput::make('Periodicidad')->maxLength(20),
                    DatePicker::make('Fec_Inicio'),
                    DatePicker::make('Fec_Vcto_Final'),
                    TextInput::make('Altura')->maxLength(20),
                    DatePicker::make('Fec_Vencto'),
                    TextInput::make('Calif_Antes_Ley_Arrast')->maxLength(10),
                    TextInput::make('Calif_Aplicada')->maxLength(10),
                    TextInput::make('Calif_Mes_Ant')->maxLength(10),
                    TextInput::make('Int_Cte_Orden')->numeric(),
                    TextInput::make('Int_Mora_Orden')->numeric(),
                    DatePicker::make('Fec_Historico'),
                    TextInput::make('Forma_de_Pago')->maxLength(50),
                    DatePicker::make('Fec_Ult_Pago'),
                    TextInput::make('Cuenta_Contable')->maxLength(50),
                    TextInput::make('Suc_Credito')->maxLength(20),
                    TextInput::make('Dias_linix')->numeric(),
                    TextInput::make('Dias_actuar')->numeric(),
                    TextInput::make('Vencido_linix')->numeric(),
                    TextInput::make('Vencido_77')->numeric(),
                    TextInput::make('Vencido_Actuar')->numeric(),
                    TextInput::make('Dias_vencidos_Int')->numeric(),
                    TextInput::make('C_Costo_Obl')->maxLength(20),
                    TextInput::make('Instan_Aprob')->maxLength(20),
                    TextInput::make('Scoring')->numeric(),
                    DatePicker::make('Fecha_restructuracion'),
                    TextInput::make('usuario_edita')->numeric(),
                ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('Obligacion')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('Cedula_Cliente')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('Tipo_Obl'),
                Tables\Columns\TextColumn::make('Estado'),
                Tables\Columns\TextColumn::make('Monto')->money('COP'),
                Tables\Columns\TextColumn::make('Saldo_Actual')->money('COP'),
                Tables\Columns\TextColumn::make('Saldo_total')->money('COP'),
                Tables\Columns\TextColumn::make('Fec_Aprobacion')->date(),
                Tables\Columns\TextColumn::make('Fec_Prestamo')->date(),
                Tables\Columns\TextColumn::make('Fec_Liquidacion')->date(),
                Tables\Columns\TextColumn::make('Fec_Vencto')->date(),
                Tables\Columns\TextColumn::make('Fec_Ult_Pago')->date(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([
                Action::make('Cargar nuevas obligaciones')
                    ->icon('heroicon-o-document-arrow-up')
                    ->action(fn () => redirect()->route('import.excel.obligaciones')),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListObligacions::route('/'),
            'create' => Pages\CreateObligacion::route('/create'),
            'edit' => Pages\EditObligacion::route('/{record}/edit'),
        ];
    }
}
