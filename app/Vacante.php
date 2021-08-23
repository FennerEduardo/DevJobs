<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Vacante extends Model
{
    protected $fillable = [
        'titulo', 'descripcion', 'categoria_id', 'experiencia_id', 'ubicacion_id', 'salario_id', 'descripcion', 'imagen', 'skills'
    ];

    /**Relación 1:1 categoría y vacante */
    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }
    /**Relación 1:1 experiencia y vacante */
    public function experiencia()
    {
        return $this->belongsTo(Experiencia::class);
    }
    /**Relación 1:1 ubicacion y vacante */
    public function ubicacion()
    {
        return $this->belongsTo(Ubicacion::class);
    }
    /**Relación 1:1 salario y vacante */
    public function salario()
    {
        return $this->belongsTo(Salario::class);
    }
    /**Relación 1:1 usuario y vacante */
    public function reclutador()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    /**Relación de 1:n con candidatos */
    public function candidatos()
    {
        return $this->hasMany(Candidato::class);
    }
}
