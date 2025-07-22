<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TimeRecord extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'recorded_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'recorded_at' => 'datetime',
        ];
    }

    /**
     * Relacionamentos
     */
    
    // Funcionário que fez o registro
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scopes
     */
    
    // Filtrar por período entre duas datas
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('recorded_at', [$startDate, $endDate]);
    }

    // Filtrar por funcionário específico
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Ordenar por data/hora mais recente
    public function scopeLatest($query)
    {
        return $query->orderBy('recorded_at', 'desc');
    }
}
