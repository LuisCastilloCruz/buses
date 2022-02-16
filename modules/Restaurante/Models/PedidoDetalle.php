<?php

namespace Modules\Restaurante\Models;

use App\Models\Tenant\ModelTenant;
use http\Env\Request;

class PedidoDetalle extends ModelTenant
{
    protected $table = 'restaurante_pedidos_detalles';
    protected $fillable = ['pedido_id', 'producto_id','cantidad','precio','impreso','dividir'];


    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

}
