<?php

namespace FastDog\Config\Request;

use App\Core\Module\Components;
use FastDog\User\Models\User;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Добавление публичного модуля
 *
 * @package FastDog\Config\Request
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class AddSiteModule extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if (!\Auth::guest()) {
            $user = \Auth::getUser();
            if ($user->type == User::USER_TYPE_ADMIN) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required',
        ];
    }

    /**
     * @return array
     */
    public function messages()
    {
        return [
            'name.required' => 'Поле "Название" обязательно для заполнения.',
        ];
    }

    /**
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function getValidatorInstance()
    {
        $validator = parent::getValidatorInstance();
        $validator->after(function () use ($validator) {
            $input = $this->all();
            if (!isset($input['id']) || ($input['id'] == null)) {
                $item = Components::where(Components::NAME, $input[Components::NAME])->first();
                if ($item) {
                    $validator->errors()->add('name', 'Модуль с указанным именем уже существует.');
                }
            }
        });

        return $validator;
    }
}
