<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class MorphMapServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
        Relation::morphMap([
            'MS' => \App\Models\WbSolicitudMateriales::class,
            'AS' => \App\Models\WbSolitudAsfalto::class,
            'CS' => \App\Models\solicitudConcreto::class,
            'MF' => \App\Models\WbFormulaLista::class,
            'AF' => \App\Models\WbAsfaltFormula::class,
            'CF' => \App\Models\Formula::class,
        ]);
    }
}
