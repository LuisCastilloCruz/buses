<?php

namespace Modules\Transporte\Models;
use App\Models\Tenant\ModelTenant;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransporteVehiculo extends ModelTenant
{
    protected $table = 'transporte_vehiculos';
    protected $appends = [
        'img_front',
        'img_back'
    ];

    protected $fillable = ['placa','nombre','asientos','pisos','image_front','image_back'];


    public function getImgFrontAttribute(){

        if(!is_null($this->image_front)) return asset('storage\\images\\'.$this->image_front);
        return null;
        
    }

    public function getImgBackAttribute(){
        if(!is_null($this->image_back)) return asset('storage\\images\\'.$this->image_back);

        return null;
        
    }

    public function seats() : HasMany{
        //traigo solo
        return $this->hasMany(TransporteAsiento::class,'vehiculo_id','id');
    }

}
