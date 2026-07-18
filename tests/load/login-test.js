// tests/load/login-test.js
// Prueba de inicio de sesión concurrente

import http from 'k6/http';
import { check, sleep } from 'k6';

export const options = {
    stages: [
        { duration: '1m', target: 50 },
        { duration: '3m', target: 100 },
        { duration: '2m', target: 100 },
        { duration: '1m', target: 0 },
    ],
    thresholds: {
        http_req_duration: ['p(95)<1000'],
        http_req_failed: ['rate<0.01'],
    },
};

const BASE_URL = 'http://localhost:8000';

const TEST_USERS = [];
for (let i = 1; i <= 100; i++) {
    TEST_USERS.push(`cliente${i}@test.com`);
}

export default function() {
    const csrfRes = http.get(`${BASE_URL}/sanctum/csrf-cookie`);
    const csrfToken = csrfRes.cookies['XSRF-TOKEN'];

    const email = TEST_USERS[Math.floor(Math.random() * TEST_USERS.length)];

    const payload = {
        email: email,
        password: 'password123',
        remember: 'on',
        _token: csrfToken,
    };

    const loginRes = http.post(`${BASE_URL}/login`, payload, {
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-XSRF-TOKEN': csrfToken,
            'Referer': BASE_URL,
        },
        redirects: 3,
    });

    check(loginRes, {
        ' Login exitoso': (r) => r.status === 302 || r.status === 200,
        ' Tiempo de respuesta < 1s': (r) => r.timings.duration < 1000,
    });

    if (loginRes.status === 302 || loginRes.status === 200) {
        const cookies = loginRes.cookies;
        const dashboardRes = http.get(`${BASE_URL}/dashboard`, {
            headers: {
                'Cookie': `laravel_session=${cookies['laravel_session']}; XSRF-TOKEN=${cookies['XSRF-TOKEN']}`,
            },
        });

        check(dashboardRes, {
            ' Dashboard carga': (r) => r.status === 200,
        });
    }

    sleep(0.3);
}
