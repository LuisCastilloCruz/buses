<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Transporte\Models\TransporteTerminales;

class UserTerminal extends ModelTenant
{
    //
    protected $table = 'user_terminal';

    public function terminal() : BelongsTo {
        return $this->belongsTo(TransporteTerminales::class,'terminal_id','id');
    }

    public function user() : BelongsTo {
        return $this->belongsTo(User::class,'user_id','id');
    }
}
