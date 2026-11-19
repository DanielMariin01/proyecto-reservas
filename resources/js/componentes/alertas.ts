import Swal from 'sweetalert2';

/**
 * Envoltura delgada de SweetAlert2 para que una alerta de error o de éxito
 * se vea siempre, sin importar si vino de un Acordeon cerrado o de
 * cualquier otra parte de la pantalla que pueda estar oculta. Toma los
 * colores de los tokens del proyecto (variables CSS de tokens.css), no de
 * la paleta por defecto de la librería.
 */
function colorToken(variable: string): string {
    return getComputedStyle(document.documentElement).getPropertyValue(variable).trim();
}

const BASE = {
    buttonsStyling: false,
    customClass: {
        popup: 'rounded-tarjeta',
        confirmButton: 'rounded-boton bg-primario px-6 py-2 font-semibold text-blanco hover:bg-acento',
    },
};

export async function mostrarErrorDeValidacion(mensajes: string[]): Promise<void> {
    await Swal.fire({
        ...BASE,
        icon: 'error',
        iconColor: '#dc2626',
        title: 'No se pudo guardar',
        html: `<ul style="text-align:left;margin:0;padding-left:1.2em">${mensajes.map((mensaje) => `<li>${mensaje}</li>`).join('')}</ul>`,
        confirmButtonText: 'Entendido',
    });
}

export async function mostrarExito(mensaje: string): Promise<void> {
    await Swal.fire({
        ...BASE,
        icon: 'success',
        iconColor: colorToken('--color-secundario'),
        title: mensaje,
        timer: 1200,
        showConfirmButton: false,
    });
}
