<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstablishmentUser extends Model
{
    protected $table = "establishment_user";
    protected $guarded = ["id"];
    protected $fillable = ["user_id","establishment_id"];
    protected $hidden = ["created_at", "updated_at"];

    public function establishment()
    {
        return $this->hasMany(Establishment::class, "id", "user_id");
    }

    public function establishment_user()
    {
        return $this->hasOne(Establishment::class, "id", "establishment_id")->select("id", "name", "cnpj", "cpf", "phone");
    }

    public function user()
    {
        return $this->hasOne(User::class, "id", "user_id");
    }

    // public function users()
    // {
    //     return $this->belongsToMany(User::class, EstablishmentUser::class, "establishment_id", "user_id");
    // }

    public function scopeFiltro($query, $filtro, $page) {
        return $query
        ->with("establishment_user")
        ->where('name', 'ILIKE', '%' . $filtro . '%')
        ->OrWhere('cpf', 'ILIKE', '%' . $filtro . '%')
        ->OrWhere('cnpj', 'ILIKE', '%' . $filtro . '%')
        ->OrWhere('phone', 'ILIKE', '%' . $filtro . '%')
        ->paginate($page);

    }
}
