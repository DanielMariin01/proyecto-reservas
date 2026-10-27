<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\Negocio;
use App\Models\Scopes\ScopeNegocio;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait PerteneceANegocio
{
    protected static function bootPerteneceANegocio(): void
    {
        static::addGlobalScope(new ScopeNegocio);
    }

    /**
     * @return BelongsTo<Negocio, $this>
     */
    public function negocio(): BelongsTo
    {
        return $this->belongsTo(Negocio::class);
    }
}
