{{-- Página pública de Términos y Condiciones --}}
@extends('layouts.landing')

@section('title', 'Términos y Condiciones')

@section('content')
<div class="max-w-4xl mx-auto px-6 lg:px-8 py-16">
    <nav class="text-sm text-slate-500 mb-6">
        <a href="{{ route('home') }}" class="hover:text-mso-gold transition-colors">Inicio</a>
        <span class="mx-2">/</span>
        <span class="text-slate-700">Términos y Condiciones</span>
    </nav>

    <h1 class="font-serif text-3xl md:text-4xl font-bold text-slate-900 mb-2">Términos y Condiciones</h1>
    <p class="text-slate-500 mb-10">Última actualización: {{ date('d \d\e F \d\e Y') }}</p>

    <div class="space-y-10 text-slate-700 leading-relaxed">

        <section>
            <h2 class="font-bold text-xl text-slate-900 mb-3">1. Aceptación de los Términos</h2>
            <p class="mb-3">
                Al acceder y utilizar el sitio web de <strong>MSO Grupo Inmobiliario</strong> (en adelante,
                "la Plataforma", "el Sitio" o "nosotros"), usted acepta cumplir con los presentes
                <strong>Términos y Condiciones</strong>, la <a href="{{ route('legal.privacy') }}" class="text-mso-gold underline">Política de Privacidad</a>
                y la <a href="{{ route('legal.cookies') }}" class="text-mso-gold underline">Política de Cookies</a>.
                Si no está de acuerdo con alguno de estos términos, le solicitamos que no utilice el Sitio.
            </p>
            <p>
                Estos Términos se rigen por el ordenamiento jurídico de la República Bolivariana de Venezuela y, de
                forma supletoria y en lo que corresponda, por la normativa internacional aplicable en materia de
                comercio electrónico y protección de datos personales.
            </p>
        </section>

        <section>
            <h2 class="font-bold text-xl text-slate-900 mb-3">2. Objeto</h2>
            <p class="mb-3">
                El Sitio tiene como finalidad publicar y difundir bienes inmuebles (venta y alquiler),
                brindar información sobre servicios inmobiliarios, facilitar el contacto entre interesados
                y asesores inmobiliarios, la solicitud de valoración de propiedades, la gestión de citas
                y el envío de consultas a través de los canales de contacto habilitados.
            </p>
            <p>
                Toda la información publicada en el Sitio tiene carácter meramente informativo y no constituye
                oferta vinculante. Las condiciones, precios y disponibilidad de los inmuebles deben ser
                confirmados directamente con un asesor inmobiliario de la Plataforma.
            </p>
        </section>

        <section>
            <h2 class="font-bold text-xl text-slate-900 mb-3">3. Registro y cuentas de usuario</h2>
            <ul class="list-disc pl-6 space-y-2 mb-3">
                <li>Para acceder a determinadas funcionalidades (favoritos, solicitudes, seguimiento de citas) es necesario crear una cuenta proporcionando datos veraces.</li>
                <li>Usted es responsable de la confidencialidad de sus credenciales de acceso y de todas las actividades realizadas bajo su cuenta.</li>
                <li>La Plataforma se reserva el derecho de suspender o eliminar cuentas que vulneren estos Términos o la normativa vigente.</li>
                <li>Los menores de edad no pueden registrarse sin la autorización de su representante legal, conforme a la legislación venezolana.</li>
            </ul>
            <p>
                El tratamiento de sus datos personales se realiza conforme a nuestra
                <a href="{{ route('legal.privacy') }}" class="text-mso-gold underline">Política de Privacidad</a>.
            </p>
        </section>

        <section>
            <h2 class="font-bold text-xl text-slate-900 mb-3">4. Uso permitido del Sitio</h2>
            <p class="mb-3">El usuario se compromete a utilizar el Sitio únicamente para fines legales y de conformidad con la normativa venezolana e internacional, en especial la <em>Ley Especial contra los Delitos Informáticos</em>. Queda expresamente prohibido:</p>
            <ul class="list-disc pl-6 space-y-2">
                <li>Utilizar el Sitio para actividades ilícitas o que vulneren derechos de terceros.</li>
                <li>Intentar acceder sin autorización a áreas restringidas, bases de datos o sistemas de la Plataforma.</li>
                <li>Reproducir, copiar, distribuir o explotar comercialmente los contenidos sin autorización previa por escrito.</li>
                <li>Introducir virus, malware o cualquier código que dañe o interfiera con el funcionamiento del Sitio.</li>
                <li>Enviar mensajes no solicitados (spam), publicidad no autorizada o suplantar la identidad de terceros.</li>
            </ul>
        </section>

        <section>
            <h2 class="font-bold text-xl text-slate-900 mb-3">5. Propiedad intelectual</h2>
            <p class="mb-3">
                Todos los contenidos del Sitio (marcas, logotipos, textos, gráficos, fotografías, diseños y software)
                son titularidad de MSO Grupo Inmobiliario o de terceros que han autorizado su uso, y están protegidos
                por la legislación venezolana sobre propiedad intelectual e industrial y por los tratados
                internacionales aplicables.
            </p>
            <p>
                Queda prohibida la reproducción, modificación, distribución o uso comercial de dichos contenidos sin
                la autorización escrita de su titular. El uso indebido podrá dar lugar a las acciones civiles y
                penales correspondientes.
            </p>
        </section>

        <section>
            <h2 class="font-bold text-xl text-slate-900 mb-3">6. Responsabilidad y exención de garantías</h2>
            <ul class="list-disc pl-6 space-y-2 mb-3">
                <li>La información sobre los inmuebles es suministrada por los propietarios, desarrolladores o asesores, por lo que la Plataforma no garantiza su exactitud, integridad o vigencia.</li>
                <li>La Plataforma actúa como intermediaria de información y no es responsable de las negociaciones, contratos o acuerdos celebrados entre las partes.</li>
                <li>No se garantiza la disponibilidad ininterrumpida o libre de errores del Sitio; se realizarán los esfuerzos razonables para su correcto funcionamiento.</li>
                <li>La Plataforma no será responsable por daños indirectos, lucro cesante o pérdida de datos derivados del uso del Sitio, salvo lo dispuesto por normas de orden público de la legislación venezolana.</li>
            </ul>
            <p>
                Los usuarios son responsables de verificar de forma independiente la información de los inmuebles y
                de cumplir con la normativa aplicable (incluyendo la <em>Ley de Propiedad Horizontal</em> y la
                <em>Ley de Corretaje con Inmuebles</em>, en lo que corresponda).
            </p>
        </section>

        <section>
            <h2 class="font-bold text-xl text-slate-900 mb-3">7. Enlaces a sitios de terceros</h2>
            <p>
                El Sitio puede contener enlaces a sitios externos (WhatsApp, Instagram, redes sociales, servicios
                en la nube). La Plataforma no controla ni asume responsabilidad alguna por el contenido, las
                políticas de privacidad o las prácticas de dichos sitios. Le recomendamos revisar sus respectivas
                políticas antes de proporcionarles información personal.
            </p>
        </section>

        <section>
            <h2 class="font-bold text-xl text-slate-900 mb-3">8. Modificaciones de los Términos</h2>
            <p>
                Nos reservamos el derecho de modificar estos Términos y Condiciones en cualquier momento.
                Las modificaciones entrarán en vigor desde su publicación en el Sitio. El uso continuado del Sitio
                después de la publicación de cambios constituye aceptación de los mismos.
            </p>
        </section>

        <section>
            <h2 class="font-bold text-xl text-slate-900 mb-3">9. Normativa aplicable y jurisdicción</h2>
            <p class="mb-3">
                Estos Términos se rigen por las leyes de la República Bolivariana de Venezuela. En la interpretación
                y aplicación de los mismos se tomarán en cuenta, entre otras, las siguientes normas:
            </p>
            <ul class="list-disc pl-6 space-y-2">
                <li>Constitución de la República Bolivariana de Venezuela (artículos 28, 58 y 60).</li>
                <li>Código Civil venezolano.</li>
                <li>Ley Especial contra los Delitos Informáticos.</li>
                <li>Ley de Infogobierno.</li>
                <li>Ley Orgánica de Protección de Datos Personales (en vigor conforme a su normativa de aplicación).</li>
                <li>Ley de Responsabilidad Social en Radio, Televisión y Medios Electrónicos.</li>
                <li>Reglamento (UE) 2016/679 del Parlamento Europeo y del Consejo (RGPD/GDPR), en lo que resulte aplicable a usuarios residentes en la Unión Europea.</li>
            </ul>
            <p class="mt-3">
                Para la resolución de controversias, las partes se someterán a los tribunales competentes de la
                República Bolivariana de Venezuela.
            </p>
        </section>

        <section>
            <h2 class="font-bold text-xl text-slate-900 mb-3">10. Contacto</h2>
            <p>
                Para consultas relacionadas con estos Términos y Condiciones, puede contactarnos por correo
                electrónico a <a href="mailto:msogruoinmobilirio.2023@gmail.com" class="text-mso-gold underline">msogruoinmobilirio.2023@gmail.com</a>
                o a través de los canales de contacto disponibles en el Sitio.
            </p>
        </section>
    </div>
</div>
@endsection
