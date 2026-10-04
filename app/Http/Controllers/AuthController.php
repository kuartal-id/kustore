<?php
namespace App\Http\Controllers;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
class AuthController extends Controller {
 public function showLogin(){return view('auth.login');}
 public function showRegister(){return view('auth.register');}
 public function register(Request $request){
  $data=$request->validate(['name'=>'required|string|max:100','email'=>'required|email|max:255|unique:users','password'=>'required|string|min:8|confirmed','username'=>'required|string|max:40|alpha_dash|unique:stores,username']);
  $user=User::create(['name'=>$data['name'],'email'=>$data['email'],'password'=>Hash::make($data['password'])]);
  Store::create(['user_id'=>$user->id,'username'=>strtolower($data['username']),'display_name'=>$data['name'],'bio'=>'','theme'=>'light','published'=>true]);
  Auth::login($user); $request->session()->regenerate();
  return redirect()->route('dashboard.index');
 }
 public function login(Request $request){
  $data=$request->validate(['email'=>'required|email','password'=>'required|string']);
  if(!Auth::attempt($data,$request->boolean('remember'))){return back()->withErrors(['email'=>'Those credentials do not match our records.'])->onlyInput('email');}
  $request->session()->regenerate(); return redirect()->intended(route('dashboard.index'));
 }
 public function logout(Request $request){Auth::logout();$request->session()->invalidate();$request->session()->regenerateToken();return redirect('/');}
}