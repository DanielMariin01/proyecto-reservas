<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\PerteneceANegocio;
use Database\Factories\PaginaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class Pagina extends Model
{
    /** @use HasFactory<PaginaFactory> */
    use HasFactory;

    use PerteneceANegocio;

    protected $table = 'paginas';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'negocio_id',
        'bloques',
        'bloques_publicados',
        'publicada_en',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'bloques' => 'array',
            'bloques_publicados' => 'array',
            'publicada_en' => 'immutable_datetime',
        ];
    }

    public function estaPublicada(): bool
    {
        return $this->publicada_en !== null;
    }
}
