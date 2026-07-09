<aside class="w-full lg:w-72 flex-shrink-0">
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 sticky top-24">
        <div class="flex justify-between items-center mb-6">
            <h3 class="font-bold text-slate-800 text-lg">Filtros</h3>
            <button id="clear-filters" class="text-xs text-red-500 hover:text-red-700 font-medium hover:underline">Limpiar todo</button>
        </div>

        <!-- Tipo de Propiedad -->
        <div class="mb-6">
            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Tipo</h4>
            <div class="space-y-2">
                <label class="flex items-center gap-3 cursor-pointer group">
                    <input type="checkbox" name="type[]" value="all" class="custom-checkbox hidden" checked>
                    <div class="w-5 h-5 border-2 border-slate-300 rounded flex items-center justify-center transition-colors">
                        <svg class="w-3 h-3 text-white hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <span class="text-sm text-slate-600 group-hover:text-mso-blue transition-colors">Todos</span>
                </label>
                <label class="flex items-center gap-3 cursor-pointer group">
                    <input type="checkbox" name="type[]" value="apartment" class="custom-checkbox hidden">
                    <div class="w-5 h-5 border-2 border-slate-300 rounded flex items-center justify-center transition-colors">
                        <svg class="w-3 h-3 text-white hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <span class="text-sm text-slate-600 group-hover:text-mso-blue transition-colors">Apartamentos</span>
                </label>
                <label class="flex items-center gap-3 cursor-pointer group">
                    <input type="checkbox" name="type[]" value="house" class="custom-checkbox hidden">
                    <div class="w-5 h-5 border-2 border-slate-300 rounded flex items-center justify-center transition-colors">
                        <svg class="w-3 h-3 text-white hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <span class="text-sm text-slate-600 group-hover:text-mso-blue transition-colors">Casas / Quintas</span>
                </label>
                <label class="flex items-center gap-3 cursor-pointer group">
                    <input type="checkbox" name="type[]" value="commercial" class="custom-checkbox hidden">
                    <div class="w-5 h-5 border-2 border-slate-300 rounded flex items-center justify-center transition-colors">
                        <svg class="w-3 h-3 text-white hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <span class="text-sm text-slate-600 group-hover:text-mso-blue transition-colors">Locales / Oficinas</span>
                </label>
                <label class="flex items-center gap-3 cursor-pointer group">
                    <input type="checkbox" name="type[]" value="land" class="custom-checkbox hidden">
                    <div class="w-5 h-5 border-2 border-slate-300 rounded flex items-center justify-center transition-colors">
                        <svg class="w-3 h-3 text-white hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <span class="text-sm text-slate-600 group-hover:text-mso-blue transition-colors">Terrenos</span>
                </label>
            </div>
        </div>

        <!-- Operación (Venta/Alquiler) -->
        <div class="mb-6">
            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Operación</h4>
            <div class="space-y-2">
                <label class="flex items-center gap-3 cursor-pointer group">
                    <input type="radio" name="operation" value="all" class="text-mso-blue focus:ring-mso-gold" checked>
                    <span class="text-sm text-slate-600 group-hover:text-mso-blue">Todos</span>
                </label>
                <label class="flex items-center gap-3 cursor-pointer group">
                    <input type="radio" name="operation" value="venta" class="text-mso-blue focus:ring-mso-gold">
                    <span class="text-sm text-slate-600 group-hover:text-mso-blue">Venta</span>
                </label>
                <label class="flex items-center gap-3 cursor-pointer group">
                    <input type="radio" name="operation" value="alquiler" class="text-mso-blue focus:ring-mso-gold">
                    <span class="text-sm text-slate-600 group-hover:text-mso-blue">Alquiler</span>
                </label>
            </div>
        </div>

        <!-- Rango de Precio -->
        <div class="mb-6">
            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Rango de Precio</h4>
            <div class="space-y-3">
                <label class="flex items-center gap-3 cursor-pointer group">
                    <input type="radio" name="price_range" value="any" class="text-mso-blue focus:ring-mso-gold" checked>
                    <span class="text-sm text-slate-600 group-hover:text-mso-blue">Cualquier precio</span>
                </label>
                <label class="flex items-center gap-3 cursor-pointer group">
                    <input type="radio" name="price_range" value="0-50000" class="text-mso-blue focus:ring-mso-gold">
                    <span class="text-sm text-slate-600 group-hover:text-mso-blue">Hasta $50,000</span>
                </label>
                <label class="flex items-center gap-3 cursor-pointer group">
                    <input type="radio" name="price_range" value="50000-150000" class="text-mso-blue focus:ring-mso-gold">
                    <span class="text-sm text-slate-600 group-hover:text-mso-blue">$50k - $150k</span>
                </label>
                <label class="flex items-center gap-3 cursor-pointer group">
                    <input type="radio" name="price_range" value="150000-300000" class="text-mso-blue focus:ring-mso-gold">
                    <span class="text-sm text-slate-600 group-hover:text-mso-blue">$150k - $300k</span>
                </label>
                <label class="flex items-center gap-3 cursor-pointer group">
                    <input type="radio" name="price_range" value="300000-999999999" class="text-mso-blue focus:ring-mso-gold">
                    <span class="text-sm text-slate-600 group-hover:text-mso-blue">Más de $300k</span>
                </label>
            </div>
        </div>

        <!-- Habitaciones -->
        <div class="mb-6">
            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Habitaciones</h4>
            <select name="bedrooms" class="w-full border border-slate-200 rounded-lg px-4 py-2 text-slate-600 text-sm focus:outline-none focus:ring-2 focus:ring-mso-gold/20 focus:border-mso-gold bg-white">
                <option value="">Cualquiera</option>
                <option value="1">1 Habitación</option>
                <option value="2">2 Habitaciones</option>
                <option value="3">3 Habitaciones</option>
                <option value="4">4 o más</option>
            </select>
        </div>

        <!-- Baños -->
        <div class="mb-6">
            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Baños</h4>
            <select name="bathrooms" class="w-full border border-slate-200 rounded-lg px-4 py-2 text-slate-600 text-sm focus:outline-none focus:ring-2 focus:ring-mso-gold/20 focus:border-mso-gold bg-white">
                <option value="">Cualquiera</option>
                <option value="1">1 Baño</option>
                <option value="2">2 Baños</option>
                <option value="3">3 Baños</option>
                <option value="4">4 o más</option>
            </select>
        </div>

        <!-- Ubicación -->
        <div class="mb-6">
            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Ubicación</h4>
            <div class="relative">
                <i class="ph ph-map-pin absolute left-3 top-3 text-slate-400"></i>
                <input type="text" name="location" placeholder="Ciudad, zona o urbanización" class="w-full pl-10 pr-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-mso-gold/20 focus:border-mso-gold">
            </div>
            <div class="flex flex-wrap gap-2 mt-3">
                <button type="button" data-location="Lechería" class="location-chip px-3 py-1 bg-slate-100 text-slate-600 rounded-full text-xs font-medium cursor-pointer hover:bg-slate-200">Lechería</button>
                <button type="button" data-location="Puerto La Cruz" class="location-chip px-3 py-1 bg-slate-100 text-slate-600 rounded-full text-xs font-medium cursor-pointer hover:bg-slate-200">Puerto La Cruz</button>
                <button type="button" data-location="Barcelona" class="location-chip px-3 py-1 bg-slate-100 text-slate-600 rounded-full text-xs font-medium cursor-pointer hover:bg-slate-200">Barcelona</button>
            </div>
        </div>

        <!-- Área (m²) -->
        <div class="mb-6">
            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Área (m²)</h4>
            <div class="flex gap-3">
                <input type="number" name="area_min" placeholder="Mínimo" class="w-1/2 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-mso-gold/20">
                <input type="number" name="area_max" placeholder="Máximo" class="w-1/2 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-mso-gold/20">
            </div>
        </div>

        <!-- Botón Aplicar -->
        <button id="apply-filters" class="w-full bg-mso-blue text-white font-bold py-3 rounded-lg shadow-md hover:bg-slate-800 transition-colors">
            Aplicar Filtros
        </button>
    </div>
</aside>

<style>
    .custom-checkbox:checked + div {
        background-color: #0f172a;
        border-color: #0f172a;
    }
    .custom-checkbox:checked + div svg {
        display: block;
    }
</style>
