<?php

namespace App\Http\trait;

use Carbon\Carbon;

trait DateHelpersTrait
{
    public function traitGetDateTimeNow() {
        $date = Carbon::now()->toDateTimeString();
        return Carbon::createFromFormat('Y-m-d H:i:s', $date)->format('d-m-Y H:i:s');
    }

    public function traitGetDateNow() {
        $date = Carbon::now()->toDateTimeString();
        return Carbon::createFromFormat('Y-m-d H:i:s', $date)->format('d-m-Y');
    }

    public function traitGetDateNowFormatFull() {
        $date = Carbon::now()->toDateTimeString();
        return Carbon::createFromFormat('Y-m-d H:i:s', $date)->format('YmdHis');
    }

    public function traitGetTime() {
        $date = Carbon::now()->toDateTimeString();
        return Carbon::createFromFormat('Y-m-d H:i:s', $date)->format('H:i:s');
    }

    public function traitDateFormateado($date) {
        return Carbon::createFromFormat('Y-m-d H:i:s', $date)->format('d-m-Y H:i:s');
    }
}
