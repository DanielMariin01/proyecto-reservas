<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Storage;

test('sirve un archivo existente del disco publico', function (): void {
    Storage::fake('public');
    Storage::disk('public')->put('servicios/corte.jpg', 'contenido-de-prueba');

    $respuesta = $this->get('/storage/servicios/corte.jpg');

    $respuesta->assertOk();
    $respuesta->assertHeader('Cache-Control', 'max-age=604800, public');
});

test('responde 404 si el archivo no existe en el disco publico', function (): void {
    Storage::fake('public');

    $this->get('/storage/servicios/no-existe.jpg')->assertNotFound();
});
