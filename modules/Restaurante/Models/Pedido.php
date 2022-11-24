<?php

namespace Modules\Restaurante\Models;

use App\Models\Tenant\ModelTenant;
use http\Env\Request;

class Pedido extends ModelTenant
{

    protected $table = 'restaurante_pedidos';
    protected $fillable = ['mesa_id', 'document_id','user_id','tipo','estado','establishment_id'];

    public function pedido_detalles(Request $request){
        return $this->hasMany(PedidoDetalle::class);
    }

    public function mesa(){
        return $this->belongsTo(Mesa::class, 'mesa_id','id');
    }

}
