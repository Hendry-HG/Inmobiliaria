// tests/load/register-debug.js
import http from 'k6/http';
import { check } from 'k6';

export default function() {
    const BASE_URL = 'http://localhost:8000';

    // Obtener CSRF
    const csrfRes = http.get(`${BASE_URL}/sanctum/csrf-cookie`);
    const csrfToken = csrfRes.cookies['XSRF-TOKEN'];

    const uniqueId = Date.now();

    const payload = {
        name: `Debug${uniqueId}`,
        last_name: 'Test',
        email: `debug${uniqueId}@test.com`,
        password: 'password123',
        password_confirmation: 'password123',
        phone: '04121234567',
        address: 'Calle de prueba',
        id_type: 'V',
        id_number: `1234567${uniqueId}`,
        country_id: '1',
        state_id: '1',
        municipality_id: '1',
        parish_id: '1',
        city_id: '1',
        terms: '1',
        security_question_1: '¿Cuál es el nombre de tu primera mascota?',
        security_answer_1: 'respuesta_1',
        security_question_2: '¿Cuál es el apellido de soltera de tu madre?',
        security_answer_2: 'respuesta_2',
        security_question_3: '¿En qué ciudad naciste?',
        security_answer_3: 'respuesta_3',
    };

    const registerRes = http.post(`${BASE_URL}/register`, payload, {
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-XSRF-TOKEN': csrfToken,
            'Referer': BASE_URL,
        },
        redirects: 0,
    });

    // Mostrar información de depuración
    console.log(` STATUS CODE: ${registerRes.status}`);
    console.log(` STATUS TEXT: ${registerRes.status_text}`);
    console.log(` HEADERS:`, registerRes.headers);
    console.log(` BODY (primeros 300 chars): ${registerRes.body.substring(0, 300)}`);

    check(registerRes, {
        ' Registro exitoso (302)': (r) => r.status === 302,
        ' Registro exitoso (201)': (r) => r.status === 201,
        'Registro exitoso (200)': (r) => r.status === 200,
    });
}
