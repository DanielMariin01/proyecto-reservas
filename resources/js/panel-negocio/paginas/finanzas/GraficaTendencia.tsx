import type { ReactElement } from 'react';
import { CartesianGrid, Legend, Line, LineChart, ResponsiveContainer, Tooltip, XAxis, YAxis } from 'recharts';
import type { PuntoTendencia } from '../../api/finanzas';

interface Propiedades {
    tendencia: PuntoTendencia[];
}

export default function GraficaTendencia({ tendencia }: Propiedades): ReactElement {
    if (tendencia.length === 0) {
        return <p className="text-sm text-muted-foreground">Sin datos todavía en este periodo.</p>;
    }

    return (
        <div className="h-64">
            <ResponsiveContainer width="100%" height="100%">
                <LineChart data={tendencia} margin={{ left: 8, right: 8 }}>
                    <CartesianGrid vertical={false} stroke="var(--border)" />
                    <XAxis dataKey="etiqueta" tick={{ fill: 'var(--muted-foreground)', fontSize: 12 }} />
                    <YAxis tick={{ fill: 'var(--muted-foreground)', fontSize: 12 }} width={60} />
                    <Tooltip
                        formatter={(valor) => `$${Number(valor).toLocaleString('es-CO')}`}
                        contentStyle={{ backgroundColor: 'var(--popover)', border: '1px solid var(--border)', borderRadius: 'var(--radius)' }}
                    />
                    <Legend wrapperStyle={{ fontSize: 12, color: 'var(--muted-foreground)' }} />
                    <Line type="monotone" dataKey="ingresos" name="Ingresos" stroke="var(--primary)" strokeWidth={2} dot={false} />
                    <Line type="monotone" dataKey="gastos" name="Gastos" stroke="var(--destructive)" strokeWidth={2} dot={false} />
                </LineChart>
            </ResponsiveContainer>
        </div>
    );
}
