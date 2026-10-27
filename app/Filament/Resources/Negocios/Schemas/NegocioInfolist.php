<?php

declare(strict_types=1);

namespace App\Filament\Resources\Negocios\Schemas;

use App\Models\Negocio;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class NegocioInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Negocio')
                ->columns(3)
                ->components([
                    TextEntry::make('nombre'),
                    TextEntry::make('slug'),
                    TextEntry::make('tipo')->formatStateUsing(fn ($state) => $state?->etiqueta()),
                ]),
            Section::make('Suscripción')
                ->columns(3)
                ->components([
                    TextEntry::make('estado_suscripcion')
                        ->label('Estado')
                        ->badge()
                        ->formatStateUsing(fn ($state) => $state?->etiqueta()),
                    TextEntry::make('proxima_fecha_pago')->label('Próximo pago')->date('d/m/Y'),
                    TextEntry::make('cancelado_desde')
                        ->label('Datos se conservan hasta')
                        ->state(fn (Negocio $record) => $record->fechaLimiteRetencionDeDatos()?->format('d/m/Y') ?? '—'),
                ]),
            Section::make('Usuarios')
                ->components([
                    TextEntry::make('usuarios')
                        ->label('')
                        ->state(fn (Negocio $record) => $record->usuariosConRol()
                            ->map(fn (array $fila) => "{$fila['usuario']->nombre} ({$fila['rol']}) — {$fila['usuario']->email}")
                            ->values()
                            ->all())
                        ->listWithLineBreaks()
                        ->bulleted(),
                ]),
        ]);
    }
}
