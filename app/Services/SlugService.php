<?php

namespace App\Services;

use App\Models\Article;
use Illuminate\Support\Str;

class SlugService
{

    public function generateUnique($title, $ignoreId)
    {

        $baseSlug = Str::slug($title);
        $slug = $baseSlug;
        $counter = 1;

        while (
            Article::when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists()
        ) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
