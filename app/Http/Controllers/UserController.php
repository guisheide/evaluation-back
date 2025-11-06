<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\Address;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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
        // Hash::make() no controller não esta sendo necessário, pois foi automizado no model User
        try {
            $user = new User($data);
            $user->save();
            $addresses = collect($data['addresses'])->map(fn($addr) => Address::firstOrCreate(
    [
                    'zip_code' => $addr['zip_code'],
                    'street' => $addr['street'],
                    'number' => $addr['number'],
                ],
        [
                    'neighborhood' => $addr['neighborhood'],
                    'city' => $addr['city'],
                    'state' => $addr['state'],
                ]
            ));

            $user->addresses()->syncWithoutDetaching($addresses->pluck('id'));
            $user->load(['addresses', 'profile']);
            return (new UserResource($user))->response()->setStatusCode(201);
            } catch (\Throwable $e) {
                Log::error('Erro ao criar usuário: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

                return response()->json([
                    'message' => 'Erro ao registrar usuário.',
                    'error' => $e->getMessage(),
                ], 500);
            }
    }
}
