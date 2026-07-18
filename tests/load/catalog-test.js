// tests/load/catalog-test.js
// Prueba de catálogo con búsquedas

import http from 'k6/http';
import { check, sleep } from 'k6';

export const options = {
    stages: [
        { duration: '2m', target: 100 },
        { duration: '3m', target: 200 },
        { duration: '1m', target: 0 },
    ],
    thresholds: {
        http_req_duration: ['p(95)<1500'],
        http_req_failed: ['rate<0.02'],
    },
};

const BASE_URL = 'http://localhost:8000';

const SEARCH_TERMS = ['casa', 'apartamento', 'oficina', 'terreno', 'local', 'quinta'];
const TYPES = ['venta', 'alquiler', 'venta/alquiler'];

export default function() {
    const params = {
        search: SEARCH_TERMS[Math.floor(Math.random() * SEARCH_TERMS.length)],
        type: TYPES[Math.floor(Math.random() * TYPES.length)],
        min_price: String(Math.floor(Math.random() * 50000) + 10000),
        max_price: String(Math.floor(Math.random() * 400000) + 100000),
        bedrooms: String(Math.floor(Math.random() * 3) + 1),
        bathrooms: String(Math.floor(Math.random() * 2) + 1),
        state_id: '1',
        city_id: '1',
    };

    const url = `${BASE_URL}/catalogo?${Object.entries(params)
        .filter(([_, value]) => value)
        .map(([key, value]) => `${key}=${encodeURIComponent(value)}`)
        .join('&')}`;

    const res = http.get(url, {
        headers: {
            'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        },
    });

    check(res, {
        ' Catálogo carga correctamente': (r) => r.status === 200,
        ' Tiempo de respuesta < 1.5s': (r) => r.timings.duration < 1500,
    });

    if (Math.random() < 0.2) {
        const propertyId = Math.floor(Math.random() * 100) + 1;
        const detailRes = http.get(`${BASE_URL}/catalogo/${propertyId}`);

        check(detailRes, {
            ' Detalle de propiedad carga': (r) => r.status === 200 || r.status === 404,
        });
    }

    sleep(0.5);
}
