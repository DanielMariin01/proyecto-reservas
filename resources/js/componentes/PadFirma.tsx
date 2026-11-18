import { useEffect, useRef, useState } from 'react';
import type { PointerEvent as ReactPointerEvent, ReactElement } from 'react';
import Boton from './Boton';

interface PropiedadesPadFirma {
    etiqueta: string;
    firmaExistenteUrl?: string | null;
    onCambiar: (firmaBase64: string | null) => void;
}

const ALTO_CANVAS = 160;

/**
 * Pad de firma sobre canvas (sección 19 de requerimientos.md, RF-HCL-06):
 * se dibuja con el mouse o el dedo y se exporta como PNG en base64 al
 * padre, que la manda junto con el resto del formulario.
 */
export default function PadFirma({ etiqueta, firmaExistenteUrl = null, onCambiar }: PropiedadesPadFirma): ReactElement {
    const canvasRef = useRef<HTMLCanvasElement | null>(null);
    const dibujandoRef = useRef(false);
    const [mostrarExistente, setMostrarExistente] = useState(firmaExistenteUrl !== null);
    const [tieneTrazo, setTieneTrazo] = useState(false);

    useEffect(() => {
        const canvas = canvasRef.current;

        if (canvas === null || mostrarExistente) {
            return;
        }

        canvas.width = canvas.offsetWidth * window.devicePixelRatio;
        canvas.height = ALTO_CANVAS * window.devicePixelRatio;

        const contexto = canvas.getContext('2d');
        contexto?.scale(window.devicePixelRatio, window.devicePixelRatio);
    }, [mostrarExistente]);

    function coordenadas(evento: ReactPointerEvent<HTMLCanvasElement>): { x: number; y: number } {
        const rect = evento.currentTarget.getBoundingClientRect();

        return { x: evento.clientX - rect.left, y: evento.clientY - rect.top };
    }

    function alPresionar(evento: ReactPointerEvent<HTMLCanvasElement>): void {
        const contexto = canvasRef.current?.getContext('2d');

        if (!contexto) {
            return;
        }

        dibujandoRef.current = true;
        const { x, y } = coordenadas(evento);
        contexto.beginPath();
        contexto.moveTo(x, y);
    }

    function alMover(evento: ReactPointerEvent<HTMLCanvasElement>): void {
        const contexto = canvasRef.current?.getContext('2d');

        if (!contexto || !dibujandoRef.current) {
            return;
        }

        const { x, y } = coordenadas(evento);
        contexto.lineWidth = 2;
        contexto.lineCap = 'round';
        contexto.strokeStyle = '#3E4043';
        contexto.lineTo(x, y);
        contexto.stroke();
        setTieneTrazo(true);
    }

    function alSoltar(): void {
        if (!dibujandoRef.current) {
            return;
        }

        dibujandoRef.current = false;
        onCambiar(canvasRef.current?.toDataURL('image/png') ?? null);
    }

    function borrar(): void {
        const canvas = canvasRef.current;
        const contexto = canvas?.getContext('2d');
        contexto?.clearRect(0, 0, canvas?.width ?? 0, canvas?.height ?? 0);
        setTieneTrazo(false);
        onCambiar(null);
    }

    function firmarDeNuevo(): void {
        setMostrarExistente(false);
        onCambiar(null);
    }

    return (
        <div className="flex flex-col gap-2">
            <p className="text-sm font-medium text-tinta">{etiqueta}</p>

            {mostrarExistente ? (
                <div className="flex flex-col gap-2">
                    <img
                        src={firmaExistenteUrl ?? undefined}
                        alt={etiqueta}
                        className="h-[160px] w-full rounded-boton border border-gris-claro object-contain"
                    />
                    <Boton variante="contorno" type="button" onClick={firmarDeNuevo} className="self-start">
                        Firmar de nuevo
                    </Boton>
                </div>
            ) : (
                <div className="flex flex-col gap-2">
                    <canvas
                        ref={canvasRef}
                        style={{ height: ALTO_CANVAS }}
                        className="w-full touch-none rounded-boton border border-gris-claro bg-blanco"
                        onPointerDown={alPresionar}
                        onPointerMove={alMover}
                        onPointerUp={alSoltar}
                        onPointerLeave={alSoltar}
                    />
                    <Boton variante="texto" type="button" onClick={borrar} disabled={!tieneTrazo} className="self-start">
                        Borrar
                    </Boton>
                </div>
            )}
        </div>
    );
}
