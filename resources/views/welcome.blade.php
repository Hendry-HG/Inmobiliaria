{{-- Página de bienvenida predeterminada de Laravel --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inmobiliaria - Inicio</title>
    
    <!-- Tailwind CSS (Vía CDN para funcionamiento inmediato sin instalación) -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- FontAwesome para Iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Configuración personalizada de Tailwind -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Montserrat', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            dark: '#0f172a',   // Azul oscuro noche
                            primary: '#334155', // Gris azulado
                            accent: '#3b82f6',  // Azul brillante
                            gold: '#eab308'     // Dorado para detalles premium
                        }
                    }
                }
            }
        }
    </script>

    <style>
        /* Estilos personalizados adicionales */
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #f8fafc;
        }
        
        /* Efecto de gradiente sobre la imagen Hero para mejorar lectura */
        .hero-overlay {
            background: linear-gradient(to bottom, rgba(15, 23, 42, 0.6), rgba(15, 23, 42, 0.9));
        }

        /* Transiciones suaves */
        .property-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .property-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        }

        /* Animación del menú móvil */
        #mobile-menu {
            transition: max-height 0.3s ease-in-out, opacity 0.3s ease-in-out;
            max-height: 0;
            opacity: 0;
            overflow: hidden;
        }
        #mobile-menu.open {
            max-height: 400px;
            opacity: 1;
        }
    </style>
</head>
<body class="flex flex-col min-h-screen text-slate-800">

    <!-- HEADER / NAVEGACIÓN -->
    <header class="fixed w-full top-0 z-50 bg-brand-dark/95 backdrop-blur-sm text-white shadow-lg border-b border-white/10">
        <div class="container mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                
                <!-- Logo -->
                <a href="#" class="text-2xl font-bold tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-city text-brand-accent"></i>
                    INMO<span class="text-brand-accent">LAR</span>
                </a>

                <!-- Menú Escritorio -->
                <nav class="hidden md:flex space-x-8 items-center">
                    <a href="#" class="text-sm font-medium hover:text-brand-accent transition-colors uppercase tracking-wide">Inicio</a>
                    <a href="#" class="text-sm font-medium hover:text-brand-accent transition-colors uppercase tracking-wide">Catálogo</a>
                    <a href="#" class="text-sm font-medium hover:text-brand-accent transition-colors uppercase tracking-wide">Servicios</a>
                    <a href="#" class="text-sm font-medium hover:text-brand-accent transition-colors uppercase tracking-wide">Nosotros</a>
                    <a href="#" class="text-sm font-medium hover:text-brand-accent transition-colors uppercase tracking-wide">Contacto</a>
                </nav>

                <!-- Botón de acción (Escritorio) -->
                <div class="hidden md:block">
                    <button class="bg-brand-accent hover:bg-blue-600 text-white px-6 py-2 rounded-full font-medium transition-all shadow-lg shadow-blue-500/30">
                        Publicar Propiedad
                    </button>
                </div>

                <!-- Botón Menú Móvil -->
                <button id="mobile-menu-btn" class="md:hidden text-2xl focus:outline-none">
                    <i class="fa-solid fa-bars"></i>
                </button>
            </div>

            <!-- Menú Móvil (Desplegable) -->
            <div id="mobile-menu" class="md:hidden mt-4 border-t border-white/10 pt-4 flex flex-col space-y-4">
                <a href="#" class="block text-sm font-medium hover:text-brand-accent">Inicio</a>
                <a href="#" class="block text-sm font-medium hover:text-brand-accent">Catálogo</a>
                <a href="#" class="block text-sm font-medium hover:text-brand-accent">Servicios</a>
                <a href="#" class="block text-sm font-medium hover:text-brand-accent">Contacto</a>
                <button class="w-full bg-brand-accent py-2 rounded-lg text-sm font-medium">Publicar Propiedad</button>
            </div>
        </div>
    </header>

    <!-- HERO SECTION (Imagen Panorámica Nocturna) -->
    <section class="relative h-screen flex items-center justify-center overflow-hidden">
        <!-- Imagen de fondo (Ciudad nocturna) -->
        <div class="absolute inset-0 z-0">
            <img src="https://picsum.photos/seed/nightcity/1920/1080" alt="Ciudad nocturna" class="w-full h-full object-cover">
            <div class="absolute inset-0 hero-overlay"></div>
        </div>

        <!-- Contenido Hero -->
        <div class="relative z-10 text-center px-4 max-w-4xl mx-auto mt-16">
            <h1 class="text-4xl md:text-6xl font-bold text-white mb-6 drop-shadow-lg">
                Encuentra tu espacio ideal <br> <span class="text-brand-accent">en la ciudad</span>
            </h1>
            <p class="text-lg text-gray-200 mb-10 max-w-2xl mx-auto font-light">
                Explora las mejores propiedades en las zonas más exclusivas. Tu nuevo hogar te espera.
            </p>

            <!-- Botones de Acción Principal -->
            <div class="flex flex-col sm:flex-row justify-center gap-4 mb-12">
                <button class="bg-brand-accent hover:bg-blue-600 text-white px-8 py-3 rounded-lg font-semibold shadow-lg transition-transform transform hover:scale-105">
                    Comprar
                </button>
                <button class="bg-white/10 hover:bg-white/20 backdrop-blur-md text-white border border-white/30 px-8 py-3 rounded-lg font-semibold transition-all">
                    Alquilar
                </button>
            </div>

            <!-- Barra de Búsqueda / Filtros -->
            <div class="bg-white rounded-xl p-4 md:p-6 shadow-2xl max-w-5xl mx-auto transform transition-all hover:shadow-blue-500/20">
                <form class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Ubicación -->
                    <div class="relative group">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1 ml-1">Ubicación</label>
                        <div class="flex items-center bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 group-hover:border-brand-accent transition-colors">
                            <i class="fa-solid fa-location-dot text-gray-400 mr-2"></i>
                            <input type="text" placeholder="Ciudad, Barrio..." class="bg-transparent w-full outline-none text-sm text-gray-700 placeholder-gray-400">
                        </div>
                    </div>

                    <!-- Tipo de Propiedad -->
                    <div class="relative group">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1 ml-1">Tipo</label>
                        <div class="flex items-center bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 group-hover:border-brand-accent transition-colors">
                            <i class="fa-solid fa-house text-gray-400 mr-2"></i>
                            <select class="bg-transparent w-full outline-none text-sm text-gray-700 appearance-none cursor-pointer">
                                <option>Casa</option>
                                <option>Apartamento</option>
                                <option>Oficina</option>
                                <option>Terroreno</option>
                            </select>
                            <i class="fa-solid fa-chevron-down text-gray-400 text-xs ml-auto"></i>
                        </div>
                    </div>

                    <!-- Precio -->
                    <div class="relative group">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1 ml-1">Rango de Precio</label>
                        <div class="flex items-center bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 group-hover:border-brand-accent transition-colors">
                            <i class="fa-solid fa-dollar-sign text-gray-400 mr-2"></i>
                            <select class="bg-transparent w-full outline-none text-sm text-gray-700 appearance-none cursor-pointer">
                                <option>Cualquiera</option>
                                <option>$50k - $100k</option>
                                <option>$100k - $200k</option>
                                <option>$200k+</option>
                            </select>
                            <i class="fa-solid fa-chevron-down text-gray-400 text-xs ml-auto"></i>
                        </div>
                    </div>

                    <!-- Botón Buscar -->
                    <div class="flex items-end">
                        <button type="button" class="w-full bg-brand-dark hover:bg-black text-white font-semibold py-2.5 rounded-lg transition-colors flex justify-center items-center gap-2">
                            <i class="fa-solid fa-magnifying-glass"></i> Buscar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- SECCIÓN PROPIEDADES DESTACADAS -->
    <section class="py-20 container mx-auto px-6">
        <div class="flex justify-between items-end mb-10">
            <div>
                <h2 class="text-3xl font-bold text-brand-dark mb-2">Propiedades Destacadas</h2>
                <p class="text-gray-500">Selección exclusiva de mejores oportunidades.</p>
            </div>
            <a href="#" class="hidden md:inline-block text-brand-accent font-semibold hover:underline">Ver todo el catálogo &rarr;</a>
        </div>

        <!-- Grid de Propiedades -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            
            <!-- Tarjeta 1 -->
            <article class="property-card bg-white rounded-2xl overflow-hidden shadow-md border border-gray-100 flex flex-col h-full">
                <div class="relative h-64">
                    <span class="absolute top-4 left-4 bg-brand-accent text-white text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wide z-10">Venta</span>
                    <img src="https://picsum.photos/seed/house1/800/600" alt="Casa con Jardín" class="w-full h-full object-cover">
                    <button class="absolute bottom-4 right-4 bg-white p-2 rounded-full text-gray-400 hover:text-red-500 shadow-md transition-colors">
                        <i class="fa-regular fa-heart"></i>
                    </button>
                </div>
                <div class="p-6 flex flex-col flex-grow">
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="text-xl font-bold text-brand-dark hover:text-brand-accent cursor-pointer transition-colors">Casa con Jardín en El Hatillo</h3>
                        <span class="text-brand-gold font-bold text-lg">$450,000</span>
                    </div>
                    <p class="text-gray-400 text-sm mb-4"><i class="fa-solid fa-map-pin mr-1"></i> El Hatillo, Caracas</p>
                    
                    <div class="flex justify-between items-center border-t border-gray-100 pt-4 mt-auto">
                        <div class="flex gap-4 text-sm text-gray-500">
                            <span class="flex items-center gap-1"><i class="fa-solid fa-bed"></i> 4</span>
                            <span class="flex items-center gap-1"><i class="fa-solid fa-bath"></i> 3</span>
                            <span class="flex items-center gap-1"><i class="fa-solid fa-ruler-combined"></i> 250m²</span>
                        </div>
                    </div>
                </div>
            </article>

            <!-- Tarjeta 2 -->
            <article class="property-card bg-white rounded-2xl overflow-hidden shadow-md border border-gray-100 flex flex-col h-full">
                <div class="relative h-64">
                    <span class="absolute top-4 left-4 bg-emerald-500 text-white text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wide z-10">Alquiler</span>
                    <img src="https://picsum.photos/seed/apt2/800/600" alt="Apartamento Moderno" class="w-full h-full object-cover">
                    <button class="absolute bottom-4 right-4 bg-white p-2 rounded-full text-gray-400 hover:text-red-500 shadow-md transition-colors">
                        <i class="fa-regular fa-heart"></i>
                    </button>
                </div>
                <div class="p-6 flex flex-col flex-grow">
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="text-xl font-bold text-brand-dark hover:text-brand-accent cursor-pointer transition-colors">Apartamento Vista Lago</h3>
                        <span class="text-brand-gold font-bold text-lg">$1,200<span class="text-sm text-gray-400 font-normal">/mes</span></span>
                    </div>
                    <p class="text-gray-400 text-sm mb-4"><i class="fa-solid fa-map-pin mr-1"></i> Valencia, Edo. Carabobo</p>
                    
                    <div class="flex justify-between items-center border-t border-gray-100 pt-4 mt-auto">
                        <div class="flex gap-4 text-sm text-gray-500">
                            <span class="flex items-center gap-1"><i class="fa-solid fa-bed"></i> 2</span>
                            <span class="flex items-center gap-1"><i class="fa-solid fa-bath"></i> 2</span>
                            <span class="flex items-center gap-1"><i class="fa-solid fa-ruler-combined"></i> 120m²</span>
                        </div>
                    </div>
                </div>
            </article>

            <!-- Tarjeta 3 -->
            <article class="property-card bg-white rounded-2xl overflow-hidden shadow-md border border-gray-100 flex flex-col h-full">
                <div class="relative h-64">
                    <span class="absolute top-4 left-4 bg-brand-accent text-white text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wide z-10">Venta</span>
                    <img src="https://picsum.photos/seed/office3/800/600" altOficina "Oficina Corporativa" class="w-full h-full object-cover">
                    <button class="absolute bottom-4 right-4 bg-white p-2 rounded-full text-gray-400 hover:text-red-500 shadow-md transition-colors">
                        <i class="fa-regular fa-heart"></i>
                    </button>
                </div>
                <div class="p-6 flex flex-col flex-grow">
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="text-xl font-bold text-brand-dark hover:text-brand-accent cursor-pointer transition-colors">Oficina en Centro Financiero</h3>
                        <span class="text-brand-gold font-bold text-lg">$890,000</span>
                    </div>
                    <p class="text-gray-400 text-sm mb-4"><i class="fa-solid fa-map-pin mr-1"></i> Las Mercedes, Caracas</p>
                    
                    <div class="flex justify-between items-center border-t border-gray-100 pt-4 mt-auto">
                        <div class="flex gap-4 text-sm text-gray-500">
                            <span class="flex items-center gap-1"><i class="fa-solid fa-users"></i> 15</span>
                            <span class="flex items-center gap-1"><i class="fa-solid fa-bath"></i> 4</span>
                            <span class="flex items-center gap-1"><i class="fa-solid fa-ruler-combined"></i> 450m²</span>
                        </div>
                    </div>
                </div>
            </article>

        </div>
        
        <div class="mt-10 text-center md:hidden">
            <a href="#" class="inline-block bg-gray-100 hover:bg-gray-200 text-brand-dark font-bold py-3 px-8 rounded-lg transition-colors">Ver todo el catálogo</a>
        </div>
    </section>

    <!-- SECCIÓN INFORMATIVA (Breve) -->
    <section class="bg-slate-900 text-white py-16">
        <div class="container mx-auto px-6 grid grid-cols-1 md:grid-cols-3 gap-10 text-center">
            <div class="p-4">
                <i class="fa-solid fa-handshake text-4xl text-brand-accent mb-4"></i>
                <h3 class="text-xl font-bold mb-2">Seguridad Jurídica</h3>
                <p class="text-gray-400 text-sm">Todos nuestros inmuebles cuentan con la debida verificación legal.</p>
            </div>
            <div class="p-4">
                <i class="fa-solid fa-headset text-4xl text-brand-accent mb-4"></i>
                <h3 class="text-xl font-bold mb-2">Asesoría 24/7</h3>
                <p class="text-gray-400 text-sm">Nuestro equipo está disponible para resolver tus dudas en cualquier momento.</p>
            </div>
            <div class="p-4">
                <i class="fa-solid fa-chart-line text-4xl text-brand-accent mb-4"></i>
                <h3 class="text-xl font-bold mb-2">Plusvalía Garantizada</h3>
                <p class="text-gray-400 text-sm">Invierte en zonas con alto crecimiento y valorización a futuro.</p>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="bg-gray-900 text-gray-300 py-12 mt-auto">
        <div class="container mx-auto px-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
                <!-- Columna 1: Brand -->
                <div>
                    <a href="#" class="text-2xl font-bold tracking-wider flex items-center gap-2 text-white mb-4">
                        <i class="fa-solid fa-city text-brand-accent"></i>
                        INMO<span class="text-brand-accent">LAR</span>
                    </a>
                    <p class="text-sm text-gray-500 leading-relaxed">
                        Tu socio confiable en bienes raíces. Encontramos el lugar perfecto para que vivas, trabajes o inviertas.
                    </p>
                </div>
                
                <!-- Columna 2: Enlaces -->
                <div>
                    <h4 class="text-white font-bold mb-4 uppercase text-sm tracking-wider">Enlaces Rápidos</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="hover:text-brand-accent transition-colors">Comprar</a></li>
                        <li><a href="#" class="hover:text-brand-accent transition-colors">Alquilar</a></li>
                        <li><a href="#" class="hover:text-brand-accent transition-colors">Vender</a></li>
                        <li><a href="#" class="hover:text-brand-accent transition-colors">Tasaciones</a></li>
                    </ul>
                </div>

                <!-- Columna 3: Contacto -->
                <div>
                    <h4 class="text-white font-bold mb-4 uppercase text-sm tracking-wider">Contacto</h4>
                    <ul class="space-y-3 text-sm">
                        <li class="flex items-start gap-3">
                            <i class="fa-solid fa-location-dot mt-1 text-brand-accent"></i>
                            <span>Av. Principal con Calle 10, Edif. Torre Norte, Piso 5.</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <i class="fa-solid fa-phone text-brand-accent"></i>
                            <span>+58 212 555 0123</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <i class="fa-solid fa-envelope text-brand-accent"></i>
                            <span>contacto@inmolar.com</span>
                        </li>
                    </ul>
                </div>

                <!-- Columna 4: Social -->
                <div>
                    <h4 class="text-white font-bold mb-4 uppercase text-sm tracking-wider">Síguenos</h4>
                    <div class="flex space-x-4">
                        <a href="#" class="w-10 h-10 rounded-full bg-gray-800 flex items-center justify-center hover:bg-brand-accent hover:text-white transition-all">
                            <i class="fa-brands fa-instagram"></i>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-full bg-gray-800 flex items-center justify-center hover:bg-brand-accent hover:text-white transition-all">
                            <i class="fa-brands fa-facebook-f"></i>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-full bg-gray-800 flex items-center justify-center hover:bg-brand-accent hover:text-white transition-all">
                            <i class="fa-brands fa-twitter"></i>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-full bg-gray-800 flex items-center justify-center hover:bg-brand-accent hover:text-white transition-all">
                            <i class="fa-brands fa-linkedin-in"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-800 pt-8 flex flex-col md:flex-row justify-between items-center text-xs text-gray-600">
                <p>&copy; 2023 Inmolar. Todos los derechos reservados.</p>
                <div class="flex space-x-6 mt-4 md:mt-0">
                    <a href="#" class="hover:text-gray-400">Privacidad</a>
                    <a href="#" class="hover:text-gray-400">Términos</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- JAVASCRIPT (Lógica del menú) -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const btn = document.getElementById('mobile-menu-btn');
            const menu = document.getElementById('mobile-menu');
            const icon = btn.querySelector('i');

            btn.addEventListener('click', () => {
                // Alternar clase para abrir/cerrar
                menu.classList.toggle('open');

                // Cambiar icono de hamburguesa a X
                if (menu.classList.contains('open')) {
                    icon.classList.remove('fa-bars');
                    icon.classList.add('fa-xmark');
                } else {
                    icon.classList.remove('fa-xmark');
                    icon.classList.add('fa-bars');
                }
            });

            // Cerrar menú al hacer clic en un enlace (opcional, mejora UX móvil)
            const mobileLinks = menu.querySelectorAll('a, button');
            mobileLinks.forEach(link => {
                link.addEventListener('click', () => {
                    menu.classList.remove('open');
                    icon.classList.remove('fa-xmark');
                    icon.classList.add('fa-bars');
                });
            });
            
            // Efecto simple al hacer scroll en el header (opcional)
            window.addEventListener('scroll', () => {
                const header = document.querySelector('header');
                if (window.scrollY > 50) {
                    header.classList.add('shadow-md');
                } else {
                    header.classList.remove('shadow-md');
                }
            });
        });
    </script>
</body>
</html>