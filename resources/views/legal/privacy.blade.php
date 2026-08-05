{{-- Página pública de Política de Privacidad --}}
@extends('layouts.landing')

@section('title', 'Política de Privacidad')

@section('content')
<div class="max-w-4xl mx-auto px-6 lg:px-8 py-16">
    <nav class="text-sm text-slate-500 mb-6">
        <a href="{{ route('home') }}" class="hover:text-mso-gold transition-colors">Inicio</a>
        <span class="mx-2">/</span>
        <span class="text-slate-700">Política de Privacidad</span>
    </nav>

    <h1 class="font-serif text-3xl md:text-4xl font-bold text-slate-900 mb-2">Política de Privacidad</h1>
    <p class="text-slate-500 mb-10">Última actualización: {{ date('d \d\e F \d\e Y') }}</p>

    <div class="space-y-10 text-slate-700 leading-relaxed">

        <section>
            <h2 class="font-bold text-xl text-slate-900 mb-3">1. Introducción</h2>
            <p class="mb-3">
                En <strong>MSO Grupo Inmobiliario</strong> nos comprometemos a proteger la privacidad y los datos
                personales de nuestros usuarios, visitantes y clientes. Esta Política de Privacidad describe qué
                datos recopilamos, con qué finalidad, cómo los tratamos, qué derechos tiene usted y la normativa
                que aplicamos, tanto venezolana como internacional.
            </p>
            <p>
                Esta Política debe leerse en conjunto con nuestros <a href="{{ route('legal.terms') }}" class="text-mso-gold underline">Términos y Condiciones</a>
                y nuestra <a href="{{ route('legal.cookies') }}" class="text-mso-gold underline">Política de Cookies</a>.
            </p>
        </section>

        <section>
            <h2 class="font-bold text-xl text-slate-900 mb-3">2. Responsable del tratamiento</h2>
            <p class="mb-3">
                El responsable del tratamiento de los datos personales es <strong>MSO Grupo Inmobiliario</strong>,
                con presencia en Venezuela y contacto a través de:
            </p>
            <ul class="list-disc pl-6 space-y-1">
                <li>Correo electrónico: <a href="mailto:msogruoinmobilirio.2023@gmail.com" class="text-mso-gold underline">msogruoinmobilirio.2023@gmail.com</a></li>
                <li>Teléfono: +58 426-9077422</li>
                <li>Dirección: Centro Comercial Buena Aventura, Venezuela</li>
            </ul>
        </section>

        <section>
            <h2 class="font-bold text-xl text-slate-900 mb-3">3. Datos personales que recopilamos</h2>
            <p class="mb-3">Recopilamos únicamente los datos necesarios para los fines descritos en esta Política:</p>
            <ul class="list-disc pl-6 space-y-2">
                <li><strong>Datos de registro de cuenta:</strong> nombre, apellido, correo electrónico, teléfono y credenciales de acceso.</li>
                <li><strong>Datos de solicitudes de valoración / leads:</strong> nombre, apellido, correo, teléfono, tipo de interés (compra, alquiler, venta, asesoría), dirección y características de la propiedad, y observaciones.</li>
                <li><strong>Datos de citas:</strong> fechas y horarios de las citas solicitadas y su motivo.</li>
                <li><strong>Datos de contacto:</strong> mensajes enviados por WhatsApp, correo u otros canales.</li>
                <li><strong>Datos técnicos:</strong> dirección IP, tipo de navegador, dispositivo y páginas visitadas, recopilados mediante cookies (ver <a href="{{ route('legal.cookies') }}" class="text-mso-gold underline">Política de Cookies</a>).</li>
                <li><strong>Datos de auditoría:</strong> registros de acciones realizadas en el sistema con fines de seguridad y trazabilidad.</li>
            </ul>
        </section>

        <section>
            <h2 class="font-bold text-xl text-slate-900 mb-3">4. Finalidades y bases de legitimación</h2>
            <p class="mb-3">Tratamos sus datos personales para:</p>
            <ul class="list-disc pl-6 space-y-2">
                <li><strong>Prestación de servicios:</strong> gestionar su cuenta, publicar y mostrar propiedades, procesar solicitudes de valoración y gestionar citas (base: ejecución de un contrato o solicitud precontractual).</li>
                <li><strong>Atención al cliente:</strong> responder consultas y canalizar solicitudes con nuestros asesores (base: interés legítimo o ejecución de una solicitud).</li>
                <li><strong>Seguridad y prevención del fraude:</strong> registrar y auditar accesos para proteger la Plataforma (base: interés legítimo y cumplimiento normativo).</li>
                <li><strong>Envíos de información:</strong> comunicaciones relacionadas con sus solicitudes (base: interés legítimo). En caso de boletines o campañas comerciales, solicitaremos su consentimiento previo.</li>
            </ul>
        </section>

        <section>
            <h2 class="font-bold text-xl text-slate-900 mb-3">5. Consentimiento</h2>
            <p>
                Cuando el tratamiento se base en su consentimiento (por ejemplo, aceptación de cookies no esenciales
                o comunicaciones comerciales), usted podrá retirarlo en cualquier momento sin efectos retroactivos,
                contactándonos por los canales indicados o mediante la gestión de preferencias del Sitio.
            </p>
        </section>

        <section>
            <h2 class="font-bold text-xl text-slate-900 mb-3">6. Sus derechos</h2>
            <p class="mb-3">
                De acuerdo con la legislación venezolana de protección de datos personales y, en lo aplicable, con el
                Reglamento General de Protección de Datos de la Unión Europea (RGPD), usted tiene derecho a:
            </p>
            <ul class="list-disc pl-6 space-y-2">
                <li><strong>Acceso:</strong> conocer qué datos tratamos y con qué finalidad.</li>
                <li><strong>Rectificación:</strong> solicitar la corrección de datos inexactos o incompletos.</li>
                <li><strong>Supresión / cancelación:</strong> solicitar la eliminación de sus datos cuando ya no sean necesarios o se haya retirado el consentimiento.</li>
                <li><strong>Oposición:</strong> oponerse al tratamiento en determinados supuestos, incluido el interés legítimo.</li>
                <li><strong>Limitación:</strong> solicitar la suspensión del tratamiento en los casos previstos por la ley.</li>
                <li><strong>Portabilidad:</strong> recibir sus datos en un formato estructurado y de uso común, en el ámbito del RGPD.</li>
                <li><strong>No ser objeto de decisiones automatizadas</strong> que produzcan efectos jurídicos significativos.</li>
            </ul>
            <p class="mt-3">
                Puede ejercer estos derechos enviando su solicitud a
                <a href="mailto:msogruoinmobilirio.2023@gmail.com" class="text-mso-gold underline">msogruoinmobilirio.2023@gmail.com</a>,
                acreditando su identidad. Asimismo, cuando corresponda, podrá presentar una reclamación ante la
                autoridad de control competente.
            </p>
        </section>

        <section>
            <h2 class="font-bold text-xl text-slate-900 mb-3">7. Conservación de los datos</h2>
            <p>
                Conservamos sus datos personales únicamente durante el tiempo necesario para cumplir las finalidades
                descritas, salvo que una norma legal exija un plazo de conservación mayor. Una vez agotadas las
                finalidades, los datos serán suprimidos o anonimizados.
            </p>
        </section>

        <section>
            <h2 class="font-bold text-xl text-slate-900 mb-3">8. Cesión y transferencia de datos</h2>
            <p class="mb-3">
                No vendemos ni alquilamos datos personales. Sus datos pueden ser comunicados a:
            </p>
            <ul class="list-disc pl-6 space-y-2">
                <li>Asesores inmobiliarios de la Plataforma, para gestionar las solicitudes y citas.</li>
                <li>Proveedores de servicios tecnológicos (alojamiento, correo electrónico, servicios en la nube) que actúan como encargados del tratamiento.</li>
                <li>Autoridades competentes cuando exista obligación legal.</li>
            </ul>
            <p class="mt-3">
                En caso de transferencias internacionales, aplicaremos las garantías adecuadas exigidas por el RGPD
                y la normativa venezolana.
            </p>
        </section>

        <section>
            <h2 class="font-bold text-xl text-slate-900 mb-3">9. Seguridad de la información</h2>
            <p>
                Implementamos medidas técnicas y organizativas apropiadas para proteger sus datos contra el acceso no
                autorizado, la alteración, la divulgación o la destrucción, conforme a la
                <em>Ley Especial contra los Delitos Informáticos</em> y las buenas prácticas de seguridad de la información.
            </p>
        </section>

        <section>
            <h2 class="font-bold text-xl text-slate-900 mb-3">10. Menores de edad</h2>
            <p>
                El Sitio no está dirigido a menores de edad. No recopilamos de forma consciente datos personales de
                menores sin el consentimiento de su representante legal, de conformidad con la legislación venezolana.
            </p>
        </section>

        <section>
            <h2 class="font-bold text-xl text-slate-900 mb-3">11. Normativa aplicable</h2>
            <p class="mb-3">Esta Política se elabora conforme a las siguientes normas:</p>
            <ul class="list-disc pl-6 space-y-2">
                <li>Constitución de la República Bolivariana de Venezuela (artículos 28, 58 y 60).</li>
                <li>Ley Orgánica de Protección de Datos Personales (LOPDP).</li>
                <li>Ley de Infogobierno.</li>
                <li>Ley Especial contra los Delitos Informáticos.</li>
                <li>Ley de Responsabilidad Social en Radio, Televisión y Medios Electrónicos.</li>
                <li>Reglamento (UE) 2016/679 (RGPD) y Directiva 2002/58/CE (ePrivacy), en lo aplicable a usuarios en la Unión Europea.</li>
                <li>California Consumer Privacy Act (CCPA/CPRA), en lo aplicable a residentes de California.</li>
            </ul>
        </section>

        <section>
            <h2 class="font-bold text-xl text-slate-900 mb-3">12. Cambios en esta Política</h2>
            <p>
                Podemos actualizar esta Política de Privacidad. La fecha de la última versión se indicará al inicio de
                esta página. Le recomendamos revisarla periódicamente.
            </p>
        </section>

        <section>
            <h2 class="font-bold text-xl text-slate-900 mb-3">13. Contacto</h2>
            <p>
                Para cualquier consulta sobre el tratamiento de sus datos personales o el ejercicio de sus derechos,
                escríbanos a <a href="mailto:msogruoinmobilirio.2023@gmail.com" class="text-mso-gold underline">msogruoinmobilirio.2023@gmail.com</a>.
            </p>
        </section>
    </div>
</div>
@endsection
