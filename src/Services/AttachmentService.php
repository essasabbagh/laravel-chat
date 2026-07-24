<?php

namespace Essasabbagh\LaravelChat\Services;

use Essasabbagh\LaravelChat\Models\Attachment;
use Essasabbagh\LaravelChat\Models\Message;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

class AttachmentService
{
    public function upload(Message $message, UploadedFile $file, string $type): Attachment
    {
        $this->validate($file, $type);

        $path = $file->store('attachments', config('chat.storage.disk', 'local'));

        $metadata = $this->extractMetadata($file, $type);

        return $message->attachments()->create([
            'type' => $type,
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'metadata' => $metadata,
        ]);
    }

    public function uploadFromUrl(Message $message, string $url): Attachment
    {
        $type = $this->resolveUrlType($url);

        $metadata = $this->scrapeUrlMetadata($url, $type);

        return $message->attachments()->create([
            'type' => $type,
            'path' => null,
            'mime_type' => null,
            'size' => null,
            'metadata' => $metadata,
        ]);
    }

    private function validate(UploadedFile $file, string $type): void
    {
        $maxSize = config('chat.storage.max_file_size', 10240);
        $allowedMimes = config('chat.storage.allowed_mimes', []);

        if ($file->getSize() > $maxSize * 1024) {
            throw new \InvalidArgumentException("File exceeds maximum size of {$maxSize}KB");
        }

        $extension = strtolower($file->getClientOriginalExtension());
        if (! empty($allowedMimes) && ! in_array($extension, $allowedMimes)) {
            throw new \InvalidArgumentException("File type {$extension} is not allowed");
        }
    }

    private function extractMetadata(UploadedFile $file, string $type): array
    {
        $metadata = [
            'original_name' => $file->getClientOriginalName(),
            'extension' => $file->getClientOriginalExtension(),
        ];

        if ($type === 'image') {
            $dimensions = @getimagesize($file->getPathname());
            if ($dimensions) {
                $metadata['width'] = $dimensions[0];
                $metadata['height'] = $dimensions[1];
            }
        }

        if ($type === 'voice') {
            $metadata['duration'] = null;
        }

        return $metadata;
    }

    private function resolveUrlType(string $url): string
    {
        if (str_contains($url, 'youtube.com') || str_contains($url, 'youtu.be')) {
            return 'video';
        }

        return 'link';
    }

    private function scrapeUrlMetadata(string $url, string $type): array
    {
        $metadata = ['url' => $url];

        if ($type === 'video') {
            $videoId = $this->extractYoutubeId($url);
            if ($videoId) {
                $metadata['video_id'] = $videoId;
                $response = Http::get('https://www.youtube.com/oembed', [
                    'url' => $url,
                    'format' => 'json',
                ]);
                if ($response->successful()) {
                    $metadata = array_merge($metadata, $response->json());
                }
            }
        }

        if ($type === 'link') {
            $response = Http::get($url);
            if ($response->successful()) {
                $body = $response->body();
                $metadata['title'] = $this->extractOgTag($body, 'og:title');
                $metadata['description'] = $this->extractOgTag($body, 'og:description');
                $metadata['image'] = $this->extractOgTag($body, 'og:image');
            }
        }

        return $metadata;
    }

    private function extractYoutubeId(string $url): ?string
    {
        preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $url, $matches);

        return $matches[1] ?? null;
    }

    private function extractOgTag(string $html, string $property): ?string
    {
        preg_match('/<meta\s+property="'.preg_quote($property, '/').'"\s+content="([^"]+)"\s*\/?>/i', $html, $matches);

        return $matches[1] ?? null;
    }
}
