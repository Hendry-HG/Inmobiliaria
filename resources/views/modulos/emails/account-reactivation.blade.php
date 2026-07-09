<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reactivación de Cuenta</title>
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
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
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
            transition: all 0.3s;
        }
        .button:hover {
            background-color: #1e3c72;
            color: #D4AF37;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        .warning {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 12px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .link-fallback {
            word-break: break-all;
            color: #1e3c72;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Solicitud de Reactivación de Cuenta</h1>
        </div>

        <div class="content">
            <p>Hola <strong>{{ $user->name }}</strong>,</p>

            <p>Hemos recibido una solicitud para reactivar tu cuenta en nuestra plataforma.</p>

            <div class="warning">
                <strong>⚠️ Importante:</strong> Si no has solicitado esta reactivación, puedes ignorar este correo.
                Tu cuenta permanecerá inactiva y segura.
            </div>

            <p>Para reactivar tu cuenta, haz clic en el siguiente botón:</p>

            <div style="text-align: center;">
                <a href="{{ $reactivationLink }}" class="button" style="color: #1e3c72;">
                    Reactivar mi Cuenta
                </a>
            </div>

            <p>O copia y pega este enlace en tu navegador:</p>
            <p class="link-fallback">{{ $reactivationLink }}</p>

            <p><strong>Este enlace expirará en 24 horas</strong> por razones de seguridad.</p>

            <hr style="border: 1px solid #eee; margin: 20px 0;">

            <p style="font-size: 14px;">
                <strong>Información adicional:</strong><br>
                • Correo asociado: {{ $user->email }}<br>
                • Fecha de solicitud: {{ now()->format('d/m/Y H:i:s') }}
            </p>

            <p>Si tienes alguna pregunta, no dudes en contactarnos.</p>

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
