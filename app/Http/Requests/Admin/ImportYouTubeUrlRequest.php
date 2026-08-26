<?php

namespace App\Http\Requests\Admin;

use App\Services\YouTubeService;
use Illuminate\Foundation\Http\FormRequest;

class ImportYouTubeUrlRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'url' => ['required', 'string', 'url', function ($attribute, $value, $fail) {
                $videoId = (new YouTubeService)->extractVideoId($value);

                if ($videoId === null) {
                    $fail('The URL must be a valid YouTube video URL.');
                }
            }],
        ];
    }
}
