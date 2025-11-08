<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\Address;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{

    public function index(Request $request)
    {
        $users = User::query() 
        ->with('profile', 'addresses')
        ->filter($request->all()) 
        ->paginate($request->get('per_page', 15));

    return UserResource::collection($users);
    }


    public function store(Request $request)
    {
        try {
            $data = $request->all();

            // Criação do usuário
            $user = User::create([
                'name'       => $data['name'] ?? null,
                'email'      => $data['email'] ?? null,
                'cpf'        => $data['cpf'] ?? null,
                'profile_id' => $data['profile_id'] ?? null,
            ]);

            // Se houver endereços, cria ou vincula
            if (!empty($data['addresses']) && is_array($data['addresses'])) {
                $addressIds = collect($data['addresses'])->map(function ($addr) {
                    $address = Address::firstOrCreate(
                        [
                            'zip_code' => $addr['zip_code'],
                            'street'   => $addr['street'],
                            'number'   => $addr['number'],
                        ],
                        [
                            'neighborhood' => $addr['neighborhood'] ?? '',
                            'city'         => $addr['city'] ?? '',
                            'state'        => $addr['state'] ?? '',
                        ]
                    );

                    return $address->id;
                });

                $user->addresses()->syncWithoutDetaching($addressIds);
            }

            // Carrega relações e retorna
            $user->load(['addresses', 'profile']);

            return (new UserResource($user))
                ->response()
                ->setStatusCode(201);

        } catch (\Throwable $e) {
            Log::error('Erro ao criar usuário: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Erro ao registrar usuário.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(User $user)
    {
        try {
            $user->deleteWithAddresses();
            return response()->json(['message' => 'Usuário excluído com sucesso.'], 200);
        } catch (\Throwable $e) {
            Log::error('Erro ao deletar usuário: ' . $e->getMessage());
            return response()->json(['message' => 'Erro ao excluir usuário.'], 500);
        }
    }

    public function detachAddress(User $user, Address $address)
    {
        try {
            $user->detachAddress($address);
            return response()->json(['message' => 'Endereço excluído com sucesso.'], 200);
        } catch (\Throwable $e) {
            Log::error('Erro ao desvincular endereço: ' . $e->getMessage());
            return response()->json(['message' => 'Erro ao excluir endereço.'], 500);
        }
    }
    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'profile_id' => 'sometimes|exists:profiles,id',
            'addresses' => 'sometimes|array',
            'addresses.*.street' => 'required_with:addresses|string|max:255',
            'addresses.*.zip_code' => 'required_with:addresses|string|max:20',
        ]);

        try {
            $user->fill($data);
            $user->save();

            if (!empty($data['addresses'])) {
                $addressIds = collect($data['addresses'])->map(function ($addr) {
                    return Address::firstOrCreate([
                        'zip_code' => $addr['zip_code'],
                        'street' => $addr['street'],
                    ])->id;
                });
                $user->addresses()->sync($addressIds);
            }

            if (!empty($data['profile_id'])) {
                $user->profile()->associate($data['profile_id']);
                $user->save();
            }

            $user->load(['addresses', 'profile']);

            return new UserResource($user);

        } catch (\Throwable $e) {
            Log::error('Erro ao atualizar usuário: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Erro ao atualizar usuário.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
