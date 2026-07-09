<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cuenta Desactivada</title>
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
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
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
        .warning-box {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
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
            <h1>Cuenta Desactivada</h1>
        </div>

        <div class="content">
            <p>Hola <strong>{{ $user->name }}</strong>,</p>

            <div class="warning-box">
                <strong>⚠️ Tu cuenta ha sido desactivada temporalmente.</strong>
            </div>

            <p>Te informamos que tu cuenta en {{ config('app.name', 'MSO Inmobiliaria') }} ha sido desactivada por un administrador.</p>

            <p>Si crees que esto es un error o deseas reactivar tu cuenta, puedes solicitar la reactivación haciendo clic en el siguiente botón:</p>

            <div style="text-align: center;">
                <a href="{{ route('account.inactive') }}" class="button" style="color: #1e3c72;">
                    Solicitar Reactivación
                </a>
            </div>

            <p>Si tienes alguna pregunta sobre esta desactivación, no dudes en contactar a nuestro equipo de soporte.</p>

            <hr style="border: 1px solid #eee; margin: 20px 0;">

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
