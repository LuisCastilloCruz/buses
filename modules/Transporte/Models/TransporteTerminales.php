<?php

namespace Modules\Transporte\Models;

use App\Models\Tenant\ModelTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransporteTerminales extends ModelTenant
{
    //
    protected $table = 'transporte_terminales';
    protected $fillable = ['nombre','direccion','destino_id'];


    public function destino() : BelongsTo{
        return $this->belongsTo(TransporteDestino::class,'destino_id','id');
    }


    public function programaciones(){
        return $this->hasMany(TransporteProgramacion::class,'terminal_origen_id','id');
    }
}
