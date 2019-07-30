<?php

namespace FastDog\Config\Http\Request;


use FastDog\Core\Models\Domain;
use FastDog\User\Models\User;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Добавление\обновление домена
 *
 * @package FastDog\Config\Request
 * @version 0.2.0
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class AddHelp extends FormRequest
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
            'text' => 'required',
            'alias' => 'required',
        ];
    }

    /**
     * @return array
     */
    public function messages()
    {
        return [
            'name.required' => trans('config::requests.add_help.name_required'),
            'text.required' => trans('config::requests.add_help.html_required'),
            'alias.required' => trans('config::requests.add_help.alias_required'),
        ];
    }
}
