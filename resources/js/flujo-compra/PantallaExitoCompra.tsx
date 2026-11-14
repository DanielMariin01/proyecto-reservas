import { Banknote, Building2, CheckCircle2, MapPin, MessageCircle, Package } from 'lucide-react';
import type { ReactElement } from 'react';
import Boton from '@/componentes/Boton';
import LineaDetalle from '@/componentes/LineaDetalle';
import Tarjeta from '@/componentes/Tarjeta';
import type { PedidoCreado } from '@/tipos';

interface Propiedades {
    pedido: PedidoCreado;
    onCerrar: () => void;
}

export default function PantallaExitoCompra({ pedido, onCerrar }: Propiedades): ReactElement {
    const enlaceWhatsapp = pedido.whatsapp
        ? `https://wa.me/${pedido.whatsapp.replace(/\D/g, '')}?text=${encodeURIComponent(
              `Hola, acabo de hacer un pedido en ${pedido.nombreNegocio}.`,
          )}`
        : null;
    const enlaceMapa =
        pedido.tipoEntrega === 'recoger' && pedido.direccion
            ? `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(pedido.direccion)}`
            : null;

    return (
        <Tarjeta className="scroll-primario max-h-[90vh] w-full max-w-md overflow-y-auto text-center">
            <div className="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-secundario text-blanco">
                <CheckCircle2 size={32} />
            </div>
            <h2 className="text-xl font-semibold text-tinta">¡Tu pedido quedó registrado!</h2>

            <div className="mt-4 space-y-2 rounded-tarjeta bg-fondo-panel p-4 text-left">
                <p className="font-semibold text-tinta">{pedido.nombreNegocio}</p>
                {pedido.items.map((item, indice) => (
                    <LineaDetalle key={indice} icono={Package} texto={`${item.cantidad} × ${item.nombreProducto}`} />
                ))}
                <LineaDetalle icono={Banknote} texto={`$${pedido.total.toLocaleString('es-CO')}`} />
                {pedido.tipoEntrega === 'domicilio' ? (
                    <LineaDetalle icono={MapPin} texto={`Domicilio · ${pedido.barrio ?? ''}`} />
                ) : (
                    pedido.direccion && <LineaDetalle icono={Building2} texto={pedido.direccion} />
                )}
            </div>

            <div className="mt-4 flex flex-col gap-2">
                {enlaceWhatsapp && (
                    <a href={enlaceWhatsapp} target="_blank" rel="noreferrer">
                        <Boton variante="contorno" className="w-full">
                            <span className="flex items-center justify-center gap-2">
                                <MessageCircle size={18} /> Escribir por WhatsApp
                            </span>
                        </Boton>
                    </a>
                )}
                {enlaceMapa && (
                    <a href={enlaceMapa} target="_blank" rel="noreferrer">
                        <Boton variante="contorno" className="w-full">
                            <span className="flex items-center justify-center gap-2">
                                <MapPin size={18} /> Cómo llegar
                            </span>
                        </Boton>
                    </a>
                )}
            </div>

            <p className="mt-4 text-sm text-texto-suave">Te avisaremos cuando tu pedido esté listo.</p>

            <Boton variante="texto" className="mt-2" onClick={onCerrar}>
                Cerrar
            </Boton>
        </Tarjeta>
    );
}
