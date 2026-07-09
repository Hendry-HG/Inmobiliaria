<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cuenta Reactivada</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .content {
            padding: 30px;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #D4AF37;
            color: #1e3c72;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin: 20px 0;
        }
        .success-box {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            text-align: center;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✅ ¡Cuenta Reactivada!</h1>
        </div>

        <div class="content">
            <p>Hola <strong>{{ $user->name }}</strong>,</p>

            <div class="success-box">
                <strong>¡Tu cuenta ha sido reactivada exitosamente!</strong>
            </div>

            <p>Nos complace informarte que tu cuenta en {{ config('app.name', 'MSO Inmobiliaria') }} ha sido reactivada.
               Ya puedes acceder a todos los servicios y funcionalidades de la plataforma.</p>

            <p>Puedes iniciar sesión haciendo clic en el siguiente botón:</p>

            <div style="text-align: center;">
                <a href="{{ route('login') }}" class="button" style="color: #1e3c72;">
                    Iniciar Sesión
                </a>
            </div>

            <p>Te recomendamos revisar tu perfil para asegurarte de que toda tu información esté actualizada.</p>

            <hr style="border: 1px solid #eee; margin: 20px 0;">

            <p>Gracias por confiar en nosotros. Si tienes alguna pregunta, nuestro equipo de soporte está disponible para ayudarte.</p>

            <p>Saludos cordiales,<br>
            <strong>El Equipo de {{ config('app.name', 'MSO Inmobiliaria') }}</strong></p>
        </div>

        <div class="footer">
            <p>© {{ date('Y') }} {{ config('app.name', 'MSO Inmobiliaria') }}. Todos los derechos reservados.</p>
            <p>Este es un correo automático, por favor no respondas a esta dirección.</p>
        </div>
    </div>
</body>
</html>
