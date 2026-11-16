<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MechanicSparepartRequest extends Model
{
    protected $table = 'mechanic_sparepart_requests';
    protected $primaryKey = 'id_request';
    protected $guarded = ['id_request'];

    protected function casts(): array
    {
        return [
            'jumlah' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function servis()
    {
        return $this->belongsTo(Servis::class, 'id_servis', 'id_servis');
    }

    public function petugas()
    {
        return $this->belongsTo(Petugas::class, 'id_petugas', 'id_petugas');
    }
}
