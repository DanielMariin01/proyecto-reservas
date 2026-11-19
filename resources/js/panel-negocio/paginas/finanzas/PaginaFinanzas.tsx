import type { ReactElement } from 'react';
import { useState } from 'react';
import { useQuery, useQueryClient } from '@tanstack/react-query';
import { Banknote, PiggyBank, Plus, TrendingDown, TrendingUp } from 'lucide-react';
import { Button } from '@/componentes/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/componentes/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/componentes/ui/select';
import { type RangoFinanzas as TipoRango, obtenerFinanzas } from '../../api/finanzas';
import LayoutPanelNegocio from '../../LayoutPanelNegocio';
import DialogoNuevoGasto from './DialogoNuevoGasto';
import GraficaIngresosPorMetodo from './GraficaIngresosPorMetodo';
import GraficaTendencia from './GraficaTendencia';
import TablaGastos from './TablaGastos';

const OPCIONES_RANGO: { value: TipoRango; label: string }[] = [
    { value: 'mes', label: 'Este mes' },
    { value: 'quince_dias', label: 'Últimos 15 días' },
    { value: 'anio', label: 'Este año' },
];

function formatearMoneda(valor: number): string {
    return `$${valor.toLocaleString('es-CO')}`;
}

interface Propiedades {
    negocioNombre: string;
    negocioSlug: string;
    urlSalir: string;
    tokenCsrf: string;
}

export default function PaginaFinanzas({ negocioNombre, negocioSlug, urlSalir, tokenCsrf }: Propiedades): ReactElement {
    const [rango, setRango] = useState<TipoRango>('mes');
    const [dialogoAbierto, setDialogoAbierto] = useState(false);
    const queryClient = useQueryClient();

    const consulta = useQuery({
        queryKey: ['panel-finanzas', negocioSlug, rango],
        queryFn: () => obtenerFinanzas(negocioSlug, rango),
    });

    function invalidar(): void {
        queryClient.invalidateQueries({ queryKey: ['panel-finanzas', negocioSlug, rango] });
    }

    const datos = consulta.data;

    return (
        <LayoutPanelNegocio negocioNombre={negocioNombre} negocioSlug={negocioSlug} urlSalir={urlSalir} tokenCsrf={tokenCsrf}>
            <div className="space-y-6">
                <div className="flex flex-wrap items-center justify-between gap-4">
                    <h2 className="text-lg font-semibold text-tinta">Finanzas</h2>

                    <div className="flex flex-wrap items-center gap-2">
                        <Select value={rango} onValueChange={(valor) => setRango(valor as TipoRango)}>
                            <SelectTrigger className="w-44">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                {OPCIONES_RANGO.map((opcion) => (
                                    <SelectItem key={opcion.value} value={opcion.value}>
                                        {opcion.label}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>

                        <Button onClick={() => setDialogoAbierto(true)}>
                            <Plus size={16} strokeWidth={1.75} />
                            Nuevo gasto
                        </Button>
                    </div>
                </div>

                {consulta.isLoading ? (
                    <p className="text-sm text-muted-foreground">Cargando...</p>
                ) : consulta.isError ? (
                    <p className="text-sm text-muted-foreground">Algo falló cargando finanzas. Código REF-FIN0.</p>
                ) : datos ? (
                    <>
                        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            <Card>
                                <CardHeader className="flex-row items-center justify-between pb-2">
                                    <CardTitle>Ingresos brutos</CardTitle>
                                    <TrendingUp size={18} strokeWidth={1.75} className="text-primary" />
                                </CardHeader>
                                <CardContent className="pt-0 text-2xl font-bold text-foreground">{formatearMoneda(datos.ingresosBrutos)}</CardContent>
                            </Card>
                            <Card>
                                <CardHeader className="flex-row items-center justify-between pb-2">
                                    <CardTitle>Gastos</CardTitle>
                                    <TrendingDown size={18} strokeWidth={1.75} className="text-destructive" />
                                </CardHeader>
                                <CardContent className="pt-0 text-2xl font-bold text-foreground">{formatearMoneda(datos.totalGastos)}</CardContent>
                            </Card>
                            <Card>
                                <CardHeader className="flex-row items-center justify-between pb-2">
                                    <CardTitle>Neto</CardTitle>
                                    <PiggyBank size={18} strokeWidth={1.75} className="text-secondary" />
                                </CardHeader>
                                <CardContent className="pt-0 text-2xl font-bold text-foreground">{formatearMoneda(datos.neto)}</CardContent>
                            </Card>
                            <Card>
                                <CardHeader className="flex-row items-center justify-between pb-2">
                                    <CardTitle>Propinas</CardTitle>
                                    <Banknote size={18} strokeWidth={1.75} className="text-accent" />
                                </CardHeader>
                                <CardContent className="pt-0 text-2xl font-bold text-foreground">{formatearMoneda(datos.totalPropinas)}</CardContent>
                            </Card>
                        </div>

                        <div className="grid gap-4 lg:grid-cols-2">
                            <Card>
                                <CardHeader>
                                    <CardTitle className="text-base text-foreground">Ingresos por método de pago</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <GraficaIngresosPorMetodo ingresosPorMetodo={datos.ingresosPorMetodo} />
                                </CardContent>
                            </Card>
                            <Card>
                                <CardHeader>
                                    <CardTitle className="text-base text-foreground">Ingresos vs. gastos</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <GraficaTendencia tendencia={datos.tendencia} />
                                </CardContent>
                            </Card>
                        </div>

                        <Card>
                            <CardHeader>
                                <CardTitle className="text-base text-foreground">Gastos del periodo</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <TablaGastos negocioSlug={negocioSlug} gastos={datos.gastos} onEliminado={invalidar} />
                            </CardContent>
                        </Card>
                    </>
                ) : null}
            </div>

            {dialogoAbierto && (
                <DialogoNuevoGasto
                    negocioSlug={negocioSlug}
                    onCerrar={() => setDialogoAbierto(false)}
                    onRegistrado={() => {
                        setDialogoAbierto(false);
                        invalidar();
                    }}
                />
            )}
        </LayoutPanelNegocio>
    );
}
