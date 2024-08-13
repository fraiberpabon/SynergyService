<?php

namespace App\Http\interfaces;

use Illuminate\Http\Request;

interface Vervos
{
    /**
     * @param Request $req
     */
    public function post(Request $req);

    /**
     * @param Request $req
     * @param $id
     */
    public function update(Request $req, $id);
   
    /**
     * @param Request $request
     * @param $id
     */
    public function delete(Request $request, $id);

    /**
     * @param Request $request
     */
    public function get(Request $request);

    public function getPorProyecto(Request $request, $proyecto);

}
