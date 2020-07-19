<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\barang;
use App\Pesanan;
use App\PesananDetail;
use Auth;
use SweetAlert;
use Carbon\Carbon;

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

    public function pesan (Request $request , $id)
    {
        $pesan = barang::where('id', $id)->first();
        $tanggal = Carbon::now();

        if($request->jumlah_pesan > $pesan->stok)
    	{
    		return redirect('pesan/'.$id);
    	}

        //cek validasi
    	$cek_pesanan = Pesanan::where('user_id', Auth::user()->id)->where('status',0)->first();
        //simpan ke database pesan
        if(empty($cek_pesanan)){
            $pesanan = new Pesanan;
	    	$pesanan->user_id = Auth::user()->id;
	    	$pesanan->tanggal = $tanggal;
	    	$pesanan->status = 0;
	    	$pesanan->jumlah_harga = 0;
            $pesanan->save();
        }
            
            
            //simpan ke database pesanan_detail
            $pesanan_new = Pesanan::where('user_id', Auth::user()->id)->where('status',0)->first();

            $cek_pesanan_detail = PesananDetail::where('barang_id', $pesan->id)->where('pesanan_id', $pesanan_new->id)->first();
    	if(empty($cek_pesanan_detail)){
            $pesanan_detail = new PesananDetail;
	    	$pesanan_detail->barang_id = $pesan->id;
	    	$pesanan_detail->pesanan_id = $pesanan_new->id;
	    	$pesanan_detail->jumlah = $request->jumlah_pesan;
	    	$pesanan_detail->jumlah_harga = $pesan->harga*$request->jumlah_pesan;
            $pesanan_detail->save();
        }else 
    	{
    		$pesanan_detail = PesananDetail::where('barang_id', $pesan->id)->where('pesanan_id', $pesanan_new->id)->first();

    		$pesanan_detail->jumlah = $pesanan_detail->jumlah+$request->jumlah_pesan;

    		//harga sekarang
    		$harga_pesanan_detail_baru = $pesan->harga*$request->jumlah_pesan;
	    	$pesanan_detail->jumlah_harga = $pesanan_detail->jumlah_harga+$harga_pesanan_detail_baru;
	    	$pesanan_detail->update();
        }
        
        //jumlah total
    	$pesanan = Pesanan::where('user_id', Auth::user()->id)->where('status',0)->first();
    	$pesanan->jumlah_harga = $pesanan->jumlah_harga+$pesan->harga*$request->jumlah_pesan;
    	$pesanan->update();
            
            alert()->success('Suskes masuk keranjang', 'sukses');
            return redirect('home');
    }

    public function check_out(){
        $pesanan = Pesanan::where('user_id', Auth::user()->id)->where('status',0)->first();
        $pesanan_details = [];
        if(!empty($pesanan )){
        $pesanan_details = PesananDetail::where('pesanan_id', $pesanan->id)->get();
        }

        return view('pesan.check_out', compact('pesanan', 'pesanan_details'));
    }

    public function delete($id)
    {
        $pesanan_detail = PesananDetail::where('id', $id)->first();

        $pesanan = Pesanan::where('id', $pesanan_detail->pesanan_id)->first();
        $pesanan->jumlah_harga = $pesanan->jumlah_harga-$pesanan_detail->jumlah_harga;
        $pesanan->update();


        $pesanan_detail->delete();

        alert()->success('Pesanan Sukses Dihapus', 'Hapus');
        return redirect('check-out');
    }
    
    public function konfirmasi()
    {
        $pesanan = Pesanan::where('user_id', Auth::user()->id)->where('status',0)->first();
        $pesanan->status = 1;
        $pesanan->update();

        
        alert()->success('Pesanan Sukses Check Out Silahkan Lanjutkan Proses Pembayaran', 'Success');
        return redirect('check-out');
    }
}
