{{-- Sección de venta de propiedades en la landing page --}}
<section id="vender" class="bg-mso-blue py-24 relative overflow-hidden">
    <div class="absolute top-0 right-0 w-1/2 h-full bg-gradient-to-l from-white/5 to-transparent"></div>

    <div class="max-w-7xl mx-auto px-6 relative z-10 grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
        <div class="text-white">
            <span class="text-mso-gold font-bold tracking-widest uppercase text-xs mb-4 block">Para Propietarios</span>
            <h2 class="font-serif text-4xl md:text-5xl mb-6">Vende tu propiedad al mejor precio.</h2>
            <p class="text-slate-300 text-lg leading-relaxed mb-8 font-light">
                Nuestra estrategia de marketing de lujo y nuestra red de contactos exclusiva garantizan que tu inmueble reciba la atención que merece.
            </p>
            <ul class="space-y-4 text-slate-300">
                <li class="flex items-center gap-3">
                    <i class="ph-fill ph-check-circle text-mso-gold text-xl"></i>
                    <span>Fotografía Profesional & Video</span>
                </li>
                <li class="flex items-center gap-3">
                    <i class="ph-fill ph-check-circle text-mso-gold text-xl"></i>
                    <span>Publicación en Portales Premium</span>
                </li>
                <li class="flex items-center gap-3">
                    <i class="ph-fill ph-check-circle text-mso-gold text-xl"></i>
                    <span>Asesoría Legal Integral</span>
                </li>
            </ul>
        </div>

        <div class="bg-white p-8 rounded-2xl shadow-2xl">
            <h3 class="font-serif text-2xl text-slate-900 mb-6">Solicitar Valoración</h3>

            @if(session('success'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form class="space-y-4" method="POST" action="{{ route('lead.store.public') }}">
                @csrf

                {{-- Nombre y Apellido --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="sell_name" class="sr-only">Nombre</label>
                        <input type="text" id="sell_name" name="name" value="{{ old('name') }}"
                            placeholder="Nombre" required autocomplete="given-name"
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-5 py-3 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-mso-gold/50 transition-colors">
                    </div>
                    <div>
                        <label for="sell_last_name" class="sr-only">Apellido</label>
                        <input type="text" id="sell_last_name" name="last_name" value="{{ old('last_name') }}"
                            placeholder="Apellido" autocomplete="family-name"
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-5 py-3 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-mso-gold/50 transition-colors">
                    </div>
                </div>

                {{-- Email y Teléfono --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="sell_email" class="sr-only">Correo electrónico</label>
                        <input type="email" id="sell_email" name="email" value="{{ old('email') }}"
                            placeholder="Correo electrónico" required autocomplete="email"
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-5 py-3 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-mso-gold/50 transition-colors">
                    </div>
                    <div>
                        <label for="sell_phone" class="sr-only">Teléfono</label>
                        <input type="tel" id="sell_phone" name="phone" value="{{ old('phone') }}"
                            placeholder="Teléfono" required autocomplete="tel"
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl px-5 py-3 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-mso-gold/50 transition-colors">
                    </div>
                </div>

                {{--  Selección de Asesor --}}
                <div>
                    <label for="sell_asesor_id" class="sr-only">Asesor</label>
                    <select name="asesor_id" id="sell_asesor_id" autocomplete="off"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-5 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-mso-gold/50 transition-colors">
                        <option value="">Selecciona un asesor </option>
                        @php
                            $asesores = App\Models\User::role('Asesor Inmobiliario')
                                ->where('is_active', true)
                                ->get();
                        @endphp
                        @foreach($asesores as $asesor)
                            <option value="{{ $asesor->id }}" {{ old('asesor_id') == $asesor->id ? 'selected' : '' }}>
                                {{ $asesor->name }} {{ $asesor->last_name }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-slate-400 mt-1">Selecciona el asesor de tu preferencia. Si no eliges, asignaremos uno automáticamente.</p>
                </div>

                {{-- Dirección de la propiedad --}}
                <div>
                    <label for="sell_property_address" class="sr-only">Dirección de la propiedad</label>
                    <input type="text" id="sell_property_address" name="property_address" value="{{ old('property_address') }}"
                        placeholder="Dirección de la propiedad" autocomplete="street-address"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-5 py-3 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-mso-gold/50 transition-colors">
                </div>

                {{-- Tipo de propiedad --}}
                <div>
                    <label for="sell_property_type" class="sr-only">Tipo de propiedad</label>
                    <select name="property_type" id="sell_property_type" autocomplete="off"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-5 py-3 text-slate-800 focus:outline-none focus:ring-2 focus:ring-mso-gold/50 transition-colors">
                        <option value="">Tipo de propiedad</option>
                        @php
                            $categorias = App\Models\Category::active()->ordered()->get();
                        @endphp
                        @foreach($categorias as $categoria)
                            <option value="{{ $categoria->name }}" {{ old('property_type') == $categoria->name ? 'selected' : '' }}>
                                {{ $categoria->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Detalles adicionales --}}
                <div>
                    <label for="sell_property_details" class="sr-only">Detalles adicionales de la propiedad</label>
                    <textarea id="sell_property_details" name="property_details" rows="3" placeholder="Detalles adicionales de la propiedad (habitaciones, baños, área, etc.)" autocomplete="off"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-5 py-3 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-mso-gold/50 transition-colors resize-none">{{ old('property_details') }}</textarea>
                </div>

                {{-- Campos ocultos --}}
                <input type="hidden" name="source" value="website_valoracion" autocomplete="off">
                <input type="hidden" name="interest_type" value="venta" autocomplete="off">

                <button type="submit" class="w-full bg-mso-gold text-slate-900 font-bold py-4 rounded-xl hover:bg-yellow-600 transition-colors duration-300 shadow-lg hover:shadow-yellow-500/30">
                    Enviar Solicitud
                </button>
            </form>
        </div>
    </div>
</section>
