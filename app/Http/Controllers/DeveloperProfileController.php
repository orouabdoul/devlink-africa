<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateDeveloperProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class DeveloperProfileController extends Controller
{
    public function update(UpdateDeveloperProfileRequest $request): RedirectResponse
    {
        $profile = $request->user()->profile()->firstOrCreate();
        $data = collect($request->validated())->except('avatar')->all();

        if ($request->hasFile('avatar')) {
            if ($profile->avatar) {
                Storage::disk('public')->delete($profile->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        if ($request->boolean('remove_avatar') && $profile->avatar) {
            Storage::disk('public')->delete($profile->avatar);
            $data['avatar'] = null;
        }

        $profile->update($data);

        return back()->with('success', __('Profil développeur mis à jour.'));
    }
}
