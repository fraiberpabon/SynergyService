<style>

 /* .btn-secondary-outline:hover {
  color: #fff !important;
    background-color: #dc3545 !important;
    border-color: #dc3545 !important;
} */

.btn.custom-checkbox {
  color:#000000;
  background-color: #E0E0DF;
  border-color: #E0E0DF;
}

.btn.custom-checkbox:hover {
  color:#000000;
  background-color: #e0e0e0;
  border-color: #e0e0e0;
}

.btn-check:checked+.btn, :not(.btn-check)+.btn:active, .btn:first-child:active, .btn.active, .btn.show {
    color: #fff ;
    background-color: #FF5050;
    border-color: #FF5050;
}
</style>


<div class="row">
  <div class="col-md-2 mb-2">
    <label>Material</label>
    <div class="form-group">
      <div class="form-group has-feedback">
        <select class="form-select col-12" name="material" required>
          @if($materiales->count() > 0)
          <!--option></option-->
            @foreach($materiales as $key)
              <option value="{{$key->id_material_lista}}">{{$key->Nombre}}</option>
            @endForeach
          @else
            Sin registros por mostrar
          @endif   
        </select>
      </div>
    </div>
  </div>
  <div class="col-md-2 mb-2">
    <label>Tipo Control</label>
    <div class="form-group">
      <div class="form-group has-feedback">
        <select class="form-select col-12" name="tipo_control" required>
          @if($tipoControl->count() > 0)
          <!--option></option-->
            @foreach($tipoControl as $key)
              <option value="{{$key->id_tipo_control}}">{{$key->descripcion}}</option>
            @endForeach
          @else
            Sin registros por mostrar
          @endif   
        </select>
      </div>
    </div>
  </div>
</div>
@if($ensayos->count() > 0)
<div class="container">
  <label>Ensayos</label>
  <br>
  <div class="d-flex flex-row flex-wrap">
      @foreach($ensayos as $key)
        <div >
          <div class="form-group m-1">
            <div class="form-group has-feedback">
              <input type="checkbox" class="btn-check form-control col-12" id="btn-check-outlined-{{ $key->id_ensayo }}" name="ensayos" autocomplete="off" value="{{ $key->id_ensayo }}">
              <label class="btn btn-outline-secondary custom-checkbox" for="btn-check-outlined-{{ $key->id_ensayo }}" id="check-{{ $key->id_ensayo }}">{{$key->nombre .' - '. $key->descripcion}}</label><br>
            </div>
          </div>
        </div>
      @endForeach
  </div>
</div>
  
@else
    Sin registros por mostrar
@endif 