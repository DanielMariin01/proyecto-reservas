<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Mensajes de restablecimiento de contraseña
    |--------------------------------------------------------------------------
    |
    | Usados por RestablecimientoContrasenaController, que llama a
    | Password::reset() y traduce el estado devuelto con __($estado).
    |
    */

    'reset' => 'Tu contraseña quedó actualizada.',
    'sent' => 'Te enviamos un correo con el enlace para restablecer tu contraseña.',
    'throttled' => 'Espera un momento antes de volver a intentarlo.',
    'token' => 'Este enlace para restablecer la contraseña no es válido o ya expiró.',
    'user' => 'No encontramos ninguna cuenta con ese correo electrónico.',

];
