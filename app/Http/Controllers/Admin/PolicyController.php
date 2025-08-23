<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Policy;
use Illuminate\Http\Request;

class PolicyController extends Controller
{
    /**
     * Display the specified resource.
     *
     * @param  string  $slug
     * @return \Illuminate\Http\Response
     */
    public function edit($slug)
    {
        $policy = Policy::where('slug', $slug)->first();
        if($policy) {
            $pageTitle = 'Edit '. $policy->title . ' content';

            return view('admin-panel.policy.edit', compact('pageTitle' , 'policy'));
        }
        return abort(404);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $policy = Policy::findOrFail($id);

        $data = $request->validate([
            'title' => 'bail|required',
            'heading_one' => 'bail|required',
            'content_one' => 'bail|required',
            'heading_two' => 'bail|nullable',
            'content_two' => 'bail|nullable',
        ]);

        if($policy->update($data)) {
            toastr()->success('Staff has been update successfully');
        }else {
            toastr()->error('Failed to update staff');
        }

        return back();

    }


}
