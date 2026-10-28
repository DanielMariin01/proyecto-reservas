<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\Citas\Pages;

use App\Actions\CrearReserva;
use App\DatosTransferencia\DatosReserva;
use App\Enums\OrigenCita;
use App\Exceptions\HoraInvalidaException;
use App\Exceptions\HoraYaTomadaException;
use App\Filament\App\Resources\Citas\CitaResource;
use App\Models\Negocio;
use App\Models\Profesional;
use App\Models\Servicio;
use Carbon\CarbonImmutable;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;

class CreateCita extends CreateRecord
{
    protected static string $resource = CitaResource::class;

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordCreation(array $data): Model
    {
        $negocio = Negocio::actual();

        try {
            return app(CrearReserva::class)->ejecutar(new DatosReserva(
                negocio: $negocio,
                servicio: Servicio::findOrFail((int) $data['servicio_id']),
                nombreCliente: $data['nombre_cliente'],
                celularCliente: $data['celular_cliente'],
                correoCliente: $data['correo_cliente'] ?? null,
                inicio: CarbonImmutable::parse("{$data['fecha']} {$data['hora']}", $negocio->zona_horaria)->utc(),
                origen: OrigenCita::Panel,
                profesional: filled($data['profesional_id'] ?? null) ? Profesional::findOrFail((int) $data['profesional_id']) : null,
                notas: $data['notas'] ?? null,
                forzar: (bool) ($data['forzar'] ?? false),
            ));
        } catch (HoraYaTomadaException|HoraInvalidaException $excepcion) {
            Notification::make()->danger()->title($excepcion->getMessage())->send();

            throw new Halt;
        }
    }
}
