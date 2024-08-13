
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>QR Equipos</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"/>
</head>
<body class="container-fluid bg-white" style="width: 8cm;">
    @foreach ($equipos as $equipo)
        <div class="bg-white pt-3" style="height: 9.95cm;">
            <div class="card col-12 bg-white border-0" >
                <div class="card-header bg-white border-0">
                    <div class="row">
                        <div class="col-8">
                            <img class="col-12"  src="{{Storage::disk('imagenes')->url('logo.png')}}" alt="">
                        </div>
                        <div class="col-4 ">
                                <div class="col-12 fw-bold">{{$equipo->equiment_id}}</div>
                                <div class="col-12 fw-bold">{{$equipo->placa}}</div>
                        </div>
                    </div>
                </div>
                <div class="card-body  text-center p-0 bg-white border-0">
                    {{ QrCode::size(250)->generate('Q'.$equipo->equiment_id) }}
                </div>
                <div class="card-footer text-center bg-white border-0 p-0">
                    <h6>{{$equipo->nombreCompania}}</h6>
                </div>
            </div>
        </div>
    @endforeach
</body>
</html>
