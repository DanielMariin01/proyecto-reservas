<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\Profesionales\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProfesionalInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Profesional')
                ->columns(2)
                ->components([
                    ImageEntry::make('foto')->disk('public')->circular(),
                    TextEntry::make('nombre'),
                    TextEntry::make('especialidad'),
                    TextEntry::make('activo')->badge()->formatStateUsing(fn (bool $state): string => $state ? 'Activo' : 'Inactivo'),
                    TextEntry::make('biografia')->label('Biografía')->columnSpanFull(),
                    TextEntry::make('certificaciones')->columnSpanFull(),
                    TextEntry::make('usuario.email')->label('Usuario vinculado')->default('Sin vincular'),
                ]),
        ]);
    }
}
