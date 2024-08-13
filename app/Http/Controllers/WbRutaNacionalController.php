<?php

namespace App\Http\Controllers;

use App\Http\interfaces\Vervos;
use App\Models\wbRutaNacional;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WbRutaNacionalController extends BaseController implements Vervos
{

    /**
     * Registra una nueva ruta nacional.
     *
     * @param  \Illuminate\Http\Request  $req
     * @return \Illuminate\Http\JsonResponse
     */
    public function post(Request $req)
    {
        // Validar los campos de la solicitud
        $validator = Validator::make($req->all(), [
            'codigo' => 'required|max:100',
            'pkInicial' => 'required|regex:/^\d{5}$/',
            'pkFinal' => 'required|regex:/^\d{5}$/',
            'nombre' => 'required|max:165',
        ]);

        // Comprobar si la validación falla y devolver los errores
        if ($validator->fails()) {
            return $this->handleAlert($validator->errors());
        }

        // Verificar si ya existe una ruta nacional con el mismo nombre
        if (wbRutaNacional::where('nombre', $req->nombre)->first() != null) {
            return $this->handleAlert(__('messages.el_nombre_de_ruta_ya_se_encuentra_registrado_en_el_sistema'));
        }

        // Establecer los valores en el modelo
        $modeloRegistrar = new wbRutaNacional;
        $modeloRegistrar->codigo = $req->codigo;
        $modeloRegistrar->pk_inicial = $req->pkInicial;
        $modeloRegistrar->pk_final = $req->pkFinal;
        $modeloRegistrar->nombre = $req->nombre;

        // Establecer el usuario que registra
        $modeloRegistrar->fk_usuario = $this->traitGetIdUsuarioToken($req);

        // Establecer el proyecto y compañía en el modelo
        $modeloRegistrar = $this->traitSetProyectoYCompania($req, $modeloRegistrar);

        // Verificar si el registro se guardó correctamente
        if ($modeloRegistrar->save()) {
            // Devolver una respuesta exitosa con un mensaje de éxito
            return $this->handleResponse($req, [], __('messages.ruta_nacional_registrada'));
        } else {
            // Devolver una alerta en caso de que no se haya guardado correctamente
            return $this->handleAlert(__('messages.ruta_nacional_no_registrada_intente_nuevamente_si_el_error_persiste_consulte_con_el_administrador'));
        }
    }

    /**
     * Actualiza una ruta nacional existente.
     *
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $req
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $req, $id)
    {
        // Verificar si el ID de la ruta nacional es válido
        if (!is_numeric($id)) {
            return $this->handleAlert(__('messages.ruta_nacional_no_valida'));
        }

        // Validar los campos de la solicitud
        $validator = Validator::make($req->all(), [
            'codigo' => 'required|max:100',
            'pkInicial' => 'required|regex:/^\d{5}$/',
            'pkFinal' => 'required|regex:/^\d{5}$/',
            'nombre' => 'required|max:165',
        ]);

        // Comprobar si la validación falla y devolver los errores
        if ($validator->fails()) {
            return $this->handleAlert($validator->errors());
        }

        // Buscar la ruta nacional en la base de datos por su ID
        $modelo = wbRutaNacional::find($id);

        // Verificar si la ruta nacional no fue encontrada
        if ($modelo == null) {
            return $this->handleAlert(__('messages.ruta_nacional_no_encontrada'));
        }

        // Verificar si se modificó el nombre y si ya existe otra ruta con el mismo nombre
        if ($modelo->nombre != $req->nombre) {
            if (wbRutaNacional::where('nombre', $req->nombre)->count() == 1) {
                return $this->handleAlert(__('messages.el_nombre_de_ruta_ya_se_encuentra_registrado_en_el_sistema'));
            }
        }

        // Actualizar los valores en el modelo
        $modelo->codigo = $req->codigo;
        $modelo->pk_inicial = $req->pkInicial;
        $modelo->pk_final = $req->pkFinal;
        $modelo->nombre = $req->nombre;

        // Guardar los cambios en la base de datos
        if ($modelo->save()) {
            return  $this->handleResponse($req, [], __('messages.ruta_nacional_modificada'));
        } else {
            return $this->handleAlert(__('messages.ruta_nacional_no_modificada_intente_nuevamente_si_el_error_persiste_consulte_con_el_administrador'));
        }
    }

    /**
     * @param Request $request
     * @param $id
     */
    public function delete(Request $request, $id)
    {
        // TODO: Implement delete() method.
    }

    /**
     * @param Request $request
     */
    public function get(Request $request)
    {
        // TODO: Implement get() method.
        $consulta = wbRutaNacional::select('Wb_ruta_nacional.*')
        ->where('estado', 1);
        $consulta = $this->filtrarPorProyecto($request, $consulta)->get();
        return $this->handleResponse($request, $this->rutaNAcionalToArray($consulta), __('messages.consultado'));
    }

    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }
}
