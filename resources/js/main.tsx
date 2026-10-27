import './bootstrap';
import { createRoot } from 'react-dom/client';
import App from './App';

const contenedor = document.getElementById('app');

if (contenedor) {
    createRoot(contenedor).render(<App />);
}
