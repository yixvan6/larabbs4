<?php

namespace App\Http\Requests\Api;

class TopicRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        switch ($this->method()) {
            case 'POST':
                return [
                    'title' => 'required|string',
                    'body' => 'required|min:3',
                    'category_id' => 'required|exists:categories,id',
                ];
                break;

            case 'PATCH':
                return [
                    'title' => 'string',
                    'body' => 'min:3',
                    'category_id' => 'exists:categories,id',
                ];
                break;
        }
    }

    public function attributes()
    {
        return [
            'title' => '标题',
            'body' => '话题内容',
            'category_id' => '分类',
        ];
    }
}