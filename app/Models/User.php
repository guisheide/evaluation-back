<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable; // Usando o padrão
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'cpf',
        'profile_id',
        'password',
    ];

    protected static function booted()
    {
        static::creating(function ($user) {
            $user->password = Hash::make($user->password);
        });
    }

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

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        } else if ($startDate) {
            $query->where('created_at', '>=', $startDate);
        } else if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }
    }

    public function updateWithRelations(array $data): self
    {
        $this->fill(collect($data)->only([
            'name', 'email', 'cpf', 'profile_id', 'password'
        ])->toArray())->save();

        if (!empty($data['addresses'])) {
            $this->syncAddresses($data['addresses']);
        }

        return $this;
    }
    public function syncAddresses(array $addresses): void
    {
        $addressIds = collect($addresses)->map(fn($addr) =>
            Address::firstOrCreate(
                [
                    'zip_code' => $addr['zip_code'],
                    'street'   => $addr['street'],
                    'number'   => $addr['number'],
                ],
                [
                    'neighborhood' => $addr['neighborhood'],
                    'city'         => $addr['city'],
                    'state'        => $addr['state'],
                ]
            )->id
        );

        $this->addresses()->sync($addressIds);
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