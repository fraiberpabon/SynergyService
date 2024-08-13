<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0">
    <title>Notificaciones de Seguridad en sitio</title>
</head>
<body>
    <p>{{ $mensaje }}</p>
    <ul>
        <li>Solicitud: {{ $solicitud->id_registro_proyecto }}</li>

        <li>fecha de inicio: {{ $solicitud->fecha_inicio }}</li>
        
        <li>fecha de finalizacion: {{ $solicitud->fecha_finalizacion }}</li>
        @if(!empty($solicitud->fk_id_tramo))
            <li>Tramo: {{$solicitud->fk_id_tramo}}</li>
        @endif
        @if(!empty($solicitud->fk_id_hito))
            <li>Hito: {{$solicitud->fk_id_hito}}</li>
        @endif
        @if(!empty($solicitud->abscisa))
            <li>Abscisa: {{$solicitud->abscisa}}</li>
        @endif
        @if(!empty($solicitud->otra_ubicacion))
            <li>Otra Ubicacion: {{$solicitud->otra_ubicacion}}</li>
        @endif
        @if(!empty($solicitud->usuario_crea_name))
            <li>Solicitante: {{$solicitud->usuario_crea_name}}</li>
        @endif
        @if(!empty($solicitud->observaciones))
            <li>Observaciones:  {{$solicitud->observaciones}}</li>
        @endif


    </ul>
</body>
</html>