{{-- Componente de perfil de usuario del dashboard --}}
<div id="perfil" class="dashboard-section hidden space-y-6">
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
            <h3 class="font-serif text-xl font-bold text-slate-800 mb-4">Mi Perfil</h3>
            <div class="flex items-center gap-6 mb-6">
                <img src="{{ $user->profile_photo_url ?? asset('images/default-avatar.png') }}" class="w-24 h-24 rounded-full object-cover border-4 border-mso-gold">
                <div>
                    <h4 class="text-xl font-bold text-slate-800">{{ $user->name }}</h4>
                    <p class="text-slate-500">{{ $user->email }}</p>
                    <p class="text-slate-500">Miembro desde {{ $user->created_at->format('d/m/Y') }}</p>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700">Teléfono</label>
                    <p class="text-slate-600">{{ $user->phone ?? 'No registrado' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Cédula</label>
                    <p class="text-slate-600">{{ ($user->id_type ?? '') . ' ' . ($user->id_number ?? 'No registrada') }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">Ciudad</label>
                    <p class="text-slate-600">{{ $user->city ?? 'No registrada' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700">País</label>
                    <p class="text-slate-600">{{ $user->country ?? 'Venezuela' }}</p>
                </div>
            </div>
        </div>
    </div>
