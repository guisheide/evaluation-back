<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'cpf',
        'profile_id',
    ];

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    public function addresses()
    {
        return $this->belongsToMany(Address::class, 'address_user');
    }

    public function scopeFilter(Builder $query, array $filters): void
    {
        $query->when($filters['name'] ?? null, function ($q, $name) {
            $q->where('name', 'like', "%{$name}%");
        });

        $query->when($filters['cpf'] ?? null, function ($q, $cpf) {
            $q->where('cpf', $cpf);
        });

        $query->when($filters['profile_id'] ?? null, function ($q, $profile_id) {
            $q->where('profile_id', $profile_id);
        });

        $startDate = $filters['start_date'] ?? null;
        $endDate = $filters['end_date'] ?? null;
        $dateField = $filters['date_field'] ?? 'created_at';

        if (!in_array($dateField, ['created_at', 'updated_at'])) {
            $dateField = 'created_at';
        }
        if ($startDate && $endDate) {
            $query->whereBetween($dateField, [$startDate, $endDate . ' 23:59:59']);
        } else if ($startDate) {
            $query->where($dateField, '>=', $startDate);
        } else if ($endDate) {
            $query->where($dateField, '<=', $endDate . ' 23:59:59');
        }
    }

        public function detachAddress(Address $address): void
    {
        $this->addresses()->detach($address->id);
    }

    public function deleteWithAddresses(): void
    {
        DB::transaction(function () {
            $this->addresses->each(function ($address) {
                if ($address->users()->count() === 1) {
                    $address->delete();
                }
            });

            $this->delete();
        });
    }
}