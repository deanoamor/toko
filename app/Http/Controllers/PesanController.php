<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\barang;

class PesanController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index($id)
    {
    	$pesan = barang::where('id', $id)->first();

    	return view('pesan.index', compact('pesan'));
    }
}
