<?php

declare(strict_types=1);

use App\Support\Dinero;

test('formatea pesos enteros con punto de miles y simbolo de peso', function (): void {
    expect(Dinero::formatearPesos(24000))->toBe('$24.000');
    expect(Dinero::formatearPesos(1500000))->toBe('$1.500.000');
    expect(Dinero::formatearPesos(0))->toBe('$0');
});
