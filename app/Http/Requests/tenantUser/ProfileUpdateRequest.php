<?php

namespace App\Http\Requests\tenantUser;

use App\Models\TenantUserModel\RegularUser;
use App\Support\InputRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => InputRules::personName(255, true),
            'email' => [
                'required',
                'string',
                'lowercase',
                'email:rfc,dns',
                'max:254',
                Rule::unique(RegularUser::class)->ignore($this->user()->id),
            ],
        ];
    }
}
