<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // Información de contacto
            [
                'key' => 'company_name',
                'value' => 'MSO Grupo Inmobiliario C.A.',
                'type' => 'text',
                'group' => 'company',
                'label' => 'Nombre de la Empresa',
            ],
            [
                'key' => 'company_email',
                'value' => 'info@msoinmobiliaria.com',
                'type' => 'text',
                'group' => 'contact',
                'label' => 'Email Principal',
            ],
            [
                'key' => 'company_phone',
                'value' => '+58 212 1234567',
                'type' => 'text',
                'group' => 'contact',
                'label' => 'Teléfono Principal',
            ],
            [
                'key' => 'company_whatsapp',
                'value' => '+584121234567',
                'type' => 'text',
                'group' => 'contact',
                'label' => 'Número de WhatsApp',
            ],
            [
                'key' => 'company_address',
                'value' => 'Av. Principal, Centro Comercial Plaza, Local 5, Caracas',
                'type' => 'text',
                'group' => 'company',
                'label' => 'Dirección',
            ],

            // Redes Sociales
            [
                'key' => 'social_instagram',
                'value' => 'https://instagram.com/msoinmobiliaria',
                'type' => 'text',
                'group' => 'social',
                'label' => 'Instagram',
            ],
            [
                'key' => 'social_facebook',
                'value' => 'https://facebook.com/msoinmobiliaria',
                'type' => 'text',
                'group' => 'social',
                'label' => 'Facebook',
            ],
            [
                'key' => 'social_twitter',
                'value' => 'https://twitter.com/msoinmobiliaria',
                'type' => 'text',
                'group' => 'social',
                'label' => 'Twitter',
            ],
            [
                'key' => 'social_linkedin',
                'value' => 'https://linkedin.com/company/msoinmobiliaria',
                'type' => 'text',
                'group' => 'social',
                'label' => 'LinkedIn',
            ],

            // Horarios
            [
                'key' => 'business_hours',
                'value' => json_encode([
                    'lunes' => '8:00 AM - 5:00 PM',
                    'martes' => '8:00 AM - 5:00 PM',
                    'miércoles' => '8:00 AM - 5:00 PM',
                    'jueves' => '8:00 AM - 5:00 PM',
                    'viernes' => '8:00 AM - 5:00 PM',
                    'sábado' => '9:00 AM - 1:00 PM',
                    'domingo' => 'Cerrado',
                ]),
                'type' => 'json',
                'group' => 'company',
                'label' => 'Horario de Atención',
            ],

            // SEO
            [
                'key' => 'seo_title',
                'value' => 'MSO Grupo Inmobiliario | Las mejores propiedades en Venezuela',
                'type' => 'text',
                'group' => 'seo',
                'label' => 'Título SEO por defecto',
            ],
            [
                'key' => 'seo_description',
                'value' => 'Encuentra las mejores propiedades en venta y alquiler en Venezuela. Apartamentos, casas, terrenos y más con el respaldo de MSO Grupo Inmobiliario.',
                'type' => 'text',
                'group' => 'seo',
                'label' => 'Descripción SEO por defecto',
            ],
            [
                'key' => 'seo_keywords',
                'value' => 'inmobiliaria, propiedades, venta, alquiler, apartamentos, casas, terrenos, Caracas, Venezuela',
                'type' => 'text',
                'group' => 'seo',
                'label' => 'Palabras clave SEO',
            ],

            // Configuración general
            [
                'key' => 'currency_symbol',
                'value' => '$',
                'type' => 'text',
                'group' => 'general',
                'label' => 'Símbolo de moneda',
            ],
            [
                'key' => 'currency_code',
                'value' => 'USD',
                'type' => 'text',
                'group' => 'general',
                'label' => 'Código de moneda',
            ],
            [
                'key' => 'items_per_page',
                'value' => '12',
                'type' => 'number',
                'group' => 'general',
                'label' => 'Propiedades por página',
            ],
            [
                'key' => 'enable_chat',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'features',
                'label' => 'Habilitar chat en vivo',
            ],
            [
                'key' => 'enable_appointments',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'features',
                'label' => 'Habilitar agendamiento de citas',
            ],
            [
                'key' => 'maintenance_mode',
                'value' => 'false',
                'type' => 'boolean',
                'group' => 'system',
                'label' => 'Modo mantenimiento',
            ],
        ];

        foreach ($settings as $setting) {
            Setting::create($setting);
        }
    }
}
