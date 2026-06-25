<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\Field;

class CoverImageUploader extends Field
{
    protected string $view = 'filament.forms.components.cover-image-uploader';

    protected string $signedUrlEndpoint = '/internal/r2-signed-url';

    public function signedUrlEndpoint(string $endpoint): static
    {
        $this->signedUrlEndpoint = $endpoint;

        return $this;
    }

    public function getSignedUrlEndpoint(): string
    {
        return $this->signedUrlEndpoint;
    }
}
