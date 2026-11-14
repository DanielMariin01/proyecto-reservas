import axios from 'axios';

export interface UsuarioActual {
    esProfesional: boolean;
    puedeVerFinanzas: boolean;
    puedeGestionarProductos: boolean;
}

export async function obtenerUsuarioActual(slug: string): Promise<UsuarioActual> {
    const respuesta = await axios.get<UsuarioActual>(`/app/${slug}/api/usuario-actual`);

    return respuesta.data;
}
