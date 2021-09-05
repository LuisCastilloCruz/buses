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
        return asset('storage\\images\\'.$this->image_front);
    }

    public function getImgBackAttribute(){
        return asset('storage\\images\\'.$this->image_back);
    }

    public function seats() : HasMany{
        //traigo solo
        return $this->hasMany(TransporteAsiento::class,'vehiculo_id','id');
    }

}
