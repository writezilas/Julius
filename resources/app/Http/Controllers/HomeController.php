<?php

namespace App\Http\Controllers;

use App\Models\Policy;
use App\Models\Trade;
use App\Models\User;
use App\Models\UserShare;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class HomeController extends Controller
{

//    /**
//     * Show the application dashboard.
//     *
//     * @return \Illuminate\Contracts\Support\Renderable
//     */
//    public function index(Request $request)
//    {
//        if(auth()->user()->role_id != 2){
////            if (view()->exists($request->path())) {
////                return view($request->path());
////            }
////            return abort(404);
//            return view('index');
//        }else{
//
//            if (view()->exists('user-panel.'.$request->path())) {
//                return view('user-panel.'.$request->path());
//            }
//            return abort(404);
//        }
//        return abort(404);
//    }

public function index()
{
    $trades = Trade::with('userShares')->whereStatus(1)->get();
    return view('index', compact('trades'));
}

    public function root()
    {
        return view('user-panel.dashboard');
    }

    /*Language Translation*/
    public function lang($locale)
    {
        if ($locale) {
            App::setLocale($locale);
            Session::put('lang', $locale);
            Session::save();
            return redirect()->back()->with('locale', $locale);
        } else {
            return redirect()->back();
        }
    }

    public function updateProfile(Request $request, $id)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:1024'],
        ]);

        $user = User::find($id);
        $user->name = $request->get('name');

//        if ($request->file('avatar')) {
//            $avatar = $request->file('avatar');
//            $avatarName = time() . '.' . $avatar->getClientOriginalExtension();
//            $avatarPath = public_path('/images/');
//            $avatar->move($avatarPath, $avatarName);
//            $user->avatar =  $avatarName;
//        }

        if ($request->file('avatar')) {
            $user->avatar = $request->avatar->store('uploads/avatar', 'public');
        }

        if ($user->update()) {
            toastr()->success('User profile details Updated successfully!');
        } else {
            toastr()->error('Failed to update user profile');
        }
        return back();
    }

    public function updatePassword(Request $request, $id)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        if (!(Hash::check($request->get('current_password'), Auth::user()->password))) {
            toastr()->info('Your Current password does not matches with the password you provided. Please try again.');
        } else {
            $user = User::find($id);
            $user->password = Hash::make($request->get('password'));
            $user->update();
            if ($user) {
                toastr()->success('Your password has been updated successfully');
            } else {
                toastr()->error('Failed to update password. please try again');
            }
        }
        return back();
    }

    public function profile() {
        if(\auth()->user()->role_id === 2) {
            return view('user-panel.profile');
        }else {
            $pageTitle = 'Admin profile';
            return view('admin-panel.settings.profile', compact('pageTitle'));
        }

    }

    public function referrals()
    {
        $pageTitle = __('translation.refferals');

        return view('user-panel.refferals', compact('pageTitle'));
    }
    public function boughtShares()
    {
        $pageTitle = __('translation.boughtshares');

        $boughtShares = UserShare::where('user_id', \auth()->user()->id)->orderBy('id','DESC')->get();

        return view('user-panel.bought-shares', compact('pageTitle', 'boughtShares'));
    }

    public function soldShares()
    {
        $pageTitle = __('translation.soldshares');

        $soldShares = UserShare::where('user_id', \auth()->user()->id)->whereStatus('completed')->where('start_date', '!=', '')->orderBy('id', 'desc')->get();
        return view('user-panel.sold-shares', compact('pageTitle', 'soldShares'));
    }
    public function support()
    {
        $pageTitle = __('translation.support');

        return view('user-panel.support', compact('pageTitle'));
    }

    public function howItWorksPage()
    {
        $pageTitle = 'How it works';

        $policy = Policy::where('slug', 'how-it-work')->first();

        return view('user-panel.how-it-works', compact('pageTitle', 'policy'));

    }
    public function privacyPolicy()
    {
        $policy = Policy::where('slug', 'privacy-policy')->first();
        $pageTitle = $policy->title;

        return view('user-panel.privacy-policy', compact('pageTitle', 'policy'));

    }
    public function termsAndConditions()
    {
        $policy = Policy::where('slug', 'terms-and-conditions')->first();
        $pageTitle = $policy->title;

        return view('user-panel.terms-conditions', compact('pageTitle', 'policy'));

    }
    public function confidentialityPolicy()
    {

        $policy = Policy::where('slug', 'confidentiality-policy')->first();
        $pageTitle = $policy->title;

        return view('user-panel.confidentiality-policy', compact('pageTitle', 'policy'));

    }

}
