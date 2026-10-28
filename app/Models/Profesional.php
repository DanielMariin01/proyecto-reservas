<?php

declare(strict_types=1);

namespace App\Models;

use App\Disponibilidad\BloqueLibre;
use App\Enums\DiaSemana;
use App\Enums\TipoBloqueo;
use App\Models\Concerns\PerteneceANegocio;
use Carbon\CarbonImmutable;
use Database\Factories\ProfesionalFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class Profesional extends Model
{
    /** @use HasFactory<ProfesionalFactory> */
    use HasFactory;

    use PerteneceANegocio;

    private const TTL_CACHE_BLOQUES_LIBRES_MINUTOS = 15;

    protected $table = 'profesionales';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'negocio_id',
        'usuario_id',
        'nombre',
        'foto',
        'especialidad',
        'biografia',
        'certificaciones',
        'color',
        'activo',
        'google_calendar_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Usuario, $this>
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }

    /**
     * @return BelongsToMany<Servicio, $this, ProfesionalServicio>
     */
    public function servicios(): BelongsToMany
    {
        return $this->belongsToMany(Servicio::class, 'profesional_servicio')
            ->using(ProfesionalServicio::class)
            ->withPivot(['precio', 'duracion_minutos'])
            ->withTimestamps();
    }

    /**
     * @return HasMany<HorarioTrabajo, $this>
     */
    public function horariosTrabajo(): HasMany
    {
        return $this->hasMany(HorarioTrabajo::class);
    }

    /**
     * @return HasMany<Bloqueo, $this>
     */
    public function bloqueos(): HasMany
    {
        return $this->hasMany(Bloqueo::class);
    }

    /**
     * Sección 5, pasos 1-2: bloques del horario semanal para ese día, menos
     * los bloqueos que lo crucen, más las aperturas especiales. Todo se
     * calcula y compara en UTC; la única conversión a la zona del negocio
     * ocurre aquí, al combinar la hora de pared del horario semanal con la
     * fecha pedida.
     *
     * $fecha solo aporta su fecha (Y-m-d): se reinterpreta en la zona del
     * negocio sin importar en qué zona/hora venga el objeto.
     *
     * @return Collection<int, BloqueLibre>
     */
    public function obtenerBloquesLibres(CarbonImmutable $fecha): Collection
    {
        return Cache::remember(
            $this->claveCacheBloquesLibres($fecha),
            now()->addMinutes(self::TTL_CACHE_BLOQUES_LIBRES_MINUTOS),
            function () use ($fecha): Collection {
                $negocio = Negocio::findOrFail($this->negocio_id);
                $fechaLocal = CarbonImmutable::parse($fecha->format('Y-m-d'), $negocio->zona_horaria)->startOfDay();

                $bloques = $this->bloquesDelHorarioSemanal($fechaLocal);

                foreach ($this->bloqueosQueCruzan($fechaLocal, TipoBloqueo::Bloqueo) as $bloqueo) {
                    $bloques = $this->restarIntervalo($bloques, $bloqueo->inicio, $bloqueo->fin);
                }

                foreach ($this->bloqueosQueCruzan($fechaLocal, TipoBloqueo::Apertura) as $apertura) {
                    $bloques->push(new BloqueLibre($apertura->inicio, $apertura->fin));
                }

                return $bloques
                    ->filter(fn (BloqueLibre $bloque): bool => $bloque->fin->greaterThan($bloque->inicio))
                    ->sortBy(fn (BloqueLibre $bloque): int => $bloque->inicio->getTimestamp())
                    ->values();
            },
        );
    }

    /**
     * RNF-07: la caché se invalida sola sin eventos ni tags (el driver
     * `database` no soporta tags) porque la clave incluye la marca de
     * tiempo más reciente entre los horarios y bloqueos del profesional;
     * cualquier cambio cambia la clave y las entradas viejas simplemente
     * expiran por TTL sin volver a leerse.
     */
    private function claveCacheBloquesLibres(CarbonImmutable $fecha): string
    {
        $ultimaModificacionHorarios = $this->horariosTrabajo()->max('updated_at');
        $ultimaModificacionBloqueos = $this->bloqueos()->max('updated_at');
        $version = md5("{$ultimaModificacionHorarios}|{$ultimaModificacionBloqueos}");

        return "disponibilidad.bloques_libres.{$this->id}.{$fecha->format('Y-m-d')}.{$version}";
    }

    /**
     * @return Collection<int, BloqueLibre>
     */
    private function bloquesDelHorarioSemanal(CarbonImmutable $fechaLocal): Collection
    {
        $diaSemana = DiaSemana::from($fechaLocal->dayOfWeek);
        $fechaTexto = $fechaLocal->toDateString();
        $zonaHoraria = $fechaLocal->getTimezone();

        return $this->horariosTrabajo()
            ->where('dia_semana', $diaSemana->value)
            ->get()
            ->map(function (HorarioTrabajo $horario) use ($fechaTexto, $zonaHoraria): BloqueLibre {
                $inicio = CarbonImmutable::parse("{$fechaTexto} {$horario->hora_inicio}", $zonaHoraria)->utc();
                $fin = CarbonImmutable::parse("{$fechaTexto} {$horario->hora_fin}", $zonaHoraria)->utc();

                return new BloqueLibre($inicio, $fin);
            })
            ->values();
    }

    /**
     * @return Collection<int, Bloqueo>
     */
    private function bloqueosQueCruzan(CarbonImmutable $fechaLocal, TipoBloqueo $tipo): Collection
    {
        $inicioDiaUtc = $fechaLocal->startOfDay()->utc();
        $finDiaUtc = $fechaLocal->endOfDay()->utc();

        return $this->bloqueos()
            ->where('tipo', $tipo->value)
            ->where('inicio', '<', $finDiaUtc)
            ->where('fin', '>', $inicioDiaUtc)
            ->get();
    }

    /**
     * @param  Collection<int, BloqueLibre>  $bloques
     * @return Collection<int, BloqueLibre>
     */
    private function restarIntervalo(Collection $bloques, CarbonImmutable $inicioBloqueo, CarbonImmutable $finBloqueo): Collection
    {
        return $bloques->flatMap(function (BloqueLibre $bloque) use ($inicioBloqueo, $finBloqueo): array {
            if ($finBloqueo->lessThanOrEqualTo($bloque->inicio) || $inicioBloqueo->greaterThanOrEqualTo($bloque->fin)) {
                return [$bloque];
            }

            $restante = [];

            if ($inicioBloqueo->greaterThan($bloque->inicio)) {
                $restante[] = new BloqueLibre($bloque->inicio, $inicioBloqueo);
            }

            if ($finBloqueo->lessThan($bloque->fin)) {
                $restante[] = new BloqueLibre($finBloqueo, $bloque->fin);
            }

            return $restante;
        });
    }

    /**
     * RF-PRO-02: el precio propio del profesional para ese servicio
     * sobrescribe el del servicio cuando está definido.
     */
    public function precioParaServicio(Servicio $servicio): int
    {
        return $this->valorPropioEnPivote($servicio, 'precio') ?? $servicio->precio;
    }

    public function duracionMinutosParaServicio(Servicio $servicio): int
    {
        return $this->valorPropioEnPivote($servicio, 'duracion_minutos') ?? $servicio->duracion_minutos;
    }

    private function valorPropioEnPivote(Servicio $servicio, string $columna): ?int
    {
        $valor = DB::table('profesional_servicio')
            ->where('profesional_id', $this->id)
            ->where('servicio_id', $servicio->id)
            ->value($columna);

        return $valor === null ? null : (int) $valor;
    }
}
