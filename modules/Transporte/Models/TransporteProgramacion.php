<?php

namespace Modules\Transporte\Models;

use App\Models\Tenant\ModelTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class TransporteProgramacion extends ModelTenant
{
    //

    protected $table = 'transporte_programaciones';
    protected $fillable = [
        'terminal_destino_id',
        'terminal_origen_id',
        'vehiculo_id',
        'hora_salida',
        'tiempo_aproximado'
    ];


    public function origen() : BelongsTo{
        return $this->belongsTo(TransporteTerminales::class,'terminal_origen_id','id');
    }

    public function destino() : belongsTo{
        return $this->belongsTo(TransporteTerminales::class,'terminal_destino_id','id');
    }

    public function vehiculo() : BelongsTo{
        return $this->belongsTo(TransporteVehiculo::class,'vehiculo_id','id');
    }

    public function rutas() : HasMany{
        return $this->hasMany(TransporteRuta::class,'programacion_id','id');
    }

    /*
        esta funcion la uso para traer todas las programaciones que sean iguales 
        o mayores a la fecha aca
    */
    public function scopeWhereEqualsOrBiggerDate($query,$now = null){
        $now = is_null($now) ? date('Y-m-d') : $now;
        $month = (int) date('m');
        return $query->whereRaw("DATE_FORMAT(NOW(),'%Y-%m-%d') <= '{$now}'")
        ->whereMonth(DB::raw("NOW()"),$month);
    }

    public function syncRutas(array $rutas){

        TransporteRuta::where('programacion_id',$this->id)
        ->delete();

        foreach($rutas as $ruta){
            TransporteRuta::create([
                'terminal_id' => $ruta,
                'programacion_id' => $this->id
            ]);
        }
    }

    public function getHoraSalidaAttribute($value){ 
        return date('g:i a',strtotime($value));
    }


    
}
