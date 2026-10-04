<?php
namespace App\Http\Controllers;
use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
class StoreController extends Controller {
 public function landing(){return view('home');}
 public function show(string $username){
  $store=Store::where('username',strtolower($username))->where('published',true)->with(['links','products'])->firstOrFail();
  return view('store.show',compact('store'));
 }
 public function product(string $username,string $product){
  $store=Store::where('username',strtolower($username))->where('published',true)->firstOrFail();
  $item=$store->products()->where('slug',$product)->firstOrFail();
  return view('store.product',compact('store','item'));
 }
 public function checkout(Request $request,string $username,string $product){
  $store=Store::where('username',strtolower($username))->where('published',true)->firstOrFail();
  $item=$store->products()->where('slug',$product)->firstOrFail();
  $data=$request->validate(['customer_name'=>'required|string|max:120','customer_email'=>'required|email|max:255','quantity'=>'required|integer|min:1|max:99']);
  $qty=$data['quantity'];
  if($item->inventory!==null && $item->inventory<$qty){return back()->withErrors(['quantity'=>'Not enough inventory available.']);}
  $order=Order::create(['store_id'=>$store->id,'customer_name'=>$data['customer_name'],'customer_email'=>$data['customer_email'],'amount'=>$item->price*$qty,'currency'=>$item->currency,'status'=>'pending','payment_provider'=>config('kustore.payment_provider','manual')]);
  $order->items()->create(['product_id'=>$item->id,'name'=>$item->name,'quantity'=>$qty,'unit_price'=>$item->price]);
  if($item->inventory!==null){$item->decrement('inventory',$qty);}
  return view('store.checkout',compact('store','item','order'));
 }
}