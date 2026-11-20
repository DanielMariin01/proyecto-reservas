<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Términos de Servicio | Sistema de Reservas</title>

        @vite(['resources/css/app.css'])
    </head>
    <body class="min-h-screen bg-fondo-panel">
        <main class="mx-auto max-w-3xl px-4 py-16">
            <div class="rounded-tarjeta bg-blanco p-8 shadow-tarjeta md:p-12">
                <h1 class="mb-2 text-2xl font-bold text-tinta">Términos de Servicio</h1>
                <p class="mb-8 text-sm text-texto-suave">Última actualización: 20 de noviembre de 2026</p>

                <div class="space-y-6 text-sm leading-relaxed text-tinta [&_h2]:mb-2 [&_h2]:mt-6 [&_h2]:text-lg [&_h2]:font-semibold [&_h2]:text-tinta [&_p]:mb-3 [&_ul]:mb-3 [&_ul]:list-disc [&_ul]:pl-5 [&_ul]:space-y-1">
                    <p>
                        Estos Términos de Servicio ("Términos") regulan el uso de Sistema de Reservas (en adelante,
                        "la Plataforma"), un software como servicio que permite a negocios de belleza en Colombia
                        —barberías, spas y salones— gestionar reservas, cursos y tienda, y a sus clientes agendar y
                        administrar citas. Al usar la Plataforma, ya sea como negocio o como cliente final, aceptas
                        estos Términos.
                    </p>

                    <h2>1. Quién puede usar la Plataforma</h2>
                    <p>
                        La Plataforma está dirigida a negocios legalmente establecidos en Colombia y a sus clientes.
                        Si usas la Plataforma en nombre de un negocio, declaras tener la facultad para aceptar estos
                        Términos en su representación.
                    </p>

                    <h2>2. Cuentas de negocio</h2>
                    <ul>
                        <li>Cada negocio es responsable de la veracidad de la información que publica en su página pública (servicios, precios, horarios, ubicación, contacto).</li>
                        <li>Cada negocio es responsable de mantener la confidencialidad de las credenciales de acceso de su equipo.</li>
                        <li>El acceso de un negocio a la Plataforma está sujeto al pago oportuno de su suscripción. Un negocio con pagos vencidos puede quedar suspendido o, pasado un periodo prolongado, cancelado conforme a las condiciones comerciales vigentes.</li>
                        <li>Un negocio suspendido o cancelado no puede recibir reservas nuevas ni acceder a su panel hasta regularizar su situación.</li>
                    </ul>

                    <h2>3. Reservas de clientes finales</h2>
                    <ul>
                        <li>La reserva de una cita, curso o producto es un acuerdo directo entre el cliente final y el negocio correspondiente. La Plataforma es solo el medio técnico para gestionarla.</li>
                        <li>Las políticas de cancelación, reprogramación y pago (incluyendo métodos manuales de pago) son definidas por cada negocio, no por la Plataforma.</li>
                        <li>El cliente final es responsable de la veracidad de los datos que entrega al reservar (nombre, celular, correo).</li>
                    </ul>

                    <h2>4. Uso aceptable</h2>
                    <p>Al usar la Plataforma, te comprometes a no:</p>
                    <ul>
                        <li>Usarla para fines ilegales, fraudulentos o que infrinjan derechos de terceros.</li>
                        <li>Intentar acceder sin autorización a datos de otro negocio o de otro usuario.</li>
                        <li>Interferir con el funcionamiento normal de la Plataforma (por ejemplo, mediante ataques automatizados o reservas falsas masivas).</li>
                        <li>Suplantar la identidad de otra persona o negocio.</li>
                    </ul>

                    <h2>5. Propiedad intelectual</h2>
                    <p>
                        El software, diseño y marca de Sistema de Reservas son propiedad de su operador. El contenido
                        que cada negocio publica (fotos, descripciones, logo) sigue siendo propiedad del negocio, que
                        otorga a la Plataforma una licencia limitada para mostrarlo en su página pública mientras su
                        cuenta esté activa.
                    </p>

                    <h2>6. Disponibilidad del servicio</h2>
                    <p>
                        Hacemos esfuerzos razonables para mantener la Plataforma disponible, pero no garantizamos un
                        funcionamiento ininterrumpido o libre de errores. Podemos realizar mantenimientos programados
                        que interrumpan temporalmente el servicio.
                    </p>

                    <h2>7. Limitación de responsabilidad</h2>
                    <p>
                        La Plataforma no es responsable por disputas entre un negocio y sus clientes finales (calidad
                        del servicio prestado, incumplimiento de citas, reembolsos, etc.), ya que estas relaciones son
                        ajenas a la Plataforma. En la medida permitida por la ley colombiana, nuestra responsabilidad
                        se limita a los casos de dolo o culpa grave en la operación del software.
                    </p>

                    <h2>8. Terminación</h2>
                    <p>
                        Un negocio puede cancelar su suscripción en cualquier momento. Podemos suspender o cancelar una
                        cuenta que incumpla gravemente estos Términos, previniendo además el acceso a datos de otros
                        negocios o usuarios. Tras la cancelación, los datos del negocio se conservan por un periodo
                        limitado antes de su eliminación definitiva, conforme a nuestra
                        <a class="text-primario underline" href="{{ route('politica.privacidad') }}">Política de Privacidad</a>.
                    </p>

                    <h2>9. Cambios a estos Términos</h2>
                    <p>
                        Podemos actualizar estos Términos para reflejar cambios en la Plataforma o en la normativa
                        aplicable. Publicaremos cualquier cambio en esta misma página con su fecha de actualización.
                    </p>

                    <h2>10. Ley aplicable</h2>
                    <p>
                        Estos Términos se rigen por las leyes de la República de Colombia. Cualquier controversia se
                        someterá a los jueces competentes de Colombia.
                    </p>

                    <h2>11. Contacto</h2>
                    <p>
                        Para preguntas sobre estos Términos, escríbenos a
                        <a class="text-primario underline" href="mailto:notificaciones@marintech.site">notificaciones@marintech.site</a>.
                    </p>
                </div>

                <a href="{{ url('/') }}" class="mt-8 inline-block text-sm font-semibold text-primario hover:text-acento">← Volver al inicio</a>
            </div>
        </main>
    </body>
</html>
