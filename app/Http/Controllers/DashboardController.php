<?php
namespace App\Http\Controllers;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
class DashboardController extends Controller {
 private function store(Request $r){return $r->user()->store()->firstOrFail();}
 public function index(Request $r){$store=$this->store($r);$stats=['products'=>$store->products()->count(),'links'=>$store->links()->count(),'orders'=>$store->orders()->count(),'revenue'=>$store->orders()->where('status','paid')->sum('amount')];return view('dashboard.index',compact('store','stats'));}
 public function editStore(Request $r){$store=$this->store($r);return view('dashboard.store',compact('store'));}
 public function updateStore(Request $r){$store=$this->store($r);$data=$r->validate(['display_name'=>'required|string|max:100','username'=>'required|string|max:40|alpha_dash|unique:stores,username,'.$store->id,'bio'=>'nullable|string|max:500','avatar_url'=>'nullable|url|max:1000','theme'=>'required|in:light,dark']);$store->update($data);return back()->with('success','Store updated.');}
 public function addLink(Request $r){$store=$this->store($r);$data=$r->validate(['title'=>'required|string|max:100','url'=>'required|url|max:1000']);$store->links()->create($data+['position'=>$store->links()->max('position')+1,'active'=>true]);return back()->with('success','Link added.');}
 public function deleteLink(Request $r,$link){$this->store($r)->links()->findOrFail($link)->delete();return back();}
 public function products(Request $r){$store=$this->store($r);$products=$store->products()->get();return view('dashboard.products',compact('store','products'));}
 public function createProduct(Request $r){$store=$this->store($r);$data=$r->validate(['name'=>'required|string|max:160','description'=>'nullable|string|max:5000','type'=>'required|in:physical,digital,service','price'=>'required|numeric|min:0','currency'=>'required|string|size:3','image_url'=>'nullable|url|max:1000','inventory'=>'nullable|integer|min:0']);$data['slug']=Str::slug($data['name']).'-'.Str::lower(Str::random(5));$data['active']=true;$store->products()->create($data);return back()->with('success','Product created.');}
 public function deleteProduct(Request $r,$product){$this->store($r)->products()->findOrFail($product)->delete();return back();}
 public function orders(Request $r){$store=$this->store($r);$orders=$store->orders()->latest()->with('items')->get();return view('dashboard.orders',compact('store','orders'));}
}