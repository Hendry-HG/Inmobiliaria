// =====================================================
// SISTEMA DE SELECTOR DE CÓDIGOS TELEFÓNICOS
// =====================================================

class PhoneCodeSelector {
    constructor(options) {
        this.codeSelect = document.getElementById(options.codeSelectId);
        this.phoneInput = document.getElementById(options.phoneInputId);
        this.countrySelect = document.getElementById(options.countrySelectId);

        this.countriesData = [];
        this.currentConfig = {
            code: '+58',
            mask: '000-0000000',
            minLength: 10,
            maxLength: 10
        };

        this.init();
    }

    async init() {
        if (!this.codeSelect || !this.phoneInput) {
            console.warn('PhoneCodeSelector: Elementos no encontrados');
            return;
        }

        await this.loadPhoneCodes();

        this.codeSelect.addEventListener('change', () => this.onCodeChange());
        this.phoneInput.addEventListener('input', () => this.applyMask());
        this.phoneInput.addEventListener('paste', () => setTimeout(() => this.applyMask(), 10));
        this.phoneInput.addEventListener('keydown', (e) => this.handleKeyDown(e));

        if (this.countrySelect) {
            this.countrySelect.addEventListener('change', () => this.syncWithCountry());
        }

        this.applyMask();
        console.log('✅ PhoneCodeSelector inicializado');
    }

    async loadPhoneCodes() {
        try {
            const response = await fetch('/api/phone-codes');
            const text = await response.text();
            this.countriesData = JSON.parse(text);

            this.codeSelect.innerHTML = '';

            const uniqueCodes = new Map();
            this.countriesData.forEach(country => {
                const code = country.phone_code || '+58';
                if (!uniqueCodes.has(code)) {
                    uniqueCodes.set(code, {
                        code: code,
                        mask: country.phone_format || '000-0000000',
                        minLength: country.phone_min_length || 10,
                        maxLength: country.phone_max_length || 10,
                        countries: []
                    });
                }
                uniqueCodes.get(code).countries.push(country.name);
            });

            const sortedCodes = Array.from(uniqueCodes.entries())
                .sort((a, b) => {
                    const numA = parseInt(a[0].replace(/\D/g, '')) || 0;
                    const numB = parseInt(b[0].replace(/\D/g, '')) || 0;
                    return numA - numB;
                });

            sortedCodes.forEach(([code, config]) => {
                const option = document.createElement('option');
                option.value = code;
                option.textContent = code;
                option.dataset.mask = config.mask;
                option.dataset.minLength = config.minLength;
                option.dataset.maxLength = config.maxLength;
                option.dataset.countries = config.countries.join(', ');
                this.codeSelect.appendChild(option);
            });

            console.log('✅ Códigos cargados:', uniqueCodes.size);

        } catch (error) {
            console.error('❌ Error cargando códigos:', error);
            this.loadDefaultCodes();
        }
    }

    loadDefaultCodes() {
        const defaultCodes = [
            { code: '+58', mask: '000-0000000', min: 10, max: 10 },
            { code: '+1', mask: '000-000-0000', min: 10, max: 10 },
            { code: '+34', mask: '000-000-000', min: 9, max: 9 },
            { code: '+52', mask: '000-000-0000', min: 10, max: 10 },
            { code: '+54', mask: '000-000-0000', min: 10, max: 10 },
            { code: '+55', mask: '(00) 00000-0000', min: 10, max: 11 },
            { code: '+57', mask: '000-000-0000', min: 10, max: 10 },
        ];

        this.codeSelect.innerHTML = '';
        defaultCodes.forEach(dc => {
            const option = document.createElement('option');
            option.value = dc.code;
            option.textContent = dc.code;
            option.dataset.mask = dc.mask;
            option.dataset.minLength = dc.min;
            option.dataset.maxLength = dc.max;
            this.codeSelect.appendChild(option);
        });
    }

    onCodeChange() {
        const selectedOption = this.codeSelect.options[this.codeSelect.selectedIndex];

        this.currentConfig = {
            code: selectedOption.value,
            mask: selectedOption.dataset.mask || '000-0000000',
            minLength: parseInt(selectedOption.dataset.minLength) || 10,
            maxLength: parseInt(selectedOption.dataset.maxLength) || 10
        };

        const placeholder = this.generatePlaceholder(this.currentConfig.mask);
        this.phoneInput.placeholder = placeholder;
        this.phoneInput.minLength = this.currentConfig.minLength;
        this.phoneInput.maxLength = this.currentConfig.maxLength;

        const countries = selectedOption.dataset.countries;
        if (countries) {
            this.codeSelect.title = 'Países: ' + countries;
        }

        this.applyMask();
        console.log('📞 Código cambiado:', this.currentConfig.code);
    }

    syncWithCountry() {
        if (!this.countrySelect || !this.countrySelect.value) return;

        const countryId = parseInt(this.countrySelect.value);
        const country = this.countriesData.find(c => c.id === countryId);

        if (country && country.phone_code) {
            for (let i = 0; i < this.codeSelect.options.length; i++) {
                if (this.codeSelect.options[i].value === country.phone_code) {
                    this.codeSelect.selectedIndex = i;
                    this.onCodeChange();
                    break;
                }
            }
        }
    }

    generatePlaceholder(mask) {
        const numbers = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '0'];
        let result = '';
        let numIndex = 0;

        for (let i = 0; i < mask.length; i++) {
            if (mask[i] === '0') {
                result += numbers[numIndex % numbers.length];
                numIndex++;
            } else {
                result += mask[i];
            }
        }

        return result;
    }

    handleKeyDown(e) {
        const controlKeys = ['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Tab', 'Home', 'End'];
        if (controlKeys.includes(e.key)) return;

        if (!/^\d$/.test(e.key)) {
            e.preventDefault();
        }
    }

    applyMask() {
        let value = this.phoneInput.value;
        let numbers = value.replace(/\D/g, '');

        if (numbers.length > this.currentConfig.maxLength) {
            numbers = numbers.substring(0, this.currentConfig.maxLength);
        }

        if (numbers.length === 0) {
            this.phoneInput.value = '';
            return;
        }

        let formatted = this.formatWithMask(numbers, this.currentConfig.mask);
        this.phoneInput.value = formatted;
    }

    formatWithMask(numbers, mask) {
        let result = '';
        let numberIndex = 0;

        for (let i = 0; i < mask.length; i++) {
            if (numberIndex >= numbers.length) break;

            if (mask[i] === '0') {
                result += numbers[numberIndex];
                numberIndex++;
            } else {
                result += mask[i];
            }
        }

        if (numberIndex < numbers.length) {
            result += numbers.substring(numberIndex);
        }

        return result;
    }

    getFullNumber() {
        return this.currentConfig.code + this.getLocalNumber();
    }

    getLocalNumber() {
        return this.phoneInput.value.replace(/\D/g, '');
    }

    isValid() {
        const numbers = this.getLocalNumber();
        return numbers.length >= this.currentConfig.minLength &&
               numbers.length <= this.currentConfig.maxLength;
    }
}

// =====================================================
// SISTEMA DE MÁSCARA TELEFÓNICA (Compatibilidad)
// =====================================================

class PhoneMask {
    constructor(options) {
        this.countrySelect = document.getElementById(options.countrySelectId);
        this.phoneInput = document.getElementById(options.phoneInputId);
        this.phoneCodeDisplay = document.getElementById(options.phoneCodeDisplayId);

        this.currentMask = '000-0000000';
        this.currentCode = '+58';

        this.init();
    }

    init() {
        if (!this.countrySelect || !this.phoneInput) {
            console.warn('PhoneMask: Elementos no encontrados');
            return;
        }

        if (this.countrySelect.value) {
            this.loadPhoneConfig(this.countrySelect.value);
        } else {
            this.applyDefaultMask();
        }

        this.countrySelect.addEventListener('change', (e) => {
            const countryId = e.target.value;
            if (countryId) {
                this.loadPhoneConfig(countryId);
            } else {
                this.applyDefaultMask();
            }
        });

        this.phoneInput.addEventListener('input', () => this.applyMask());
        this.phoneInput.addEventListener('paste', () => setTimeout(() => this.applyMask(), 10));
    }

    async loadPhoneConfig(countryId) {
        try {
            const response = await fetch(`/api/phone-config/${countryId}`);
            const config = await response.json();

            this.currentCode = config.code;
            this.currentMask = config.mask;

            if (this.phoneCodeDisplay) {
                this.phoneCodeDisplay.textContent = this.currentCode;
            }

            this.phoneInput.placeholder = config.placeholder || '412 1234567';
            this.phoneInput.minLength = config.minLength || 7;
            this.phoneInput.maxLength = config.maxLength || 15;

            this.applyMask();
            console.log('✅ PhoneMask: Configuración cargada', config);

        } catch (error) {
            console.error('❌ PhoneMask: Error', error);
            this.applyDefaultMask();
        }
    }

    applyDefaultMask() {
        this.currentCode = '+58';
        this.currentMask = '000-0000000';

        if (this.phoneCodeDisplay) {
            this.phoneCodeDisplay.textContent = '+58';
        }

        this.phoneInput.placeholder = '412 1234567';
        this.applyMask();
    }

    applyMask() {
        let value = this.phoneInput.value;
        let numbers = value.replace(/\D/g, '');

        if (numbers.length === 0) {
            this.phoneInput.value = '';
            return;
        }

        let formatted = this.formatWithMask(numbers, this.currentMask);
        this.phoneInput.value = formatted;
    }

    formatWithMask(numbers, mask) {
        let result = '';
        let numberIndex = 0;

        for (let i = 0; i < mask.length; i++) {
            if (numberIndex >= numbers.length) break;

            if (mask[i] === '0') {
                result += numbers[numberIndex];
                numberIndex++;
            } else {
                result += mask[i];
            }
        }

        if (numberIndex < numbers.length) {
            result += numbers.substring(numberIndex);
        }

        return result;
    }

    getFullNumber() {
        return this.currentCode + this.phoneInput.value.replace(/\D/g, '');
    }

    getLocalNumber() {
        return this.phoneInput.value.replace(/\D/g, '');
    }

    isValid() {
        const numbers = this.getLocalNumber();
        const min = parseInt(this.phoneInput.minLength) || 7;
        const max = parseInt(this.phoneInput.maxLength) || 15;
        return numbers.length >= min && numbers.length <= max;
    }
}

// =====================================================
// INICIALIZACIÓN PRINCIPAL
// =====================================================

let phoneSelector = null;

function initPhoneSelector() {
    if (document.getElementById('phone-code-select') && document.getElementById('reg-phone')) {
        console.log('📞 Inicializando PhoneCodeSelector...');
        phoneSelector = new PhoneCodeSelector({
            codeSelectId: 'phone-code-select',
            phoneInputId: 'reg-phone',
            countrySelectId: 'reg_country_id'
        });
    } else if (document.getElementById('reg_country_id') && document.getElementById('reg-phone')) {
        console.log('📞 Inicializando PhoneMask (fallback)...');
        window.phoneMask = new PhoneMask({
            countrySelectId: 'reg_country_id',
            phoneInputId: 'reg-phone',
            phoneCodeDisplayId: 'phone-code-display'
        });
    }
}

document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 JS Principal Inicializado.');

    setTimeout(function() {
        const inputs = document.querySelectorAll('#form-login input, #form-register input');
        inputs.forEach(input => {
            if (input.type !== 'checkbox' && input.type !== 'submit' && input.type !== 'hidden') {
                input.value = '';
            }
        });
    }, 50);

    const regPass = document.getElementById('reg-pass');
    const regPassConfirm = document.getElementById('reg-pass-confirm');

    function validatePassword() {
        if (regPass && regPassConfirm) {
            const errorDiv = document.getElementById('pass-error');
            if (regPass.value !== regPassConfirm.value) {
                if(errorDiv) errorDiv.classList.remove('hidden');
                return false;
            } else {
                if(errorDiv) errorDiv.classList.add('hidden');
                return true;
            }
        }
        return true;
    }

    if (regPass) regPass.addEventListener('input', validatePassword);
    if (regPassConfirm) regPassConfirm.addEventListener('input', validatePassword);

    const registerForm = document.getElementById('form-register');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            if (!validatePassword()) {
                e.preventDefault();
                alert('Las contraseñas no coinciden');
            }
        });
    }

    setTimeout(() => {
        initRegLocationSelects();
    }, 100);

    setTimeout(initPhoneSelector, 600);
});

// =====================================================
// FUNCIONES DEL MODAL
// =====================================================

function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
    }
}

function switchAuthTab(type) {
    const loginForm = document.getElementById('form-login');
    const registerForm = document.getElementById('form-register');
    const loginTab = document.getElementById('tab-login');
    const registerTab = document.getElementById('tab-register');
    const modalTitle = document.getElementById('modal-title');
    const modalSubtitle = document.querySelector('#modal-title + p');

    if (type === 'login') {
        if(loginForm) loginForm.classList.remove('hidden');
        if(registerForm) registerForm.classList.add('hidden');

        if(loginTab) {
            loginTab.classList.add('text-slate-900', 'font-bold', 'border-b-2', 'border-mso-gold');
            loginTab.classList.remove('text-slate-500', 'font-medium');
        }
        if(registerTab) {
            registerTab.classList.remove('text-slate-900', 'font-bold', 'border-b-2', 'border-mso-gold');
            registerTab.classList.add('text-slate-500', 'font-medium');
        }

        if (modalTitle) modalTitle.innerText = "Bienvenido";
        if (modalSubtitle) modalSubtitle.innerText = "Ingresa a tu cuenta para continuar";
    } else {
        if(loginForm) loginForm.classList.add('hidden');
        if(registerForm) registerForm.classList.remove('hidden');

        if(registerTab) {
            registerTab.classList.add('text-slate-900', 'font-bold', 'border-b-2', 'border-mso-gold');
            registerTab.classList.remove('text-slate-500', 'font-medium');
        }
        if(loginTab) {
            loginTab.classList.remove('text-slate-900', 'font-bold', 'border-b-2', 'border-mso-gold');
            loginTab.classList.add('text-slate-500', 'font-medium');
        }

        if (modalTitle) modalTitle.innerText = "Crear Cuenta";
        if (modalSubtitle) modalSubtitle.innerText = "Únete a MSO Grupo Inmobiliario";

        setTimeout(() => {
            initRegLocationSelects();
            setTimeout(initPhoneSelector, 300);
        }, 100);
    }
}

// =====================================================
// SELECTS DINÁMICOS DE UBICACIÓN
// =====================================================

window.regLocationSelectsInitialized = false;

function initRegLocationSelects() {
    if (window.regLocationSelectsInitialized) {
        const countrySelect = document.getElementById('reg_country_id');
        if (countrySelect && countrySelect.options.length <= 1) {
            loadRegCountries();
        }
        return;
    }

    console.log('📍 Inicializando selects de ubicación...');
    loadRegCountries();

    const setupEvent = (id, callback) => {
        const el = document.getElementById(id);
        if (el) {
            const newEl = el.cloneNode(true);
            el.parentNode.replaceChild(newEl, el);
            newEl.addEventListener('change', callback);
        }
    };

    setupEvent('reg_country_id', (e) => loadRegStates(e.target.value));
    setupEvent('reg_state_id', (e) => loadRegMunicipalities(e.target.value));
    setupEvent('reg_municipality_id', (e) => loadRegParishes(e.target.value));
    setupEvent('reg_parish_id', (e) => loadRegCities(e.target.value));

    window.regLocationSelectsInitialized = true;
}

function loadRegCountries() {
    const select = document.getElementById('reg_country_id');
    if (!select) return;
    if (select.options.length > 1) return;

    fetch('/api/register/countries')
        .then(response => response.json())
        .then(data => {
            select.innerHTML = '<option value="">Seleccione un país</option>';
            data.forEach(item => {
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = item.name;
                select.appendChild(option);
            });
            console.log('✅ Países cargados:', data.length);
        })
        .catch(error => console.error('❌ Error cargando países:', error));
}

function loadRegStates(countryId) {
    const select = document.getElementById('reg_state_id');
    resetRegSelects('reg_state_id');

    if (!countryId) {
        if(select) select.disabled = true;
        return;
    }

    fetch(`/api/register/states/${countryId}`)
        .then(response => response.json())
        .then(data => {
            if(select) {
                select.disabled = false;
                select.innerHTML = '<option value="">Seleccione un estado</option>';
                data.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.id;
                    option.textContent = item.name;
                    select.appendChild(option);
                });
            }
        })
        .catch(error => console.error('❌ Error estados:', error));
}

function loadRegMunicipalities(stateId) {
    const select = document.getElementById('reg_municipality_id');
    resetRegSelects('reg_municipality_id');

    if (!stateId) {
        if(select) select.disabled = true;
        return;
    }

    fetch(`/api/register/municipalities/${stateId}`)
        .then(response => response.json())
        .then(data => {
            if(select) {
                select.disabled = false;
                select.innerHTML = '<option value="">Seleccione un municipio</option>';
                data.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.id;
                    option.textContent = item.name;
                    select.appendChild(option);
                });
            }
        })
        .catch(error => console.error('❌ Error municipios:', error));
}

function loadRegParishes(municipalityId) {
    const select = document.getElementById('reg_parish_id');
    resetRegSelects('reg_parish_id');

    if (!municipalityId) {
        if(select) select.disabled = true;
        return;
    }

    fetch(`/api/register/parishes/${municipalityId}`)
        .then(response => response.json())
        .then(data => {
            if(select) {
                select.disabled = false;
                select.innerHTML = '<option value="">Seleccione una parroquia</option>';
                data.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.id;
                    option.textContent = item.name;
                    select.appendChild(option);
                });
            }
        })
        .catch(error => console.error('❌ Error parroquias:', error));
}

function loadRegCities(parishId) {
    const select = document.getElementById('reg_city_id');

    if (!parishId) {
        if(select) {
            select.disabled = true;
            select.innerHTML = '<option value="">Primero seleccione una parroquia</option>';
        }
        return;
    }

    fetch(`/api/register/cities/${parishId}`)
        .then(response => response.json())
        .then(data => {
            if(select) {
                select.disabled = false;
                select.innerHTML = '<option value="">Seleccione una ciudad</option>';
                data.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.id;
                    option.textContent = item.name;
                    select.appendChild(option);
                });
            }
        })
        .catch(error => console.error('❌ Error ciudades:', error));
}

function resetRegSelects(currentId) {
    const map = {
        'reg_state_id': ['reg_municipality_id', 'reg_parish_id', 'reg_city_id'],
        'reg_municipality_id': ['reg_parish_id', 'reg_city_id'],
        'reg_parish_id': ['reg_city_id']
    };

    const idsToReset = map[currentId] || [];
    idsToReset.forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.disabled = true;
            let label = id.replace('reg_', '').replace('_id', '').replace('_', ' ');
            el.innerHTML = `<option value="">Primero seleccione un ${label}</option>`;
        }
    });
}

// Exponer funciones globales
window.openModal = openModal;
window.closeModal = closeModal;
window.switchAuthTab = switchAuthTab;
window.initPhoneSelector = initPhoneSelector;
