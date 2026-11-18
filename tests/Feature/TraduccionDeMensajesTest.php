<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Validator;

/**
 * Antes del PR que agrega lang/es/*.php y lang/es.json no existía ningún
 * archivo de idioma en el proyecto: con APP_LOCALE=es pero sin traducción,
 * Laravel devolvía la clave cruda ("validation.required") en vez de un
 * mensaje, y el correo de notificaciones quedaba mitad en español mitad en
 * inglés ("Hello!", "Regards,"). Esta prueba fija ese comportamiento.
 */
test('los mensajes genericos de validacion salen en espanol, no la clave cruda', function (): void {
    $errores = Validator::make(
        ['correo' => 'no-es-un-correo'],
        ['nombre' => 'required', 'correo' => 'email'],
    )->errors();

    expect($errores->first('nombre'))
        ->toBe('El campo nombre es obligatorio.')
        ->not->toBe('validation.required');

    expect($errores->first('correo'))
        ->toBe('El campo correo electrónico debe ser un correo electrónico válido.');
});

test('los nombres de atributo mas usados tienen una traduccion legible', function (): void {
    expect(trans('validation.attributes.autorizacion_datos'))->toBe('autorización de tratamiento de datos');
    expect(trans('validation.attributes.celular'))->toBe('celular');
    expect(trans('validation.attributes.profesional_id'))->toBe('profesional');
});

test('los mensajes de restablecer contrasena salen en espanol', function (): void {
    expect(__('passwords.token'))->toBe('Este enlace para restablecer la contraseña no es válido o ya expiró.');
    expect(__('passwords.user'))->toBe('No encontramos ninguna cuenta con ese correo electrónico.');
});

test('el saludo y la firma por defecto del correo de notificaciones salen en espanol', function (): void {
    expect(__('Hello!'))->toBe('¡Hola!');
    expect(__('Regards,'))->toBe('Saludos,');
    expect(__('All rights reserved.'))->toBe('Todos los derechos reservados.');
});
