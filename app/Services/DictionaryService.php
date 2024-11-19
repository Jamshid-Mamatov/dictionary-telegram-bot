<?php

namespace App\Services;

use App\Models\Dictionary;

class DictionaryService
{
    public function searchWord($word)
    {
        return Dictionary::where('word', strtolower($word))
            ->select('definition')
            ->get();
    }

    public function addWord($word, $definition, $language = 'en')
    {
        return Dictionary::create([
            'word' => strtolower($word),
            'definition' => $definition,
            'language' => $language,
        ]);
    }
}
