@include('admin.courses._form_details', [
    'course' => $course,
    'categories' => $categories,
    'difficultyLevels' => $difficultyLevels,
    'tags' => $tags,
    'audiences' => $audiences,
])
@include('admin.courses._form_marketing', ['course' => $course])
@include('admin.seo._form', ['seoMeta' => $seoMeta ?? null, 'mediaAssets' => $mediaAssets])
@include('admin.courses._form_content', ['course' => $course])
