<?php

namespace Modules\Transporte\Models;

use App\Models\Tenant\Document;
use App\Models\Tenant\ModelTenant;
use App\Models\Tenant\Person;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransporteEncomienda extends ModelTenant
{
    protected $table = 'transporte_encomiendas';
    protected $fillable = [
        'document_id',
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
        return $this->belongsTo(Person::class,'destinatario_id','id');
    }

    public function remitente(){
        return $this->belongsTo(Person::class,'remitente_id','id');
    }

    public function document() : BelongsTo{
        return $this->belongsTo(Document::class,'document_id','id');
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
