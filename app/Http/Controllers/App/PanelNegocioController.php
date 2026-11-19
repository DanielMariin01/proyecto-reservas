<?php

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Enums\Permiso;
use App\Http\Controllers\Controller;
use App\Models\Negocio;
use App\Models\Usuario;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

final class PanelNegocioController extends Controller
{
    /**
     * La raíz del panel (/app/{slug}) no tiene contenido propio: redirige
     * a la pantalla con más sentido según el rol, en vez de mostrar el
     * mensaje genérico de bienvenida. Quien gestiona citas de todos ve
     * el panel del día; un profesional sin ese permiso iría a un 403 si
     * lo mandáramos ahí, así que cae en sus propias citas de hoy.
     */
    public function mostrar(Negocio $negocio): View|RedirectResponse
    {
        /** @var Usuario $usuario */
        $usuario = Auth::guard('web')->user();

        if ($usuario->es_super_admin || $usuario->can(Permiso::GestionarCitasTodas->value)) {
            return redirect()->route('app.panel-del-dia', $negocio);
        }

        if ($usuario->profesional !== null) {
            return redirect()->route('app.mis-citas-hoy', $negocio);
        }

        return view('panel-negocio', ['negocio' => $negocio]);
    }
}
