@php
$proporcion=1.3;

@endphp

 @if($medidas['ancho']>$medidas['alto'])
    <div class="tarjeta-contenedor container d-flex">
@else
    <div class="tarjeta-contenedor container ">
@endif
    <div class="row">
                <div class="col-12" style="height: {{$medidas['alto']*0.16}}cm;">
                    <img class="col-12" style="height: 100%; width: auto;"  src="{{Storage::disk('imagenes')->url('logo.png')}}" alt="">
                </div>
                
                <div class="col-12" >{{$datos['codigo']}}</div>
                   
                <div class="col-12" style="max-width: {{$medidas['ancho']*0.5}}cm;word-wrap: break-word; white-space: normal;" >ddvsdfsdfsdfsdfsdddddddddddddddddddddddddddddddddddddddddddddd</div>
        </div>
        <div class="row" >
            @if($medidas['ancho']>$medidas['alto'])
                {{ QrCode::size(($medidas['alto'])*30)->generate(10213) }}
            @else
                {{ QrCode::size(($medidas['ancho'])*30)->generate(10213) }}
            @endif
            
        </div>
       
</div>
    