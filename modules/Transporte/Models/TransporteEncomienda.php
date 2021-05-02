<?php

namespace Modules\Transporte\Models;

use App\Models\System\Client;
use App\Models\Tenant\ModelTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransporteEncomienda extends ModelTenant
{
    protected $table = 'transporte_encomiendas';
    protected $fillable = [
        'descripcion',
        'remitente_id',
        'destinatario_id',
        'fecha_salida',
        'programacion_id',
        'estado_envio_id',
        'estado_pago_id'
    ];


    public function programacion(){
        return $this->belongsTo(TransporteProgramacion::class,'programacion_id','id');
    }

    public function destinatario(){
        return $this->belongsTo(Client::class,'destinatario_id','id');
    }

    public function remitente(){
        return $this->belongsTo(Client::class,'remitente_id','id');
    }

    // public function getFechaSalidaAttribute($value){ 

    //     setlocale(LC_TIME, 'es');
    //     $date = \Carbon\Carbon::parse($value);
    //     return $date->formatLocalized('%d de %B %Y');
    // }

    public function estadoPago() : BelongsTo{
        return $this->belongsTo(TransporteEstadoPagoEncomienda::class,'estado_pago_id','id');
    }

    public function estadoEnvio() : BelongsTo{
        return $this->belongsTo(TransporteEstadoEnvio::class,'estado_envio_id','id');
    }
}
