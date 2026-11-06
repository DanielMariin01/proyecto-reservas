<?php

declare(strict_types=1);

use App\Enums\RolNegocio;
use App\Models\Cliente;
use App\Models\CuentaCliente;
use App\Models\Negocio;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as UsuarioFalsoDeGoogle;

test('el primer login con Google crea la cuenta y la deja en sesion', function (): void {
    Socialite::fake('google', UsuarioFalsoDeGoogle::fake([
        'id' => 'google-123',
        'email' => 'ana@example.com',
        'name' => 'Ana Pérez',
    ]));

    $respuesta = $this->get('/mi-cuenta/google/callback');

    $respuesta->assertRedirectToRoute('mi-cuenta');
    expect(CuentaCliente::query()->where('google_id', 'google-123')->count())->toBe(1);

    $cuenta = CuentaCliente::query()->where('google_id', 'google-123')->firstOrFail();
    expect($cuenta->correo)->toBe('ana@example.com');
    expect(Auth::guard('cliente')->check())->toBeTrue();
    expect(Auth::guard('cliente')->id())->toBe($cuenta->id);
});

test('el primer login con Google vincula los Cliente existentes de cualquier negocio con ese correo', function (): void {
    $negocioA = Negocio::factory()->create();
    $negocioB = Negocio::factory()->create();
    $clienteA = Cliente::factory()->create(['negocio_id' => $negocioA->id, 'correo' => 'ana@example.com']);
    $clienteB = Cliente::factory()->create(['negocio_id' => $negocioB->id, 'correo' => 'ana@example.com']);
    $clienteDeOtroCorreo = Cliente::factory()->create(['negocio_id' => $negocioA->id, 'correo' => 'otra@example.com']);

    Socialite::fake('google', UsuarioFalsoDeGoogle::fake(['id' => 'google-123', 'email' => 'ana@example.com']));
    $this->get('/mi-cuenta/google/callback');

    $cuenta = CuentaCliente::query()->where('correo', 'ana@example.com')->firstOrFail();
    expect($clienteA->refresh()->cuenta_cliente_id)->toBe($cuenta->id);
    expect($clienteB->refresh()->cuenta_cliente_id)->toBe($cuenta->id);
    expect($clienteDeOtroCorreo->refresh()->cuenta_cliente_id)->toBeNull();
});

test('volver a entrar con el mismo google_id no duplica la cuenta', function (): void {
    Socialite::fake('google', UsuarioFalsoDeGoogle::fake(['id' => 'google-123', 'email' => 'ana@example.com']));
    $this->get('/mi-cuenta/google/callback');
    Auth::guard('cliente')->logout();

    $this->get('/mi-cuenta/google/callback');

    expect(CuentaCliente::query()->where('google_id', 'google-123')->count())->toBe(1);
});

test('si el correo ya tenia cuenta con otro google_id, se actualiza en vez de duplicarse', function (): void {
    $cuenta = CuentaCliente::factory()->create(['correo' => 'ana@example.com', 'google_id' => 'google-viejo']);

    Socialite::fake('google', UsuarioFalsoDeGoogle::fake(['id' => 'google-nuevo', 'email' => 'ana@example.com']));
    $this->get('/mi-cuenta/google/callback');

    expect(CuentaCliente::query()->where('correo', 'ana@example.com')->count())->toBe(1);
    expect($cuenta->refresh()->google_id)->toBe('google-nuevo');
});

test('si Socialite falla, redirige de vuelta con un error en vez de romper', function (): void {
    Socialite::fake('google', function (): never {
        throw new RuntimeException('Google dijo que no');
    });

    $respuesta = $this->get('/mi-cuenta/google/callback');

    $respuesta->assertRedirectToRoute('mi-cuenta', ['error' => 'google']);
    expect(CuentaCliente::query()->count())->toBe(0);
});

test('el guard cliente y el guard web quedan separados', function (): void {
    $cuenta = CuentaCliente::factory()->create();
    $negocio = Negocio::factory()->create();
    $usuario = crearUsuarioConRol($negocio, RolNegocio::Dueno);

    Auth::guard('cliente')->login($cuenta);

    expect(Auth::guard('cliente')->check())->toBeTrue();
    expect(Auth::guard('web')->check())->toBeFalse();

    Auth::guard('cliente')->logout();
    Auth::guard('web')->login($usuario);

    expect(Auth::guard('web')->check())->toBeTrue();
    expect(Auth::guard('cliente')->check())->toBeFalse();
});
