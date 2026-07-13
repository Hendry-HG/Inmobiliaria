<aside class="w-full lg:w-72 flex-shrink-0">
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 sticky top-24">
        <div class="flex justify-between items-center mb-6">
            <h3 class="font-bold text-slate-800 text-lg">Filtros</h3>
            <button id="clear-filters" class="text-xs text-red-500 hover:text-red-700 font-medium hover:underline" type="button">Limpiar todo</button>
        </div>

        <form id="filter-form" class="space-y-6" novalidate>
            <!-- Tipo de Propiedad -->
            <div class="mb-6">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Tipo</h4>
                <div class="space-y-2" role="group" aria-label="Tipos de propiedad">
                    <label class="flex items-center gap-3 cursor-pointer group">
                        <input type="checkbox" name="type[]" value="all" class="custom-checkbox hidden" checked aria-checked="true">
                        <span class="w-5 h-5 border-2 border-slate-300 rounded flex items-center justify-center transition-colors" aria-hidden="true">
                            <svg class="w-3 h-3 text-white hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                        </span>
                        <span class="text-sm text-slate-600 group-hover:text-mso-blue transition-colors">Todos</span>
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer group">
                        <input type="checkbox" name="type[]" value="apartment" class="custom-checkbox hidden">
                        <span class="w-5 h-5 border-2 border-slate-300 rounded flex items-center justify-center transition-colors" aria-hidden="true">
                            <svg class="w-3 h-3 text-white hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                        </span>
                        <span class="text-sm text-slate-600 group-hover:text-mso-blue transition-colors">Apartamentos</span>
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer group">
                        <input type="checkbox" name="type[]" value="house" class="custom-checkbox hidden">
                        <span class="w-5 h-5 border-2 border-slate-300 rounded flex items-center justify-center transition-colors" aria-hidden="true">
                            <svg class="w-3 h-3 text-white hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                        </span>
                        <span class="text-sm text-slate-600 group-hover:text-mso-blue transition-colors">Casas / Quintas</span>
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer group">
                        <input type="checkbox" name="type[]" value="commercial" class="custom-checkbox hidden">
                        <span class="w-5 h-5 border-2 border-slate-300 rounded flex items-center justify-center transition-colors" aria-hidden="true">
                            <svg class="w-3 h-3 text-white hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                        </span>
                        <span class="text-sm text-slate-600 group-hover:text-mso-blue transition-colors">Locales / Oficinas</span>
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer group">
                        <input type="checkbox" name="type[]" value="land" class="custom-checkbox hidden">
                        <span class="w-5 h-5 border-2 border-slate-300 rounded flex items-center justify-center transition-colors" aria-hidden="true">
                            <svg class="w-3 h-3 text-white hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                        </span>
                        <span class="text-sm text-slate-600 group-hover:text-mso-blue transition-colors">Terrenos</span>
                    </label>
                </div>
            </div>

            <!-- Operación (Venta/Alquiler) -->
            <div class="mb-6">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Operación</h4>
                <div class="space-y-2" role="radiogroup" aria-label="Tipo de operación">
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
                <div class="space-y-3" role="radiogroup" aria-label="Rango de precio">
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
                <select name="bedrooms" class="w-full border border-slate-200 rounded-lg px-4 py-2 text-slate-600 text-sm focus:outline-none focus:ring-2 focus:ring-mso-gold/20 focus:border-mso-gold bg-white" aria-label="Número de habitaciones">
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
                <select name="bathrooms" class="w-full border border-slate-200 rounded-lg px-4 py-2 text-slate-600 text-sm focus:outline-none focus:ring-2 focus:ring-mso-gold/20 focus:border-mso-gold bg-white" aria-label="Número de baños">
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
                    <i class="ph ph-map-pin absolute left-3 top-3 text-slate-400" aria-hidden="true"></i>
                    <input type="text" name="location" placeholder="Ciudad, zona o urbanización" class="w-full pl-10 pr-4 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-mso-gold/20 focus:border-mso-gold" aria-label="Buscar por ubicación">
                </div>
                <div class="flex flex-wrap gap-2 mt-3" role="group" aria-label="Ubicaciones rápidas">
                    <button type="button" data-location="Lechería" class="location-chip px-3 py-1 bg-slate-100 text-slate-600 rounded-full text-xs font-medium cursor-pointer hover:bg-slate-200 transition-colors">Lechería</button>
                    <button type="button" data-location="Puerto La Cruz" class="location-chip px-3 py-1 bg-slate-100 text-slate-600 rounded-full text-xs font-medium cursor-pointer hover:bg-slate-200 transition-colors">Puerto La Cruz</button>
                    <button type="button" data-location="Barcelona" class="location-chip px-3 py-1 bg-slate-100 text-slate-600 rounded-full text-xs font-medium cursor-pointer hover:bg-slate-200 transition-colors">Barcelona</button>
                </div>
            </div>

            <!-- Área (m²) -->
            <div class="mb-6">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Área (m²)</h4>
                <div class="flex gap-3">
                    <div class="w-1/2">
                        <label for="area_min" class="sr-only">Área mínima en metros cuadrados</label>
                        <input type="number" id="area_min" name="area_min" placeholder="Mínimo" min="0" step="1" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-mso-gold/20">
                    </div>
                    <div class="w-1/2">
                        <label for="area_max" class="sr-only">Área máxima en metros cuadrados</label>
                        <input type="number" id="area_max" name="area_max" placeholder="Máximo" min="0" step="1" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-mso-gold/20">
                    </div>
                </div>
            </div>

            <!-- Botón Aplicar -->
            <button type="submit" id="apply-filters" class="w-full bg-mso-blue text-white font-bold py-3 rounded-lg shadow-md hover:bg-slate-800 transition-colors">
                Aplicar Filtros
            </button>
        </form>
    </div>
</aside>

<style>
    .custom-checkbox:checked + span {
        background-color: #0f172a;
        border-color: #0f172a;
    }
    .custom-checkbox:checked + span svg {
        display: block;
    }
    
    /* Mejoras de accesibilidad */
    .custom-checkbox:focus-visible + span {
        outline: 2px solid #c5a059;
        outline-offset: 2px;
    }
    
    /* Efecto hover en checkboxes */
    .custom-checkbox + span {
        transition: all 0.2s ease;
    }
    
    .custom-checkbox:not(:checked):hover + span {
        border-color: #c5a059;
        background-color: #f8fafc;
    }
    
    /* Estilo para chips de ubicación activos */
    .location-chip.active {
        background-color: #0f172a;
        color: white;
    }
    
    /* Mejoras para campos numéricos */
    input[type="number"]::-webkit-inner-spin-button,
    input[type="number"]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    
    input[type="number"] {
        -moz-appearance: textfield;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('filter-form');
        const clearBtn = document.getElementById('clear-filters');
        const locationChips = document.querySelectorAll('.location-chip');
        const locationInput = document.querySelector('input[name="location"]');

        // 1. Limpiar todos los filtros
        clearBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Resetear checkboxes (solo mantener "Todos")
            const checkboxes = document.querySelectorAll('.custom-checkbox');
            checkboxes.forEach(cb => {
                if (cb.value === 'all') {
                    cb.checked = true;
                } else {
                    cb.checked = false;
                }
                cb.dispatchEvent(new Event('change'));
            });
            
            // Resetear radios
            const radios = document.querySelectorAll('input[type="radio"]');
            radios.forEach(radio => {
                radio.checked = radio.value === 'all' || radio.value === 'any';
            });
            
            // Resetear selects
            const selects = document.querySelectorAll('select');
            selects.forEach(select => {
                select.selectedIndex = 0;
            });
            
            // Resetear inputs de texto
            const textInputs = document.querySelectorAll('input[type="text"], input[type="number"]');
            textInputs.forEach(input => {
                input.value = '';
            });
            
            // Resetear chips
            locationChips.forEach(chip => {
                chip.classList.remove('active');
            });
            
            // Disparar submit para actualizar resultados
            form.dispatchEvent(new Event('submit'));
        });

        // 2. Manejar checkboxes (desmarcar "Todos" si se selecciona otro)
        const typeCheckboxes = document.querySelectorAll('input[name="type[]"]');
        typeCheckboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                const allCheckbox = document.querySelector('input[name="type[]"][value="all"]');
                
                if (this.value === 'all' && this.checked) {
                    // Si "Todos" está marcado, desmarcar los demás
                    typeCheckboxes.forEach(other => {
                        if (other.value !== 'all') {
                            other.checked = false;
                        }
                    });
                } else if (this.value !== 'all' && this.checked) {
                    // Si se marca otro, desmarcar "Todos"
                    allCheckbox.checked = false;
                }
                
                // Si ningún otro está marcado, marcar "Todos" automáticamente
                const anyOtherChecked = Array.from(typeCheckboxes).some(
                    cb => cb.value !== 'all' && cb.checked
                );
                
                if (!anyOtherChecked) {
                    allCheckbox.checked = true;
                }
            });
        });

        // 3. Manejar chips de ubicación
        locationChips.forEach(chip => {
            chip.addEventListener('click', function() {
                const location = this.dataset.location;
                
                // Toggle active class
                this.classList.toggle('active');
                
                // Si está activo, agregar al input
                if (this.classList.contains('active')) {
                    if (locationInput.value) {
                        locationInput.value = locationInput.value + ', ' + location;
                    } else {
                        locationInput.value = location;
                    }
                } else {
                    // Remover de la lista
                    const locations = locationInput.value.split(',').map(s => s.trim());
                    const index = locations.indexOf(location);
                    if (index > -1) {
                        locations.splice(index, 1);
                        locationInput.value = locations.join(', ');
                    }
                }
            });
        });

        // 4. Submit del formulario (AJAX o normal)
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Recoger datos del formulario
            const formData = new FormData(this);
            const data = Object.fromEntries(formData.entries());
            
            // Convertir arrays (checkboxes)
            data.type = formData.getAll('type[]');
            
            console.log('Filtros aplicados:', data);
            
            // Aquí puedes enviar los datos por AJAX o redirigir
            // Ejemplo: window.location.href = '/propiedades?' + new URLSearchParams(data).toString();
            
            // O con fetch:
            /*
            fetch(window.location.pathname + '?' + new URLSearchParams(data).toString(), {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                // Actualizar resultados
            })
            .catch(error => console.error('Error:', error));
            */
        });

        // 5. Validación de campos numéricos (área mínima < área máxima)
        const areaMin = document.getElementById('area_min');
        const areaMax = document.getElementById('area_max');
        
        function validateArea() {
            if (areaMin.value && areaMax.value) {
                if (parseInt(areaMin.value) > parseInt(areaMax.value)) {
                    areaMin.setCustomValidity('El área mínima no puede ser mayor que el área máxima');
                } else {
                    areaMin.setCustomValidity('');
                }
            }
        }
        
        areaMin.addEventListener('change', validateArea);
        areaMax.addEventListener('change', validateArea);
        
        // 6. Restaurar filtros desde URL (si existe)
        function restoreFiltersFromURL() {
            const params = new URLSearchParams(window.location.search);
            
            // Restaurar checkboxes
            const types = params.getAll('type[]');
            if (types.length > 0) {
                typeCheckboxes.forEach(cb => {
                    cb.checked = types.includes(cb.value);
                });
            }
            
            // Restaurar radios
            const radioNames = ['operation', 'price_range'];
            radioNames.forEach(name => {
                const value = params.get(name);
                if (value) {
                    const radio = document.querySelector(`input[name="${name}"][value="${value}"]`);
                    if (radio) radio.checked = true;
                }
            });
            
            // Restaurar selects
            const selectNames = ['bedrooms', 'bathrooms'];
            selectNames.forEach(name => {
                const value = params.get(name);
                if (value) {
                    const select = document.querySelector(`select[name="${name}"]`);
                    if (select) {
                        select.value = value;
                    }
                }
            });
            
            // Restaurar inputs de texto
            const textInputNames = ['location', 'area_min', 'area_max'];
            textInputNames.forEach(name => {
                const value = params.get(name);
                if (value) {
                    const input = document.querySelector(`input[name="${name}"]`);
                    if (input) input.value = value;
                }
            });
        }
        
        // Ejecutar restauración si existe
        if (window.location.search) {
            restoreFiltersFromURL();
        }
    });
</script>