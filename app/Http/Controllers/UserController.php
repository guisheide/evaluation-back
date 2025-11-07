<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\Address;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $data = $request->validated();

        try {
            $user = User::create($data);

            if (!empty($data['addresses'])) {
                $user->syncAddresses($data['addresses']);
            }

            $user->load(['addresses', 'profile']);
            return (new UserResource($user))->response()->setStatusCode(201);

            } catch (\Throwable $e) {
                Log::error('Erro ao criar usuário: ' . $e->getMessage(), [
                    'trace' => $e->getTraceAsString(),
                ]);
                return response()->json([
                    'message' => 'Erro ao registrar usuário.',
                    'error' => $e->getMessage(),
                ], 500);
        }
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validated();

        try {
            $user->updateWithRelations($data);
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
}
