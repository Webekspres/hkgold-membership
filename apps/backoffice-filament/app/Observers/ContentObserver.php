<?php

declare(strict_types=1);

namespace App\Observers;

use App\Filament\Resources\Contents\Support\ContentFormSupport;
use App\Models\Content;
use App\Models\ContentCoverImage;

class ContentObserver
{
    public function deleting(Content $content): void
    {
        $content->contentCoverImages()
            ->with('media')
            ->get()
            ->each(function (ContentCoverImage $coverImage): void {
                $media = $coverImage->media;

                if ($media === null) {
                    return;
                }

                ContentFormSupport::deleteMediaFileFromStorage($media);

                if (! ContentFormSupport::isMediaInUse($media->id, $content->id)) {
                    $media->delete();
                }
            });
    }
}
