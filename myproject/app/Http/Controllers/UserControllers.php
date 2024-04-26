<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
//nhớ import 
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use App\Models\Favorite;
use App\Models\Post;

class UserControllers extends Controller
{
    public function show($user_id){
        $user = User::find($user_id);
        return view('show_user', compact('user'));
    }
    public function edit($user_id){
        $user = User::find($user_id);
        if (!$user) {
            return back()->with("error", "User không tồn tại!");
        }
        return view('edit_user', compact('user'));
    }
    public function update(Request $request, $user_id){
        $user = User::find($user_id);
        if (!$user) {
            return back()->with("error", "User không tồn tại!");
        }
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'image' => 'nullable|image|max:2048',
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8',
        ]);
        //xử lý cập nhật
        $user->name = $validatedData['name'];
        $user->phone = $validatedData['phone'];
        $user->email = $validatedData['email'];
        $user->password = Hash::make($validatedData['password']);
        if ($request->hasFile('image')) { 
            $image = $request->file('image');
            $filename = 'user'. '-'.time().rand(1,999). '.' .$image->extension();
            $image->move(public_path('images/users'), $filename);
            $user->image = $filename;
        }
        $user->save();
        // return redirect()->route('index')->with("success", "Cập nhật user thành công");
        return redirect()->route('login');
    }

    public function index(){
        $users = DB::table('users')->paginate(4);
        return view('index', compact('users'));
    }
    //
    public function delete($user_id){
        $user = User::find($user_id);
        // kiểm tra nếu không tồn tại
        if (!$user) {
            return back()->with("error", "User không tồn tại!");
        }
        $user->delete();
        return redirect()->route('index');
    }
    // authenticate
    public function login(){
        return view('auth/login');
    }
    public function customLogin(Request $request){
        $validatedData = $request->validate([
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8',
        ]);
        $credentials = [
            'email' => $validatedData['email'],
            'password' => $validatedData['password'],
        ];
        if (Auth::attempt($credentials)) {
            return redirect()->route('index');
        } else {
            return back()->with("error", "Không mật khẩu không chính xác!");
        }
    }
    public function register(){
        return view('auth/register'); //hàm này trả về giao diện B1
    }

    public function customRegister(Request $request){
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'image' => 'nullable|image|max:2048',
            'email' => 'required|string|email|unique:users|max:255',
            'password' => 'required|string|min:8',
        ]);
        $user = new User([
            'name' => $validatedData['name'],
            'phone' => $validatedData['phone'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']), 
        ]);

        //gán image
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = 'user'. '-'.time().rand(1,999). '.' .$image->extension();
            $image->move(public_path('images/users'), $filename);
            $user->image = $filename;
        }
        $user->save();
        return redirect('login');
    }

    public function logout(){
        Auth::logout();
        return redirect()->route('login');
    }
        
}
