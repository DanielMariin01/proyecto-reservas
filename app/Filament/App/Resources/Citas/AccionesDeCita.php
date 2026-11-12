<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\Citas;

use App\Actions\AnularPago;
use App\Actions\CambiarEstadoCita;
use App\Actions\CancelarCita;
use App\Actions\RegistrarPagoDeCita;
use App\Actions\ReprogramarCita;
use App\DatosTransferencia\DatosReprogramacion;
use App\Enums\EstadoCitaValor;
use App\Enums\MetodoPago;
use App\Enums\Permiso;
use App\Models\Cita;
use App\Models\Negocio;
use App\Models\Pago;
use Carbon\CarbonImmutable;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Throwable;

/**
 * Las acciones de una Cita (completar con pago, cambiar estado,
 * reprogramar, cancelar, anular pago), para `CitasTable` (la tabla del
 * recurso, todavía en Filament). La Agenda del día y Mis citas de hoy ya
 * se migraron a Laravel + React (T27) y llaman los mismos Actions de
 * dominio directamente, vía `App\Services\ServicioAccionesCita` — cuando
 * se migre `CitaResource`, esta clase se puede borrar y esa lógica queda
 * centralizada ahí.
 */
final class AccionesDeCita
{
    /**
     * CU-08: la única puerta para pasar una cita a "completada" es esta,
     * para que no se pueda saltar el registro del pago usando el cambio de
     * estado genérico (por eso destinosPosibles() excluye Completada).
     */
    public static function completarConPago(): Action
    {
        return Action::make('completarConPago')
            ->label('Completar y registrar pago')
            ->icon(Heroicon::OutlinedCheckCircle)
            ->color('success')
            ->visible(fn (Cita $record): bool => self::puedeGestionar($record)
                && $record->obtenerEstado()->puedeTransicionarA(EstadoCitaValor::Completada)
                && Auth::guard('web')->user()?->can('create', [Pago::class, $record]))
            ->schema(fn (Cita $record): array => [
                Select::make('metodo')
                    ->label('Método de pago')
                    ->options(collect(MetodoPago::cases())->mapWithKeys(fn (MetodoPago $metodo): array => [$metodo->value => $metodo->etiqueta()]))
                    ->required(),
                TextInput::make('valor')
                    ->label('Valor')
                    ->numeric()
                    ->minValue(0)
                    ->required()
                    ->default($record->precio),
                TextInput::make('propina')
                    ->label('Propina (opcional)')
                    ->numeric()
                    ->minValue(0)
                    ->default(0),
            ])
            ->action(function (Cita $record, array $data): void {
                self::ejecutarOFallar(function () use ($record, $data): void {
                    $usuario = Auth::guard('web')->user();
                    app(CambiarEstadoCita::class)->ejecutar($record, EstadoCitaValor::Completada, $usuario);
                    app(RegistrarPagoDeCita::class)->ejecutar(
                        cita: $record,
                        metodo: MetodoPago::from($data['metodo']),
                        valor: (int) $data['valor'],
                        propina: (int) ($data['propina'] ?? 0),
                        registradoPor: $usuario,
                    );
                }, 'Cita completada y pago registrado');
            });
    }

    public static function anularPago(): Action
    {
        return Action::make('anularPago')
            ->label('Anular pago')
            ->color('danger')
            ->icon(Heroicon::OutlinedXCircle)
            ->requiresConfirmation()
            ->visible(fn (Cita $record): bool => $record->pagoActivo() !== null
                && Auth::guard('web')->user()?->can('anular', $record->pagoActivo()))
            ->schema([
                Textarea::make('motivo')->label('Motivo')->required(),
            ])
            ->action(function (Cita $record, array $data): void {
                self::ejecutarOFallar(function () use ($record, $data): void {
                    $pago = $record->pagoActivo();
                    abort_if($pago === null, 404);
                    app(AnularPago::class)->ejecutar($pago, $data['motivo'], Auth::guard('web')->user());
                }, 'Pago anulado');
            });
    }

    public static function cambiarEstado(): Action
    {
        return Action::make('cambiarEstado')
            ->label('Cambiar estado')
            ->icon(Heroicon::OutlinedArrowPath)
            ->color('gray')
            ->visible(fn (Cita $record): bool => self::puedeGestionar($record) && self::destinosPosibles($record) !== [])
            ->schema(fn (Cita $record): array => [
                Select::make('estado')
                    ->label('Nuevo estado')
                    ->options(collect(self::destinosPosibles($record))->mapWithKeys(fn (EstadoCitaValor $destino): array => [$destino->value => $destino->etiqueta()]))
                    ->required(),
            ])
            ->action(function (Cita $record, array $data): void {
                self::ejecutarOFallar(function () use ($record, $data): void {
                    app(CambiarEstadoCita::class)->ejecutar($record, EstadoCitaValor::from($data['estado']), Auth::guard('web')->user());
                }, 'Estado actualizado');
            });
    }

    public static function cancelar(): Action
    {
        return Action::make('cancelar')
            ->label('Cancelar')
            ->color('danger')
            ->icon(Heroicon::OutlinedXCircle)
            ->requiresConfirmation()
            ->visible(fn (Cita $record): bool => self::puedeGestionar($record) && ! $record->obtenerEstado()->esFinal())
            ->schema([
                Textarea::make('motivo')->label('Motivo')->required(),
            ])
            ->action(function (Cita $record, array $data): void {
                self::ejecutarOFallar(function () use ($record, $data): void {
                    app(CancelarCita::class)->ejecutar($record, $data['motivo'], Auth::guard('web')->user());
                }, 'Cita cancelada');
            });
    }

    public static function reprogramar(): Action
    {
        return Action::make('reprogramar')
            ->label('Reprogramar')
            ->icon(Heroicon::OutlinedCalendarDays)
            ->color('gray')
            ->visible(fn (Cita $record): bool => self::puedeGestionar($record) && $record->obtenerEstado()->puedeTransicionarA(EstadoCitaValor::Reprogramada))
            ->schema(fn (Cita $record): array => [
                DatePicker::make('fecha')
                    ->required()
                    ->default($record->inicio->setTimezone(Negocio::actual()->zona_horaria)->toDateString()),
                TimePicker::make('hora')
                    ->seconds(false)
                    ->required()
                    ->default($record->inicio->setTimezone(Negocio::actual()->zona_horaria)->format('H:i')),
                Toggle::make('forzar')
                    ->label('Forzar (ignorar disponibilidad)')
                    ->default(false)
                    ->visible(fn (): bool => self::puedeForzar()),
            ])
            ->action(function (Cita $record, array $data): void {
                self::ejecutarOFallar(function () use ($record, $data): void {
                    $negocio = Negocio::actual();
                    $nuevoInicio = CarbonImmutable::parse("{$data['fecha']} {$data['hora']}", $negocio->zona_horaria)->utc();

                    app(ReprogramarCita::class)->ejecutar(new DatosReprogramacion(
                        cita: $record,
                        nuevoInicio: $nuevoInicio,
                        reprogramadaPor: Auth::guard('web')->user(),
                        forzar: (bool) ($data['forzar'] ?? false),
                    ));
                }, 'Cita reprogramada');
            });
    }

    /**
     * @return list<EstadoCitaValor>
     */
    private static function destinosPosibles(Cita $cita): array
    {
        $estado = $cita->obtenerEstado();
        $destinos = [];

        foreach (EstadoCitaValor::cases() as $destino) {
            // Reprogramada se maneja con su propia acción; Completada exige
            // pasar por completarConPago() para no saltarse el registro del pago.
            if ($destino->value === EstadoCitaValor::Reprogramada->value || $destino->value === EstadoCitaValor::Completada->value) {
                continue;
            }

            if ($estado->puedeTransicionarA($destino)) {
                $destinos[] = $destino;
            }
        }

        return $destinos;
    }

    private static function puedeGestionar(Cita $cita): bool
    {
        $usuario = Auth::guard('web')->user();

        return $usuario !== null && $usuario->can('update', $cita);
    }

    private static function puedeForzar(): bool
    {
        $usuario = Auth::guard('web')->user();

        return $usuario !== null && ($usuario->es_super_admin || $usuario->can(Permiso::GestionarCitasTodas->value));
    }

    private static function ejecutarOFallar(callable $accion, string $mensajeExito): void
    {
        try {
            $accion();
        } catch (Throwable $excepcion) {
            Notification::make()->danger()->title($excepcion->getMessage())->send();

            throw new Halt;
        }

        Notification::make()->success()->title($mensajeExito)->send();
    }
}
