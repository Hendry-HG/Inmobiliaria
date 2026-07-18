// tests/load/register-test.js
import http from 'k6/http';
import { check, sleep } from 'k6';

export const options = {
    stages: [
        { duration: '30s', target: 3 },   // Sube a 3 usuarios en 30 segundos
        { duration: '1m', target: 5 },    // Sube a 5 usuarios en 1 minuto
        { duration: '1m', target: 5 },    // Mantén 5 usuarios por 1 minuto
        { duration: '30s', target: 0 },   // Baja a 0 en 30 segundos
    ],
    thresholds: {
        http_req_duration: ['p(95)<5000'],
        http_req_failed: ['rate<0.05'],
    },
};

const BASE_URL = 'http://localhost:8000';

const SECURITY_QUESTIONS = [
    '¿Cuál es el nombre de tu primera mascota?',
    '¿Cuál es el apellido de soltera de tu madre?',
    '¿En qué ciudad naciste?',
    '¿Cuál es tu comida favorita?',
    '¿Cuál es el nombre de tu mejor amigo de la infancia?',
];

export default function() {
    // 1. Obtener la página de registro para extraer el token CSRF
    const registerPage = http.get(`${BASE_URL}/register`);

    // 2. Extraer el token CSRF del HTML
    let csrfToken = '';
    const match = registerPage.body.match(/<input[^>]*name="_token"[^>]*value="([^"]*)"[^>]*>/);
    if (match) {
        csrfToken = match[1];
    }

    // 3. Si no se encontró el token, intentar con la cookie
    if (!csrfToken) {
        const csrfRes = http.get(`${BASE_URL}/sanctum/csrf-cookie`);
        csrfToken = csrfRes.cookies['XSRF-TOKEN'] || csrfRes.cookies['xsrf-token'] || '';
    }

    // 4. Seleccionar 3 preguntas diferentes
    const shuffled = [...SECURITY_QUESTIONS].sort(() => 0.5 - Math.random());
    const selectedQuestions = shuffled.slice(0, 3);

    const uniqueId = `${__VU}_${__ITER}_${Date.now()}`;

    const payload = {
        _token: csrfToken,
        name: `Usuario${uniqueId}`,
        last_name: `Apellido${uniqueId}`,
        email: `user${uniqueId}@test.com`,
        password: 'password123',
        password_confirmation: 'password123',
        phone: `0412${String(Math.floor(Math.random() * 10000000)).padStart(7, '0')}`,
        address: 'Calle de prueba 123',
        id_type: 'V',
        id_number: String(Math.floor(Math.random() * 90000000) + 10000000),
        country_id: '1',
        state_id: '1',
        municipality_id: '1',
        parish_id: '1',
        city_id: '1',
        terms: '1',
        security_question_1: selectedQuestions[0],
        security_answer_1: `respuesta_${uniqueId}_1`,
        security_question_2: selectedQuestions[1],
        security_answer_2: `respuesta_${uniqueId}_2`,
        security_question_3: selectedQuestions[2],
        security_answer_3: `respuesta_${uniqueId}_3`,
    };

    const registerRes = http.post(`${BASE_URL}/register`, payload, {
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'Referer': BASE_URL,
            'X-CSRF-TOKEN': csrfToken,
            'X-XSRF-TOKEN': csrfToken,
        },
        redirects: 3,
        timeout: '60s',
    });

    const isSuccess = registerRes.status === 302 ||
                      registerRes.status === 201 ||
                      registerRes.status === 200 ||
                      registerRes.body.includes('dashboard') ||
                      registerRes.body.includes('Bienvenido');

    check(registerRes, {
        ' Registro exitoso': (r) => isSuccess,
        ' Tiempo de respuesta < 5s': (r) => r.timings.duration < 5000,
    });

    sleep(0.5);
}
