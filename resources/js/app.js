/**
 * Importar Bootstrap
 */
import './bootstrap';

/**
 * Importar Alpine.js
 */
import Alpine from 'alpinejs';

/**
 * Hacer Alpine disponible globalmente
 */
window.Alpine = Alpine;

/**
 * Componentes personalizados de Alpine
 */
document.addEventListener('alpine:init', () => {
    // Componente para filtros de propiedades
    Alpine.data('propertyFilters', () => ({
        filters: {
            price_min: '',
            price_max: '',
            location: '',
            type: '',
            bedrooms: '',
            bathrooms: ''
        },
        applyFilters() {
            this.$dispatch('filters-applied', this.filters);
        },
        clearFilters() {
            this.filters = {
                price_min: '',
                price_max: '',
                location: '',
                type: '',
                bedrooms: '',
                bathrooms: ''
            };
            this.applyFilters();
        }
    }));

    // Componente para el botón flotante de soporte
    Alpine.data('supportFloatingButton', () => ({
        isOpen: false,
        toggle() {
            this.isOpen = !this.isOpen;
        }
    }));

    // Componente para favoritos
    Alpine.data('favoriteButton', () => ({
        isFavorite: false,
        propertyId: null,
        async toggleFavorite() {
            this.isFavorite = !this.isFavorite;
            try {
                const response = await fetch(`/favorites/${this.propertyId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                const data = await response.json();
            } catch (error) {
                console.error('Error toggling favorite:', error);
                this.isFavorite = !this.isFavorite;
            }
        }
    }));
});

/**
 * Iniciar Alpine
 */
Alpine.start();
