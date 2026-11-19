import type { ReactElement } from 'react';
import { useState } from 'react';
import { useMutation } from '@tanstack/react-query';
import { Button } from '@/componentes/ui/button';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/componentes/ui/dialog';
import { Input } from '@/componentes/ui/input';
import { registrarGasto } from '../../api/finanzas';

interface Propiedades {
    negocioSlug: string;
    onRegistrado: () => void;
    onCerrar: () => void;
}

export default function DialogoNuevoGasto({ negocioSlug, onRegistrado, onCerrar }: Propiedades): ReactElement {
    const [monto, setMonto] = useState('');
    const [descripcion, setDescripcion] = useState('');
    const [fecha, setFecha] = useState(new Date().toISOString().slice(0, 10));
    const [error, setError] = useState<string | null>(null);

    const mutacion = useMutation({
        mutationFn: () => registrarGasto(negocioSlug, { monto: Number(monto), descripcion, fecha }),
        onSuccess: onRegistrado,
        onError: () => setError('No se pudo registrar el gasto. Código REF-FIN1.'),
    });

    function enviar(): void {
        setError(null);
        mutacion.mutate();
    }

    return (
        <Dialog open onOpenChange={(abierto) => !abierto && onCerrar()}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Nuevo gasto</DialogTitle>
                </DialogHeader>

                <div className="space-y-4">
                    {error && <p className="text-sm text-destructive">{error}</p>}

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
