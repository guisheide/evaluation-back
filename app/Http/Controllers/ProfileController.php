<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProfileResource; // 1. Importar o resource
use App\Models\Profile;                 // 2. Importar o model
use Illuminate\Http\Request;

class ProfileController extends Controller
{

    public function index()
    {
        $profiles = Profile::all();

        return ProfileResource::collection($profiles);
    }

}