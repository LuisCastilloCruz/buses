<?php

namespace Modules\Transporte\Models;

use App\Models\System\Client;
use App\Models\Tenant\Document;
use App\Models\Tenant\ModelTenant;
use App\Models\Tenant\Person;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransportePasaje extends ModelTenant
{
    //
    protected $table = 'transporte_pasajes';

    protected $fillable = [
        'serie',
        'document_id',
        'pasajero_id',
        'asiento_id',
        'precio',
        'fecha_salida',
        // 'fecha_llegada',
        'programacion_id',
        'estado_asiento_id'

    ];


    public function programacion() : BelongsTo{
        return $this->belongsTo(TransporteProgramacion::class,'programacion_id','id');
    }

    public function asiento() : BelongsTo{
        return $this->belongsTo(TransporteAsiento::class,'asiento_id','id');
    }

    public function pasajero() : BelongsTo{
        return $this->belongsTo(Person::class,'pasajero_id','id');
    }

    public function document() : BelongsTo{
        return $this->belongsTo(Document::class,'document_id','id');
    }
}
