<?php

namespace Modules\Transporte\Models;

use App\Models\Tenant\ModelTenant;
use App\Models\Tenant\Series;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransporteManifiesto extends ModelTenant
{
    //
    protected $table = 'transporte_manifiesto';

    protected $fillable = [
        'serie',
        'numero',
        'tipo',
        'chofer_id',
        'copiloto_id',
        'fecha',
        'hora',
        'observaciones',
        'programacion_id'
    ];

    public function chofer() : BelongsTo{
        return $this->belongsTo(TransporteChofer::class,'chofer_id','id');
    }

    public function copiloto() : BelongsTo{
        return $this->belongsTo(TransporteChofer::class,'copiloto_id','id');
    }

    public function programacion() : BelongsTo{
        return $this->belongsTo(TransporteProgramacion::class,'programacion_id','id');
    }

    public function serie() : BelongsTo{
        return $this->belongsTo(Series::class,'serie','id');
    }
}
