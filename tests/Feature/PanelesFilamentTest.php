<?php

declare(strict_types=1);

test('/admin/login redirige a la unica pantalla de acceso', function (): void {
    $respuesta = $this->get('/admin/login');

    $respuesta->assertRedirect('/ingresar');
});

test('/app/login redirige a la unica pantalla de acceso', function (): void {
    $respuesta = $this->get('/app/login');

    $respuesta->assertRedirect('/ingresar');
});

test('un invitado que visita /admin termina en la pantalla de acceso', function (): void {
    $respuesta = $this->get('/admin');

    $respuesta->assertRedirect('/ingresar');
});
