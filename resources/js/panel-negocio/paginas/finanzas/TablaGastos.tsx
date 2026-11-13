import type { ReactElement } from 'react';
import { useMutation } from '@tanstack/react-query';
import { Trash2 } from 'lucide-react';
import { Button } from '@/componentes/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/componentes/ui/table';
import { eliminarGasto, type GastoFinanzas } from '../../api/finanzas';

interface Propiedades {
    negocioSlug: string;
    gastos: GastoFinanzas[];
    onEliminado: () => void;
}

export default function TablaGastos({ negocioSlug, gastos, onEliminado }: Propiedades): ReactElement {
    const mutacionEliminar = useMutation({
        mutationFn: (gastoId: number) => eliminarGasto(negocioSlug, gastoId),
        onSuccess: onEliminado,
    });

    if (gastos.length === 0) {
        return <p className="text-sm text-muted-foreground">Sin gastos registrados en este periodo.</p>;
    }

    return (
        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>Fecha</TableHead>
                    <TableHead>Descripción</TableHead>
                    <TableHead>Monto</TableHead>
                    <TableHead />
                </TableRow>
            </TableHeader>
            <TableBody>
                {gastos.map((gasto) => (
                    <TableRow key={gasto.id}>
                        <TableCell>{gasto.fecha}</TableCell>
                        <TableCell>{gasto.descripcion}</TableCell>
                        <TableCell>${gasto.monto.toLocaleString('es-CO')}</TableCell>
                        <TableCell>
                            <Button
                                variant="ghost"
                                size="icon"
                                disabled={mutacionEliminar.isPending}
                                onClick={() => {
                                    if (window.confirm('¿Eliminar este gasto?')) {
                                        mutacionEliminar.mutate(gasto.id);
                                    }
                                }}
                            >
                                <Trash2 size={16} strokeWidth={1.75} className="text-destructive" />
                            </Button>
                        </TableCell>
                    </TableRow>
                ))}
            </TableBody>
        </Table>
    );
}
