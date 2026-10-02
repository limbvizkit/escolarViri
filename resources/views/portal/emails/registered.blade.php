<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido al portal de pagos</title>
</head>
<body style="font-family: Inter, system-ui, sans-serif; color: #1f2937; line-height: 1.5;">
    <div style="max-width: 520px; margin: 0 auto; padding: 2rem;">
        <h2 style="color: #0d6efd; margin-bottom: 1rem;">Hola, {{ $portalUser->name }}</h2>

        <p>Tu registro en el portal de pagos fue exitoso. Estas son tus credenciales de acceso temporales:</p>

        <table style="width: 100%; background: #f4f6fb; border-radius: .55rem; padding: 1rem; margin: 1.25rem 0;">
            <tr>
                <td style="padding: .4rem 0; font-weight: 600;">Usuario / correo:</td>
                <td>{{ $portalUser->email }}</td>
            </tr>
            <tr>
                <td style="padding: .4rem 0; font-weight: 600;">Contraseña temporal:</td>
                <td>{{ $tempPassword }}</td>
            </tr>
        </table>

        <p>Al iniciar sesión por primera vez se te pedirá cambiar esta contraseña.</p>

        <p style="margin-top: 1.5rem; font-size: .85rem; color: #6c757d;">
            Si no solicitaste este registro, ignora este mensaje.
        </p>
    </div>
</body>
</html>
