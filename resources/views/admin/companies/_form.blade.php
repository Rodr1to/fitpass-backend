@csrf
<div class="space-y-4">
    <div>
        <x-input-label for="name" :value="__('Company Name')" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $company->name ?? '')" required autofocus />
        <x-input-error class="mt-2" :messages="$errors->get('name')" />
    </div>
    <div>
        <x-input-label for="contact_email" :value="__('Contact Email')" />
        <x-text-input id="contact_email" name="contact_email" type="email" class="mt-1 block w-full" :value="old('contact_email', $company->contact_email ?? '')" required />
        <x-input-error class="mt-2" :messages="$errors->get('contact_email')" />
    </div>
    <div>
        <x-input-label for="contact_phone" :value="__('Contact Phone')" />
        <x-text-input id="contact_phone" name="contact_phone" type="text" class="mt-1 block w-full" :value="old('contact_phone', $company->contact_phone ?? '')" />
        <x-input-error class="mt-2" :messages="$errors->get('contact_phone')" />
    </div>
    <div>
        <x-input-label for="address" :value="__('Address')" />
        <textarea id="address" name="address" class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">{{ old('address', $company->address ?? '') }}</textarea>
        <x-input-error class="mt-2" :messages="$errors->get('address')" />
    </div>
</div>
<div class="flex items-center justify-end mt-6">
    <a href="{{ route('admin.companies.index') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white mr-4">
        Cancel
    </a>
    <x-primary-button>
        {{ $buttonText ?? 'Save' }}
    </x-primary-button>
</div>