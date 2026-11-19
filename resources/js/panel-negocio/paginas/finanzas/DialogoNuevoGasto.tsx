import type { ReactElement } from 'react';
import { useState } from 'react';
import { Button } from '@/componentes/ui/button';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/componentes/ui/dialog';
import { Input } from '@/componentes/ui/input';
import { useMutacionConAlerta } from '@/componentes/useMutacionConAlerta';
import { hoyISO } from '@/utils/fecha';
import { registrarGasto } from '../../api/finanzas';

interface Propiedades {
    negocioSlug: string;
    onRegistrado: () => void;
    onCerrar: () => void;
}

export default function DialogoNuevoGasto({ negocioSlug, onRegistrado, onCerrar }: Propiedades): ReactElement {
    const [monto, setMonto] = useState('');
    const [descripcion, setDescripcion] = useState('');
    const [fecha, setFecha] = useState(hoyISO());

    const mutacion = useMutacionConAlerta({
        mutationFn: () => registrarGasto(negocioSlug, { monto: Number(monto), descripcion, fecha }),
        mensajeExito: 'Gasto registrado correctamente.',
        onSuccess: onRegistrado,
    });

    function enviar(): void {
        mutacion.mutate();
    }

    return (
        <Dialog open onOpenChange={(abierto) => !abierto && onCerrar()}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Nuevo gasto</DialogTitle>
                </DialogHeader>

                <div className="space-y-4">
                    <div className="space-y-1">
                        <label htmlFor="gasto-monto" className="text-sm font-medium text-foreground">
                            Monto
                        </label>
                        <Input id="gasto-monto" type="number" min={1} value={monto} onChange={(evento) => setMonto(evento.target.value)} />
                    </div>

                    <div className="space-y-1">
                        <label htmlFor="gasto-descripcion" className="text-sm font-medium text-foreground">
                            Descripción
                        </label>
                        <Input
                            id="gasto-descripcion"
                            value={descripcion}
                            onChange={(evento) => setDescripcion(evento.target.value)}
                            placeholder="¿En qué se gastó?"
                        />
                    </div>

                    <div className="space-y-1">
                        <label htmlFor="gasto-fecha" className="text-sm font-medium text-foreground">
                            Fecha
                        </label>
                        <Input id="gasto-fecha" type="date" value={fecha} onChange={(evento) => setFecha(evento.target.value)} />
                    </div>

                    <Button onClick={enviar} disabled={mutacion.isPending || monto === '' || descripcion === ''} className="w-full">
                        {mutacion.isPending ? 'Guardando...' : 'Guardar gasto'}
                    </Button>
                </div>
            </DialogContent>
        </Dialog>
    );
}
