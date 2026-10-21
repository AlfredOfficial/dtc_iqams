<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg"><section class="max-w-xl" x-data="{ preview: @js($user->avatar_url), objectUrl: null }"><header><h2 class="text-lg font-medium text-gray-900">Profile Picture</h2><p class="mt-1 text-sm text-gray-600">JPG, JPEG, PNG, or WebP up to 2 MB.</p></header><form method="POST" action="{{ route('profile.photo') }}" enctype="multipart/form-data" class="mt-6">@csrf @method('PUT')<div class="flex flex-col items-center gap-5 sm:flex-row"><div class="h-24 w-24 shrink-0 overflow-hidden rounded-full bg-indigo-100 text-indigo-700"><img x-show="preview" :src="preview" class="h-full w-full object-cover" alt="Profile photo preview"><div x-show="!preview" class="grid h-full place-items-center text-3xl font-semibold">{{ strtoupper(substr($user->name, 0, 1)) }}</div></div><div><label for="avatar" class="inline-flex cursor-pointer rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">{{ $user->avatar_path ? 'Replace Photo' : 'Upload Photo' }}</label><input id="avatar" name="avatar" type="file" accept="image/jpeg,image/png,image/webp" required class="sr-only" @change="if (objectUrl) URL.revokeObjectURL(objectUrl); objectUrl = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null; preview = objectUrl || @js($user->avatar_url)"><p class="mt-2 text-xs text-gray-500">A preview appears before you save.</p>@error('avatar')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div></div><div class="mt-6"><x-primary-button>Save Photo</x-primary-button></div></form>@if ($user->avatar_path)<form method="POST" action="{{ route('profile.photo.destroy') }}" class="mt-3">@csrf @method('DELETE')<button type="submit" class="rounded-md px-4 py-2 text-sm font-semibold text-red-600 hover:bg-red-50">Remove Photo</button></form>@endif</section></div>
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
