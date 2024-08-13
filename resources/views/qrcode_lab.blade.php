
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Webu lab QR code</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"/>
</head>
<!-- <body style="width: 10.8cm; flex-wrap: wrap; display: flex">
    @foreach ($equipos as $equipo)
        <div class="bg-white" style="height: 2.5cm; width: 3cm; padding: 0.3cm; margin-left: 0.3cm">
            <div class="card col-12 bg-white border-0 d-block" style="flex-direction: row;">
                <div class="card-header bg-white border-0 p-0">
                    <div class="row p-0" style="text-align: center; font-size: 10px; ">
                        <div class="col-12 text-break">
                           {{--  <img class="col-12"  src="{{Storage::disk('imagenes')->url('logo.png')}}" alt=""> --}}
                           <div class="col-12 fw-bold ">LAB{{$equipo}}</div>
                        </div>
                        {{-- <div class="col-6 text-break">
                                <div class="col-12 fw-bold">{{$equipo->SerialNumber}}</div>
                        </div> --}}
                    </div>
                </div>
                <div class="card-body  text-center text-break p-0 bg-white border-0" style="width: 65px margin-right: 0.1cm">
                    {{ QrCode::size(65)->generate('LAB'.$equipo) }}
                </div>
            </div>
        </div>
    @endforeach
</body> -->
<body style="width: 10.8cm;" class="row justify-content-around">
    @foreach ($equipos as $equipo)
        <div class="bg-white" style="height: 2.5cm; width: 2.9cm;" class="col">
            <div class="row" style="transform: rotate(-0.25turn);">
                <div class="col-12" style="font-size: x-small; text-align: center;">LAB{{$equipo}}</div>
                <div class="col-12" style="text-align: center;">{{ QrCode::size(65)->generate('LAB'.$equipo) }}</div>
            </div>
        </div>
    @endforeach
</body>

</html>
