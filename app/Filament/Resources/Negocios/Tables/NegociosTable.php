<?php

declare(strict_types=1);

namespace App\Filament\Resources\Negocios\Tables;

use App\Actions\CambiarEstadoNegocio;
use App\Actions\RegistrarPagoSuscripcion;
use App\Actions\SuplantarNegocio;
use App\DatosTransferencia\DatosCambioEstadoNegocio;
use App\DatosTransferencia\DatosPagoSuscripcion;
use App\Enums\EstadoSuscripcion;
use App\Models\Negocio;
use App\Models\Usuario;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;

class NegociosTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')->searchable()->sortable(),
                TextColumn::make('slug')->searchable(),
                TextColumn::make('tipo')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state?->etiqueta()),
                TextColumn::make('estado_suscripcion')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state?->etiqueta())
                    ->color(fn (EstadoSuscripcion $state) => match ($state) {
                        EstadoSuscripcion::Activo => 'success',
                        EstadoSuscripcion::Vencido => 'warning',
                        EstadoSuscripcion::Suspendido, EstadoSuscripcion::Cancelado => 'danger',
                    }),
                TextColumn::make('proxima_fecha_pago')->date('d/m/Y')->sortable(),
            ])
            ->filters([])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                self::accionEntrarComoNegocio(),
                self::accionRegistrarPago(),
                self::accionCambiarEstado(),
            ])
            ->toolbarActions([]);
    }

    private static function accionEntrarComoNegocio(): Action
    {
        return Action::make('entrarComoNegocio')
            ->label('Entrar como el negocio')
            ->icon('heroicon-o-arrow-right-end-on-rectangle')
            ->action(function (Negocio $negocio) {
                /** @var Usuario $superAdmin */
                $superAdmin = Filament::auth()->user();

                app(SuplantarNegocio::class)->ejecutar($negocio, $superAdmin);

                $url = Filament::getPanel('app')->getUrl($negocio);

                abort_if($url === null, 500, 'No se pudo generar la URL del panel del negocio.');

                return redirect()->to($url);
            });
    }

    private static function accionRegistrarPago(): Action
    {
        return Action::make('registrarPago')
            ->label('Registrar pago')
            ->icon('heroicon-o-banknotes')
            ->schema([
                DatePicker::make('fecha')->required()->default(now()),
                TextInput::make('valor')->numeric()->required()->prefix('$'),
                Select::make('metodo')
                    ->options([
                        'efectivo' => 'Efectivo',
                        'nequi' => 'Nequi',
                        'daviplata' => 'Daviplata',
                        'transferencia' => 'Transferencia',
                        'tarjeta' => 'Tarjeta',
                    ])
                    ->required(),
                DatePicker::make('periodo_desde')->required()->default(now()),
                DatePicker::make('periodo_hasta')->required()->default(now()->addMonth()),
            ])
            ->action(function (Negocio $negocio, array $data): void {
                /** @var Usuario $usuario */
                $usuario = Filament::auth()->user();

                app(RegistrarPagoSuscripcion::class)->ejecutar(new DatosPagoSuscripcion(
                    negocio: $negocio,
                    fecha: Carbon::parse($data['fecha']),
                    valor: (int) $data['valor'],
                    metodo: $data['metodo'],
                    periodoDesde: Carbon::parse($data['periodo_desde']),
                    periodoHasta: Carbon::parse($data['periodo_hasta']),
                    registradoPor: $usuario,
                ));

                Notification::make()->title('Pago registrado')->success()->send();
            });
    }

    private static function accionCambiarEstado(): Action
    {
        return Action::make('cambiarEstado')
            ->label('Suspender / cancelar / reactivar')
            ->icon('heroicon-o-arrow-path')
            ->modalWidth(Width::Medium)
            ->schema([
                Select::make('estadoDestino')
                    ->label('Nuevo estado')
                    ->options([
                        EstadoSuscripcion::Activo->value => 'Reactivar',
                        EstadoSuscripcion::Suspendido->value => 'Suspender',
                        EstadoSuscripcion::Cancelado->value => 'Cancelar',
                    ])
                    ->required(),
                Textarea::make('motivo')
                    ->label('Motivo')
                    ->required(),
            ])
            ->action(function (Negocio $negocio, array $data): void {
                /** @var Usuario $usuario */
                $usuario = Filament::auth()->user();

                app(CambiarEstadoNegocio::class)->ejecutar(new DatosCambioEstadoNegocio(
                    negocio: $negocio,
                    estadoDestino: EstadoSuscripcion::from($data['estadoDestino']),
                    motivo: $data['motivo'],
                    cambiadoPor: $usuario,
                ));

                Notification::make()->title('Estado del negocio actualizado')->success()->send();
            });
    }
}
