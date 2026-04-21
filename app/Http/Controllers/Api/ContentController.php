<?php

namespace App\Http\Controllers\Api;

use App\Models\KategoriNamaTabungan;
use App\Models\KategoriJenisTabungan;
use Illuminate\Http\Request;

class ContentController extends ApiController
{
    public function categories()
    {
        return $this->success([
            'names' => KategoriNamaTabungan::all(),
            'types' => KategoriJenisTabungan::all(),
        ]);
    }

    public function storeName(Request $request)
    {
        $request->validate(['nama' => 'required|string|max:255']);
        $item = KategoriNamaTabungan::create($request->all());
        return $this->success($item, 'Category name created', 201);
    }
    
    // Additional content management methods can be added here
}
