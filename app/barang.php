<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class barang extends Model
{
    public function pesanan_detail() 
    {
        return $this->hasMany('App\PesananDetail','barang_id', 'id');
    }
}
