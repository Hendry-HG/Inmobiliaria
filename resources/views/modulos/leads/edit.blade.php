<form id="editLeadForm" method="POST" action="{{ route('leads.update', $lead) }}">
    @csrf
    @method('PUT')

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Nombre</label>
            <input type="text" name="name" value="{{ old('name', $lead->name) }}" required
                class="w-full bg-slate-50 border border-slate-300 rounded-lg px-4 py-2 text-slate-700 focus:ring-2 focus:ring-mso-gold/50 focus:border-mso-gold transition-colors">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
            <input type="email" name="email" value="{{ old('email', $lead->email) }}" required
                class="w-full bg-slate-50 border border-slate-300 rounded-lg px-4 py-2 text-slate-700 focus:ring-2 focus:ring-mso-gold/50 focus:border-mso-gold transition-colors">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Teléfono</label>
            <input type="tel" name="phone" value="{{ old('phone', $lead->phone) }}" required
                class="w-full bg-slate-50 border border-slate-300 rounded-lg px-4 py-2 text-slate-700 focus:ring-2 focus:ring-mso-gold/50 focus:border-mso-gold transition-colors">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Estado</label>
            <select name="status" class="w-full bg-slate-50 border border-slate-300 rounded-lg px-4 py-2 text-slate-700 focus:ring-2 focus:ring-mso-gold/50 focus:border-mso-gold transition-colors">
                @foreach($statuses as $value => $label)
                    <option value="{{ $value }}" {{ old('status', $lead->status) == $value ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Asesor</label>
            <select name="asesor_id" class="w-full bg-slate-50 border border-slate-300 rounded-lg px-4 py-2 text-slate-700 focus:ring-2 focus:ring-mso-gold/50 focus:border-mso-gold transition-colors">
                <option value="">Sin asignar</option>
                @foreach($asesores as $asesor)
                    <option value="{{ $asesor->id }}" {{ old('asesor_id', $lead->asesor_id) == $asesor->id ? 'selected' : '' }}>
                        {{ $asesor->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-slate-700 mb-1">Notas</label>
            <textarea name="notes" rows="3"
                class="w-full bg-slate-50 border border-slate-300 rounded-lg px-4 py-2 text-slate-700 focus:ring-2 focus:ring-mso-gold/50 focus:border-mso-gold transition-colors resize-none">{{ old('notes', $lead->notes) }}</textarea>
        </div>
    </div>

    <div class="mt-6 flex gap-3 justify-end">
        <button type="button" onclick="closeEditModal()"
            class="px-4 py-2 border border-slate-300 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors">
            Cancelar
        </button>
        <button type="submit"
            class="px-6 py-2 bg-mso-blue text-white rounded-lg text-sm font-medium hover:bg-slate-800 transition-colors shadow-lg shadow-blue-900/20">
            <i class="ph ph-check mr-1"></i> Actualizar
        </button>
    </div>
</form>
