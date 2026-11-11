import './bootstrap';
import { createRoot } from 'react-dom/client';
import PanelAdmin from './admin/PanelAdmin';

const contenedor = document.getElementById('admin');

if (contenedor) {
    const urlSalir = contenedor.dataset.urlSalir ?? '';
    const tokenCsrf = contenedor.dataset.tokenCsrf ?? '';

    createRoot(contenedor).render(<PanelAdmin urlSalir={urlSalir} tokenCsrf={tokenCsrf} />);
}
