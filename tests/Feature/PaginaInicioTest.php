<?php

declare(strict_types=1);

test('la pagina de inicio responde correctamente', function (): void {
    $respuesta = $this->get('/');

    $respuesta->assertStatus(200);
});
