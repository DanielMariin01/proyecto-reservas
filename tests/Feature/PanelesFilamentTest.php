<?php

declare(strict_types=1);

test('el panel de super admin responde en /admin/login', function (): void {
    $respuesta = $this->get('/admin/login');

    $respuesta->assertStatus(200);
});

test('el panel del negocio responde en /app/login', function (): void {
    $respuesta = $this->get('/app/login');

    $respuesta->assertStatus(200);
});
