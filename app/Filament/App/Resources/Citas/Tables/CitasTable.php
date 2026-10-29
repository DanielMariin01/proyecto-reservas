<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\Citas\Tables;

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
use App\Models\Profesional;
use Carbon\CarbonImmutable;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Throwable;

class CitasTable
{
    public static function configure(Table $table): Table
    {
        $negocio = Negocio::actual();

        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => self::aplicarAlcanceDelUsuario($query))
            ->defaultSort('inicio', 'desc')
            ->columns([
                TextColumn::make('inicio')->label('Fecha y hora')->dateTime('d/m/Y H:i', $negocio->zona_horaria)->sortable(),
                TextColumn::make('profesional.nombre')->label('Profesional')->sortable(),
                TextColumn::make('servicio.nombre')->label('Servicio'),
                TextColumn::make('cliente.nombre')->label('Cliente')->searchable(),
                TextColumn::make('estado')->badge(),
                TextColumn::make('origen'),
            ])
            ->filters([
                SelectFilter::make('profesional_id')
                    ->label('Profesional')
                    ->options(fn () => Profesional::query()->where('negocio_id', $negocio->id)->pluck('nombre', 'id')),
                SelectFilter::make('estado')
                    ->options(fn () => Collection::make(EstadoCitaValor::cases())->mapWithKeys(fn (EstadoCitaValor $estado): array => [$estado->value => $estado->etiqueta()])),
            ])
            ->recordActions([
                self::accionCompletarConPago(),
                self::accionCambiarEstado(),
                self::accionReprogramar(),
                self::accionCancelar(),
                self::accionAnularPago(),
            ]);
    }

    /**
     * CU-08: la única puerta para pasar una cita a "completada" es esta,
     * para que no se pueda saltar el registro del pago usando el cambio de
     * estado genérico (por eso destinosPosibles() excluye Completada).
     */
    private static function accionCompletarConPago(): Action
    {
        return Action::make('completarConPago')
            ->label('Completar y registrar pago')
            ->icon(Heroicon::OutlinedCheckCircle)
            ->color('success')
            ->visible(fn (Cita $record): bool => self::puedeGestionar($record)
                && $record->obtenerEstado()->puedeTransicionarA(EstadoCitaValor::Completada)
                && Filament::auth()->user()?->can('create', [Pago::class, $record]))
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
                    $usuario = Filament::auth()->user();
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

    private static function accionAnularPago(): Action
    {
        return Action::make('anularPago')
            ->label('Anular pago')
            ->color('danger')
            ->icon(Heroicon::OutlinedXCircle)
            ->requiresConfirmation()
            ->visible(fn (Cita $record): bool => $record->pagoActivo() !== null
                && Filament::auth()->user()?->can('anular', $record->pagoActivo()))
            ->schema([
                Textarea::make('motivo')->label('Motivo')->required(),
            ])
            ->action(function (Cita $record, array $data): void {
                self::ejecutarOFallar(function () use ($record, $data): void {
                    $pago = $record->pagoActivo();
                    abort_if($pago === null, 404);
                    app(AnularPago::class)->ejecutar($pago, $data['motivo'], Filament::auth()->user());
                }, 'Pago anulado');
            });
    }

    private static function accionCambiarEstado(): Action
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
                    app(CambiarEstadoCita::class)->ejecutar($record, EstadoCitaValor::from($data['estado']), Filament::auth()->user());
                }, 'Estado actualizado');
            });
    }

    private static function accionCancelar(): Action
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
                    app(CancelarCita::class)->ejecutar($record, $data['motivo'], Filament::auth()->user());
                }, 'Cita cancelada');
            });
    }

    private static function accionReprogramar(): Action
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
                        reprogramadaPor: Filament::auth()->user(),
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

    /**
     * CU-04: un profesional sin el permiso de gestionar todas las citas
     * solo ve las suyas.
     *
     * @param  Builder<Cita>  $query
     * @return Builder<Cita>
     */
    private static function aplicarAlcanceDelUsuario(Builder $query): Builder
    {
        $usuario = Filament::auth()->user();

        if ($usuario !== null && ($usuario->es_super_admin || $usuario->can(Permiso::GestionarCitasTodas->value))) {
            return $query;
        }

        $profesional = $usuario?->profesional;
        $profesionalId = $profesional === null ? 0 : $profesional->id;

        return $query->where('profesional_id', $profesionalId);
    }

    private static function puedeGestionar(Cita $cita): bool
    {
        $usuario = Filament::auth()->user();

        return $usuario !== null && $usuario->can('update', $cita);
    }

    private static function puedeForzar(): bool
    {
        $usuario = Filament::auth()->user();

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
