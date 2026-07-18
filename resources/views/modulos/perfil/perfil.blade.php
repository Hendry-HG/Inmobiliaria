@extends('layouts.dashboard')

@section('title', 'Mi Perfil')
@section('header', 'Perfil de Usuario')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Columna izquierda - Info básica y estadísticas -->
        <div class="lg:col-span-1 space-y-6">

            <!-- Tarjeta de Perfil -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="bg-gradient-to-r from-mso-blue to-slate-700 h-24"></div>
                <div class="px-6 pb-6">
                    <div class="flex justify-center -mt-12">
                        <img src="{{ $user->profile_photo_url }}"
                             class="w-24 h-24 rounded-full object-cover border-4 border-white shadow-lg"
                             alt="{{ $user->full_name }}">
                    </div>
                    <div class="text-center mt-3">
                        <h2 class="text-xl font-bold text-slate-800">{{ $user->full_name }}</h2>
                        <p class="text-sm text-slate-500">{{ $user->email }}</p>
                        <span class="inline-block mt-2 px-3 py-1 bg-mso-gold/20 text-mso-blue rounded-full text-xs font-medium">
                            {{ $mainRole }}
                        </span>
                        @if($user->specialization)
                            <p class="text-xs text-slate-500 mt-2">{{ $user->specialization }}</p>
                        @endif
                    </div>

                    @if($user->address)
                        <div class="mt-3 pt-3 border-t border-slate-100">
                            <div class="flex items-start gap-2 text-sm text-slate-600">
                                <i class="ph ph-map-pin text-mso-gold mt-0.5"></i>
                                <span><strong>Dirección:</strong> {{ $user->address }}</span>
                            </div>
                        </div>
                    @endif

                    @if($user->bio)
                    <div class="mt-4 pt-4 border-t border-slate-100">
                        <p class="text-sm text-slate-600 italic">"{{ $user->bio }}"</p>
                    </div>
                    @endif

                    <div class="mt-4 pt-4 border-t border-slate-100">
                        <div class="flex items-center gap-2 text-sm text-slate-600">
                            <i class="ph ph-calendar text-slate-400"></i>
                            <span>Miembro desde: <strong>{{ $stats['member_since'] }}</strong></span>
                        </div>
                        <div class="flex items-center gap-2 text-sm text-slate-600 mt-2">
                            <i class="ph ph-clock text-slate-400"></i>
                            <span>{{ $stats['account_age'] }} días en la plataforma</span>
                        </div>
                        @if($user->id_type && $user->id_number)
                        <div class="flex items-center gap-2 text-sm text-slate-600 mt-2">
                            <i class="ph ph-identification-card text-slate-400"></i>
                            <span><strong>ID:</strong> {{ $user->full_id }}</span>
                        </div>
                        @endif
                        @if($user->phone)
                        <div class="flex items-center gap-2 text-sm text-slate-600 mt-2">
                            <i class="ph ph-phone text-slate-400"></i>
                            <span><strong>Teléfono:</strong> {{ $user->phone }}</span>
                        </div>
                        @endif
                    </div>

                    {{-- Redes Sociales - SOLO PARA ASESOR INMOBILIARIO --}}
                    @if($mainRole == 'Asesor Inmobiliario')
                        @php
                            $socialLinks = $user->social_links ?? [];
                        @endphp
                        @if(!empty($socialLinks))
                        <div class="mt-4 pt-4 border-t border-slate-100">
                            <p class="text-xs text-slate-500 uppercase tracking-wider mb-3">Redes Sociales</p>
                            <div class="flex flex-wrap gap-2">
                                @if(isset($socialLinks['whatsapp']))
                                    <a href="https://wa.me/{{ $socialLinks['whatsapp'] }}" target="_blank"
                                       class="w-9 h-9 bg-green-500 text-white rounded-full flex items-center justify-center hover:bg-green-600 transition-colors">
                                        <i class="ph ph-whatsapp-logo text-lg"></i>
                                    </a>
                                @endif
                                @if(isset($socialLinks['instagram']))
                                    <a href="https://instagram.com/{{ $socialLinks['instagram'] }}" target="_blank"
                                       class="w-9 h-9 bg-gradient-to-br from-purple-500 to-pink-500 text-white rounded-full flex items-center justify-center hover:opacity-80 transition-opacity">
                                        <i class="ph ph-instagram-logo text-lg"></i>
                                    </a>
                                @endif
                                @if(isset($socialLinks['facebook']))
                                    <a href="{{ $socialLinks['facebook'] }}" target="_blank"
                                       class="w-9 h-9 bg-blue-600 text-white rounded-full flex items-center justify-center hover:bg-blue-700 transition-colors">
                                        <i class="ph ph-facebook-logo text-lg"></i>
                                    </a>
                                @endif
                                @if(isset($socialLinks['tiktok']))
                                    <a href="https://tiktok.com/@{{ $socialLinks['tiktok'] }}" target="_blank"
                                       class="w-9 h-9 bg-black text-white rounded-full flex items-center justify-center hover:bg-gray-800 transition-colors">
                                        <i class="ph ph-tiktok-logo text-lg"></i>
                                    </a>
                                @endif
                                @if(isset($socialLinks['telegram']))
                                    <a href="https://t.me/{{ $socialLinks['telegram'] }}" target="_blank"
                                       class="w-9 h-9 bg-sky-500 text-white rounded-full flex items-center justify-center hover:bg-sky-600 transition-colors">
                                        <i class="ph ph-telegram-logo text-lg"></i>
                                    </a>
                                @endif
                                @if(isset($socialLinks['linkedin']))
                                    <a href="{{ $socialLinks['linkedin'] }}" target="_blank"
                                       class="w-9 h-9 bg-blue-700 text-white rounded-full flex items-center justify-center hover:bg-blue-800 transition-colors">
                                        <i class="ph ph-linkedin-logo text-lg"></i>
                                    </a>
                                @endif
                                @if(isset($socialLinks['twitter']))
                                    <a href="https://twitter.com/{{ $socialLinks['twitter'] }}" target="_blank"
                                       class="w-9 h-9 bg-sky-400 text-white rounded-full flex items-center justify-center hover:bg-sky-500 transition-colors">
                                        <i class="ph ph-twitter-logo text-lg"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                        @endif
                    @endif
                </div>
            </div>

            {{-- ============================================ --}}
            {{-- ESTADÍSTICAS SEGÚN ROL - EXCLUYENDO CLIENTE --}}
            {{-- ============================================ --}}
            @if($mainRole != 'Cliente')
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                <h3 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
                    <i class="ph ph-chart-bar text-mso-gold"></i>
                    Estadísticas
                </h3>

                <div class="space-y-3">
                    {{-- SUPER ADMIN --}}
                    @if($mainRole == 'Super Admin')
                        <div class="flex justify-between items-center py-2 border-b border-slate-100">
                            <span class="text-slate-600">Total Usuarios</span>
                            <span class="font-bold text-slate-800">{{ $stats['total_users'] ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-slate-100">
                            <span class="text-slate-600">Total Propiedades</span>
                            <span class="font-bold text-slate-800">{{ $stats['total_properties'] ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2">
                            <span class="text-slate-600">Propiedades Activas</span>
                            <span class="font-bold text-green-600">{{ $stats['active_properties'] ?? 0 }}</span>
                        </div>

                    {{-- ADMINISTRADOR --}}
                    @elseif($mainRole == 'Administrador')
                        <div class="flex justify-between items-center py-2 border-b border-slate-100">
                            <span class="text-slate-600">Total Usuarios</span>
                            <span class="font-bold text-slate-800">{{ $stats['total_users'] ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-slate-100">
                            <span class="text-slate-600">Total Propiedades</span>
                            <span class="font-bold text-slate-800">{{ $stats['total_properties'] ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2">
                            <span class="text-slate-600">Propiedades Activas</span>
                            <span class="font-bold text-green-600">{{ $stats['active_properties'] ?? 0 }}</span>
                        </div>

                    {{-- ASESOR INMOBILIARIO --}}
                    @elseif($mainRole == 'Asesor Inmobiliario')
                        <div class="flex justify-between items-center py-2 border-b border-slate-100">
                            <span class="text-slate-600">Mis Propiedades</span>
                            <span class="font-bold text-slate-800">{{ $stats['my_properties'] ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-slate-100">
                            <span class="text-slate-600">Propiedades Publicadas</span>
                            <span class="font-bold text-green-600">{{ $stats['published_properties'] ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-slate-100">
                            <span class="text-slate-600">Total Citas</span>
                            <span class="font-bold text-slate-800">{{ $stats['total_appointments'] ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2">
                            <span class="text-slate-600">Citas Pendientes</span>
                            <span class="font-bold text-yellow-600">{{ $stats['pending_appointments'] ?? 0 }}</span>
                        </div>

                    {{-- AUDITOR --}}
                    @elseif($mainRole == 'Auditor')
                        <div class="flex justify-between items-center py-2 border-b border-slate-100">
                            <span class="text-slate-600">Total Auditorías</span>
                            <span class="font-bold text-slate-800">{{ $stats['total_audits'] ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2">
                            <span class="text-slate-600">Auditorías Recientes</span>
                            <span class="font-bold text-slate-800">{{ $stats['recent_audits'] ?? 0 }}</span>
                        </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Columna derecha - Formulario de edición -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="p-6 bg-slate-50 border-b border-slate-200">
                    <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                        <i class="ph ph-pencil text-mso-gold"></i>
                        Editar Información
                    </h3>
                </div>

                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
                    @csrf
                    @method('PUT')

                    {{-- NOMBRE Y APELLIDO --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Nombre *</label>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}"
                                   class="w-full border rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold outline-none" required>
                            @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Apellido</label>
                            <input type="text" name="last_name" value="{{ old('last_name', $user->last_name) }}"
                                   class="w-full border rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold outline-none">
                            @error('last_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- EMAIL (solo lectura) --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                        <input type="email" value="{{ $user->email }}" readonly
                               class="w-full border rounded-lg p-2.5 bg-slate-50 text-slate-500 cursor-not-allowed">
                        <p class="text-xs text-slate-400 mt-1">El email no se puede modificar</p>
                    </div>

                    {{-- TELÉFONO Y CÉDULA --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Teléfono</label>
                            <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                                   class="w-full border rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold outline-none">
                            @error('phone') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Tipo de Cédula</label>
                            <select name="id_type" class="w-full border rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold outline-none">
                                <option value="">Seleccionar</option>
                                <option value="V" {{ old('id_type', $user->id_type) == 'V' ? 'selected' : '' }}>Venezolano (V)</option>
                                <option value="E" {{ old('id_type', $user->id_type) == 'E' ? 'selected' : '' }}>Extranjero (E)</option>
                                <option value="J" {{ old('id_type', $user->id_type) == 'J' ? 'selected' : '' }}>Jurídico (J)</option>
                                <option value="P" {{ old('id_type', $user->id_type) == 'P' ? 'selected' : '' }}>Pasaporte (P)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Número de Cédula</label>
                            <input type="text" name="id_number" value="{{ old('id_number', $user->id_number) }}"
                                   class="w-full border rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold outline-none">
                            @error('id_number') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Dirección</label>
                        <textarea name="address" rows="2" class="w-full border rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold outline-none"
                                  placeholder="Tu dirección completa">{{ old('address', $user->address) }}</textarea>
                        @error('address') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Especialización (SOLO PARA ASESOR) --}}
                    @if($mainRole == 'Asesor Inmobiliario')
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Especialización</label>
                        <input type="text" name="specialization" value="{{ old('specialization', $user->specialization) }}"
                               placeholder="Ej: Propiedades de Lujo, Alquileres Comerciales..."
                               class="w-full border rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold outline-none">
                    </div>
                    @endif

                    <!-- Biografía (SOLO PARA ASESOR) -->
                    @if($mainRole == 'Asesor Inmobiliario')
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Biografía</label>
                        <textarea name="bio" rows="3" class="w-full border rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold outline-none"
                                  placeholder="Cuéntanos sobre ti, tu experiencia y lo que te apasiona...">{{ old('bio', $user->bio) }}</textarea>
                        <p class="text-xs text-slate-400 mt-1">Máximo 500 caracteres</p>
                        @error('bio') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    @endif

                    {{-- ============================================ --}}
                    {{-- REDES SOCIALES - SOLO PARA ASESOR --}}
                    {{-- ============================================ --}}
                    @if($mainRole == 'Asesor Inmobiliario')
                    <div>
                        <h4 class="font-bold text-slate-700 mb-3 pb-2 border-b">Redes Sociales</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">
                                    <i class="ph ph-whatsapp-logo text-green-500 mr-1"></i>WhatsApp
                                </label>
                                <input type="text" name="whatsapp" value="{{ old('whatsapp', $socialLinks['whatsapp'] ?? '') }}"
                                       placeholder="Ej: 584121234567 (solo números)"
                                       class="w-full border rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">
                                    <i class="ph ph-instagram-logo text-pink-500 mr-1"></i>Instagram
                                </label>
                                <input type="text" name="instagram" value="{{ old('instagram', $socialLinks['instagram'] ?? '') }}"
                                       placeholder="Ej: mso.inmobiliaria (sin @)"
                                       class="w-full border rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">
                                    <i class="ph ph-facebook-logo text-blue-600 mr-1"></i>Facebook
                                </label>
                                <input type="text" name="facebook" value="{{ old('facebook', $socialLinks['facebook'] ?? '') }}"
                                       placeholder="URL completa de tu perfil"
                                       class="w-full border rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">
                                    <i class="ph ph-tiktok-logo text-black mr-1"></i>TikTok
                                </label>
                                <input type="text" name="tiktok" value="{{ old('tiktok', $socialLinks['tiktok'] ?? '') }}"
                                       placeholder="Ej: mso.inmobiliaria (sin @)"
                                       class="w-full border rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">
                                    <i class="ph ph-telegram-logo text-sky-500 mr-1"></i>Telegram
                                </label>
                                <input type="text" name="telegram" value="{{ old('telegram', $socialLinks['telegram'] ?? '') }}"
                                       placeholder="Ej: mso_inmobiliaria (sin @)"
                                       class="w-full border rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">
                                    <i class="ph ph-linkedin-logo text-blue-700 mr-1"></i>LinkedIn
                                </label>
                                <input type="text" name="linkedin" value="{{ old('linkedin', $socialLinks['linkedin'] ?? '') }}"
                                       placeholder="URL completa de tu perfil"
                                       class="w-full border rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">
                                    <i class="ph ph-twitter-logo text-sky-400 mr-1"></i>Twitter / X
                                </label>
                                <input type="text" name="twitter" value="{{ old('twitter', $socialLinks['twitter'] ?? '') }}"
                                       placeholder="Ej: mso_inmobiliaria (sin @)"
                                       class="w-full border rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold outline-none">
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Ubicación (SOLO PARA ASESOR Y CLIENTE) -->
                    @if($mainRole == 'Asesor Inmobiliario' || $mainRole == 'Cliente')
                    <div>
                        <h4 class="font-bold text-slate-700 mb-3 pb-2 border-b">Ubicación</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">País</label>
                                <select name="country_id" id="profile_country_id" class="w-full border rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold outline-none">
                                    <option value="">Seleccionar país</option>
                                    @foreach($countries as $country)
                                        <option value="{{ $country->id }}" {{ old('country_id', $user->country_id) == $country->id ? 'selected' : '' }}>
                                            {{ $country->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Estado</label>
                                <select name="state_id" id="profile_state_id" class="w-full border rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold outline-none" {{ $user->country_id ? '' : 'disabled' }}>
                                    <option value="">Seleccionar estado</option>
                                    @foreach($states as $state)
                                        <option value="{{ $state->id }}" {{ old('state_id', $user->state_id) == $state->id ? 'selected' : '' }}>
                                            {{ $state->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Municipio</label>
                                <select name="municipality_id" id="profile_municipality_id" class="w-full border rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold outline-none" {{ $user->state_id ? '' : 'disabled' }}>
                                    <option value="">Seleccionar municipio</option>
                                    @foreach($municipalities as $municipality)
                                        <option value="{{ $municipality->id }}" {{ old('municipality_id', $user->municipality_id) == $municipality->id ? 'selected' : '' }}>
                                            {{ $municipality->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Parroquia</label>
                                <select name="parish_id" id="profile_parish_id" class="w-full border rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold outline-none" {{ $user->municipality_id ? '' : 'disabled' }}>
                                    <option value="">Seleccionar parroquia</option>
                                    @foreach($parishes as $parish)
                                        <option value="{{ $parish->id }}" {{ old('parish_id', $user->parish_id) == $parish->id ? 'selected' : '' }}>
                                            {{ $parish->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Ciudad</label>
                                <select name="city_id" id="profile_city_id" class="w-full border rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold outline-none" {{ $user->parish_id ? '' : 'disabled' }}>
                                    <option value="">Seleccionar ciudad</option>
                                    @foreach($cities as $city)
                                        <option value="{{ $city->id }}" {{ old('city_id', $user->city_id) == $city->id ? 'selected' : '' }}>
                                            {{ $city->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Foto de Perfil (TODOS) -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Foto de Perfil</label>
                        <input type="file" name="profile_photo" accept="image/*"
                               class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-mso-gold file:text-mso-blue hover:file:bg-mso-blue hover:file:text-white cursor-pointer">
                        <p class="text-xs text-slate-400 mt-1">Formatos: JPG, PNG. Máximo 2MB</p>
                        @error('profile_photo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Cambiar Contraseña (TODOS) -->
                    <div>
                        <h4 class="font-bold text-slate-700 mb-3 pb-2 border-b">Cambiar Contraseña</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Contraseña Actual</label>
                                <input type="password" name="current_password" placeholder="••••••••"
                                       class="w-full border rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold outline-none">
                                @error('current_password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div></div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Nueva Contraseña</label>
                                <input type="password" name="password" placeholder="Mínimo 8 caracteres"
                                       class="w-full border rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold outline-none">
                                @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Confirmar Contraseña</label>
                                <input type="password" name="password_confirmation" placeholder="Repite la contraseña"
                                       class="w-full border rounded-lg p-2.5 focus:ring-2 focus:ring-mso-gold outline-none">
                            </div>
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                        <button type="reset" class="px-6 py-2.5 border border-slate-300 rounded-lg text-slate-600 hover:bg-slate-50 font-medium transition-colors">
                            Cancelar
                        </button>
                        <button type="submit" class="bg-mso-blue text-white px-8 py-2.5 rounded-lg hover:bg-slate-800 font-bold shadow-lg transition-transform hover:-translate-y-1">
                            <i class="ph ph-check mr-1"></i>
                            Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const countrySelect = document.getElementById('profile_country_id');
    const stateSelect = document.getElementById('profile_state_id');
    const municipalitySelect = document.getElementById('profile_municipality_id');
    const parishSelect = document.getElementById('profile_parish_id');
    const citySelect = document.getElementById('profile_city_id');

    if (countrySelect) {
        countrySelect.addEventListener('change', function() {
            const countryId = this.value;
            if (countryId) {
                fetch(`/api/locations/states/${countryId}`)
                    .then(response => response.json())
                    .then(states => {
                        stateSelect.disabled = false;
                        stateSelect.innerHTML = '<option value="">Seleccione un estado</option>';
                        states.forEach(state => {
                            stateSelect.innerHTML += `<option value="${state.id}">${state.name}</option>`;
                        });
                        municipalitySelect.disabled = true;
                        municipalitySelect.innerHTML = '<option value="">Primero seleccione un estado</option>';
                        parishSelect.disabled = true;
                        parishSelect.innerHTML = '<option value="">Primero seleccione un municipio</option>';
                        citySelect.disabled = true;
                        citySelect.innerHTML = '<option value="">Primero seleccione una parroquia</option>';
                    });
            }
        });
    }

    if (stateSelect) {
        stateSelect.addEventListener('change', function() {
            const stateId = this.value;
            if (stateId) {
                fetch(`/api/locations/municipalities/${stateId}`)
                    .then(response => response.json())
                    .then(municipalities => {
                        municipalitySelect.disabled = false;
                        municipalitySelect.innerHTML = '<option value="">Seleccione un municipio</option>';
                        municipalities.forEach(municipality => {
                            municipalitySelect.innerHTML += `<option value="${municipality.id}">${municipality.name}</option>`;
                        });
                        parishSelect.disabled = true;
                        parishSelect.innerHTML = '<option value="">Primero seleccione un municipio</option>';
                        citySelect.disabled = true;
                        citySelect.innerHTML = '<option value="">Primero seleccione una parroquia</option>';
                    });
            }
        });
    }

    if (municipalitySelect) {
        municipalitySelect.addEventListener('change', function() {
            const municipalityId = this.value;
            if (municipalityId) {
                fetch(`/api/locations/parishes/${municipalityId}`)
                    .then(response => response.json())
                    .then(parishes => {
                        parishSelect.disabled = false;
                        parishSelect.innerHTML = '<option value="">Seleccione una parroquia</option>';
                        parishes.forEach(parish => {
                            parishSelect.innerHTML += `<option value="${parish.id}">${parish.name}</option>`;
                        });
                        citySelect.disabled = true;
                        citySelect.innerHTML = '<option value="">Primero seleccione una parroquia</option>';
                    });
            }
        });
    }

    if (parishSelect) {
        parishSelect.addEventListener('change', function() {
            const parishId = this.value;
            if (parishId) {
                fetch(`/api/locations/cities/${parishId}`)
                    .then(response => response.json())
                    .then(cities => {
                        citySelect.disabled = false;
                        citySelect.innerHTML = '<option value="">Seleccione una ciudad</option>';
                        cities.forEach(city => {
                            citySelect.innerHTML += `<option value="${city.id}">${city.name}</option>`;
                        });
                    });
            }
        });
    }
});

@if(session('success'))
    const toast = document.createElement('div');
    toast.className = 'fixed bottom-4 right-4 bg-green-500 text-white px-4 py-3 rounded-lg shadow-lg z-50 animate-slide-up';
    toast.innerHTML = '<i class="ph ph-check-circle mr-2"></i>{{ session("success") }}';
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 4000);
@endif
</script>
@endpush
@endsection