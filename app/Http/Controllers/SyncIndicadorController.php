<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\interfaces\Vervos;
use App\Models\SyncIndicador;


class SyncIndicadorController extends BaseController implements Vervos
{
    //
    public function post(Request $req)
    {
        // TODO: Implement post() method.
    }

    public function update(Request $req, $id)
    {
        // TODO: Implement update() method.
    }

    public function delete(Request $request, $id)
    {
        // TODO: Implement delete() method.
    }

    public function get(Request $request)
    {

        return $this->handleResponse($request, SyncIndicador::get(), 'consultado');
    }

    public function getPorProyecto(Request $request, $proyecto)
    {
        // TODO: Implement getPorProyecto() method.
    }

    public function getByUser(Request $request, $user){

    }
    //
}
