<?php

namespace Modules\Transporte\Models;

use App\Models\Tenant\ModelTenant;
use App\Models\Tenant\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserTerminal extends ModelTenant
{
    //

    protected $table = 'user_terminal';

    protected $with=['terminal'];

    public function terminal() : BelongsTo{
        return $this->belongsTo(TransporteTerminales::class,'terminal_id','id');
    }

    public function user() : BelongsTo{
        return $this->belongsTo(User::class,'terminal_id','id');
    }

}
