<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MwsConfig extends Model
{
    //
    public function hasManyMarketplaces()
    {
        return $this->hasMany(MwsMarketplace::class, 'SellerId', 'SellerId');
    }
}
