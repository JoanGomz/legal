<?php

namespace App\Models\Operation;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Clients extends BaseModel
{
    use HasFactory;

    protected $table = 'clients';

    protected $fillable = [
        'id',
        'uuid',
        'first_name',
        'last_name',
        'email',
        'phone',
        'address',
        'document_number',
        'document_type',
        'gender',
        'birth_date',
        'coutrie_id',
        'neighborhood',
        'marital_status',
        'has_vehicle',
        'contact_channel',
        'accepted_terms_at',
        'parks_id',
        'amount',
        'data_consent',
        'accepts_promotions',
        'pdf_url',
        'is_deleted',
        'brand_id',
        'created_at',
        'updated_at',
        'deleted_at',
        'user_creator',
        'user_last_update',
        'user_delete',
    ];

    public function park()
    {
        return $this->belongsTo(Parks::class, 'parks_id');
    }

    public function consents()
    {
        return $this->hasMany(Consents::class, 'client_id');
    }
}
