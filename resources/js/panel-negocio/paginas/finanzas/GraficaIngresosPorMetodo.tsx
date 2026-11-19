import type { ReactElement } from 'react';
import { Bar, BarChart, CartesianGrid, ResponsiveContainer, Tooltip, XAxis, YAxis } from 'recharts';
import type { IngresoPorMetodo } from '../../api/finanzas';

interface Propiedades {
    ingresosPorMetodo: Record<string, IngresoPorMetodo>;
}

export default function GraficaIngresosPorMetodo({ ingresosPorMetodo }: Propiedades): ReactElement {
    const datos = Object.values(ingresosPorMetodo);

    if (datos.length === 0) {
        return <p className="text-sm text-muted-foreground">Sin ingresos todavía en este periodo.</p>;
    }

    return (
        <div className="h-64">
            <ResponsiveContainer width="100%" height="100%">
                <BarChart data={datos} layout="vertical" margin={{ left: 16 }}>
                    <CartesianGrid horizontal={false} stroke="var(--border)" />
                    <XAxis type="number" tick={{ fill: 'var(--muted-foreground)', fontSize: 12 }} />
                    <YAxis type="category" dataKey="etiqueta" tick={{ fill: 'var(--muted-foreground)', fontSize: 12 }} width={90} />
                    <Tooltip
                        formatter={(valor) => `$${Number(valor).toLocaleString('es-CO')}`}
                        contentStyle={{ backgroundColor: 'var(--popover)', border: '1px solid var(--border)', borderRadius: 'var(--radius)' }}
                    />
                    <Bar dataKey="total" fill="var(--primary)" radius={4} />
                </BarChart>
            </ResponsiveContainer>
        </div>
    );
}
