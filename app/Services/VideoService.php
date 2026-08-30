<?php

namespace App\Services;

/**
 * VideoService
 * Handles parsing, validation, and embed generation for all popular free video hosting services.
 * Supports: YouTube, Google Drive, Vimeo, Loom, Dailymotion, Internet Archive, Dropbox, and Direct MP4.
 */
class VideoService
{
    /**
     * List of known free video hosting providers with configuration and helpful hints
     */
    public static function getProviders(): array
    {
        return [
            'auto'         => [
                'name'        => 'Auto-Detect (Recommended)',
                'icon'        => 'bi-magic',
                'badge'       => 'Smart',
                'placeholder' => 'Paste any video link from YouTube, Google Drive, Vimeo, Loom, etc.',
                'guide'       => 'Automatically detects the platform and formats the secure player.',
            ],
            'youtube'      => [
                'name'        => 'YouTube (Free Unlimited)',
                'icon'        => 'bi-youtube text-danger',
                'badge'       => 'Free',
                'placeholder' => 'https://www.youtube.com/watch?v=... or https://youtu.be/...',
                'guide'       => 'Supports Public and Unlisted videos. Plays in a clean, privacy-enhanced player.',
            ],
            'google_drive' => [
                'name'        => 'Google Drive (Free 15 GB)',
                'icon'        => 'bi-google text-primary',
                'badge'       => 'Free',
                'placeholder' => 'https://drive.google.com/file/d/1A2B3C.../view?usp=sharing',
                'guide'       => 'Set file sharing to "Anyone with the link can view". It embeds directly in the classroom.',
            ],
            'vimeo'        => [
                'name'        => 'Vimeo (Free / Pro)',
                'icon'        => 'bi-vimeo text-info',
                'badge'       => 'Free / Pro',
                'placeholder' => 'https://vimeo.com/123456789',
                'guide'       => 'Clean distraction-free player without ads.',
            ],
            'loom'         => [
                'name'        => 'Loom (Free Recording & Hosting)',
                'icon'        => 'bi-camera-video text-warning',
                'badge'       => 'Free',
                'placeholder' => 'https://www.loom.com/share/...',
                'guide'       => 'Great for quick recorded lessons, presentations, and screen shares.',
            ],
            'dailymotion'  => [
                'name'        => 'Dailymotion (Free)',
                'icon'        => 'bi-play-btn text-primary',
                'badge'       => 'Free',
                'placeholder' => 'https://www.dailymotion.com/video/... or https://dai.ly/...',
                'guide'       => 'Free video hosting with custom player embed.',
            ],
            'archive'      => [
                'name'        => 'Internet Archive (100% Free / Permanent)',
                'icon'        => 'bi-bank text-secondary',
                'badge'       => 'Free',
                'placeholder' => 'https://archive.org/details/...',
                'guide'       => 'Free unlimited non-profit video archive storage.',
            ],
            'dropbox'      => [
                'name'        => 'Dropbox (Free Public Link)',
                'icon'        => 'bi-box text-primary',
                'badge'       => 'Free',
                'placeholder' => 'https://www.dropbox.com/s/.../video.mp4?dl=0',
                'guide'       => 'Direct video streaming from Dropbox share link.',
            ],
            'direct'       => [
                'name'        => 'Direct MP4 / CDN Link',
                'icon'        => 'bi-file-play text-success',
                'badge'       => 'Direct',
                'placeholder' => 'https://example.com/videos/lesson1.mp4',
                'guide'       => 'Plays via protected HTML5 video player with download restrictions enabled.',
            ],
            'external'     => [
                'name'        => 'Other / Custom Embed Code',
                'icon'        => 'bi-code-slash',
                'badge'       => 'Custom',
                'placeholder' => 'https://... or <iframe>...</iframe>',
                'guide'       => 'Embed any custom learning or video iframe.',
            ],
        ];
    }

    /**
     * Auto-detect provider from URL
     */
    public static function detectProvider(string $url): string
    {
        $url = trim($url);
        if (empty($url)) {
            return 'youtube';
        }

        if (preg_match('/(youtube\.com|youtu\.be)/i', $url)) {
            return 'youtube';
        }
        if (preg_match('/(drive\.google\.com|docs\.google\.com)/i', $url)) {
            return 'google_drive';
        }
        if (preg_match('/vimeo\.com/i', $url)) {
            return 'vimeo';
        }
        if (preg_match('/loom\.com/i', $url)) {
            return 'loom';
        }
        if (preg_match('/(dailymotion\.com|dai\.ly)/i', $url)) {
            return 'dailymotion';
        }
        if (preg_match('/archive\.org/i', $url)) {
            return 'archive';
        }
        if (preg_match('/dropbox\.com/i', $url)) {
            return 'dropbox';
        }
        if (preg_match('/\.(mp4|webm|ogg|m4v)(\?.*)?$/i', $url)) {
            return 'direct';
        }

        return 'external';
    }

    /**
     * Generate secure embed HTML / Player for the classroom
     */
    public static function renderEmbed(string $url, ?string $provider = null, string $title = 'Lesson Video'): string
    {
        $url = trim($url);
        if (empty($url)) {
            return '<div class="card p-5 text-center bg-light border-0 rounded-4">
                        <i class="bi bi-play-circle display-4 text-muted mb-2"></i>
                        <h5 class="font-heading mb-1">No Video Available</h5>
                        <p class="text-muted small mb-0">The instructor has not uploaded a video for this lesson yet.</p>
                    </div>';
        }

        if (empty($provider) || $provider === 'auto') {
            $provider = self::detectProvider($url);
        }

        switch ($provider) {
            case 'youtube':
                $videoId = self::extractYouTubeId($url);
                $embedUrl = $videoId
                    ? "https://www.youtube-nocookie.com/embed/{$videoId}?rel=0&modestbranding=1&enablejsapi=1&playsinline=1"
                    : $url;
                return '<div class="video-container-responsive shadow-sm rounded-4 overflow-hidden position-relative" style="background:#000;">
                            <iframe id="classroomYoutubePlayer"
                                    src="' . htmlspecialchars($embedUrl) . '" 
                                    title="' . htmlspecialchars($title) . '" 
                                    frameborder="0" 
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                                    allowfullscreen
                                    style="position:absolute;top:0;left:0;width:100%;height:100%;border:0;"></iframe>
                        </div>';

            case 'google_drive':
                $fileId = self::extractGoogleDriveId($url);
                $embedUrl = $fileId ? "https://drive.google.com/file/d/{$fileId}/preview" : $url;
                return '<div class="video-container-responsive shadow-sm rounded-4 overflow-hidden position-relative" style="background:#000;">
                            <iframe id="classroomDrivePlayer"
                                    src="' . htmlspecialchars($embedUrl) . '" 
                                    title="' . htmlspecialchars($title) . '" 
                                    frameborder="0" 
                                    allow="autoplay"
                                    allowfullscreen
                                    style="position:absolute;top:0;left:0;width:100%;height:100%;border:0;"></iframe>
                        </div>';

            case 'vimeo':
                $videoId = self::extractVimeoId($url);
                $embedUrl = $videoId
                    ? "https://player.vimeo.com/video/{$videoId}?dnt=1&title=0&byline=0&portrait=0"
                    : $url;
                return '<div class="video-container-responsive shadow-sm rounded-4 overflow-hidden position-relative" style="background:#000;">
                            <iframe id="classroomVimeoPlayer"
                                    src="' . htmlspecialchars($embedUrl) . '" 
                                    title="' . htmlspecialchars($title) . '" 
                                    frameborder="0" 
                                    allow="autoplay; fullscreen; picture-in-picture" 
                                    allowfullscreen
                                    style="position:absolute;top:0;left:0;width:100%;height:100%;border:0;"></iframe>
                        </div>';

            case 'loom':
                $loomId = self::extractLoomId($url);
                $embedUrl = $loomId
                    ? "https://www.loom.com/embed/{$loomId}?hide_owner=true&hide_share=true&hide_title=true&hideEmbedTopBar=true"
                    : $url;
                return '<div class="video-container-responsive shadow-sm rounded-4 overflow-hidden position-relative" style="background:#000;">
                            <iframe src="' . htmlspecialchars($embedUrl) . '" 
                                    title="' . htmlspecialchars($title) . '" 
                                    frameborder="0" 
                                    webkitallowfullscreen mozallowfullscreen allowfullscreen
                                    style="position:absolute;top:0;left:0;width:100%;height:100%;border:0;"></iframe>
                        </div>';

            case 'dailymotion':
                $dmId = self::extractDailymotionId($url);
                $embedUrl = $dmId
                    ? "https://www.dailymotion.com/embed/video/{$dmId}?ui-highlight=C59B27&ui-logo=false"
                    : $url;
                return '<div class="video-container-responsive shadow-sm rounded-4 overflow-hidden position-relative" style="background:#000;">
                            <iframe src="' . htmlspecialchars($embedUrl) . '" 
                                    title="' . htmlspecialchars($title) . '" 
                                    frameborder="0" 
                                    allow="autoplay; fullscreen; picture-in-picture" 
                                    allowfullscreen
                                    style="position:absolute;top:0;left:0;width:100%;height:100%;border:0;"></iframe>
                        </div>';

            case 'archive':
                $archId = self::extractArchiveId($url);
                $embedUrl = $archId
                    ? "https://archive.org/embed/{$archId}"
                    : $url;
                return '<div class="video-container-responsive shadow-sm rounded-4 overflow-hidden position-relative" style="background:#000;">
                            <iframe src="' . htmlspecialchars($embedUrl) . '" 
                                    title="' . htmlspecialchars($title) . '" 
                                    frameborder="0" 
                                    allowfullscreen
                                    style="position:absolute;top:0;left:0;width:100%;height:100%;border:0;"></iframe>
                        </div>';

            case 'dropbox':
                $rawUrl = preg_replace('/(\?|\&)dl=0/i', '', $url);
                $rawUrl .= (strpos($rawUrl, '?') === false ? '?' : '&') . 'raw=1';
                return '<div class="video-player-wrapper shadow-sm rounded-4 overflow-hidden bg-black p-0 position-relative">
                            <video id="classroomHtml5Video" controls controlsList="nodownload" oncontextmenu="return false;" style="width:100%;max-height:560px;display:block;margin:0 auto;background:#000;">
                                <source src="' . htmlspecialchars($rawUrl) . '" type="video/mp4">
                                Your browser does not support HTML5 video.
                            </video>
                        </div>';

            case 'direct':
                return '<div class="video-player-wrapper shadow-sm rounded-4 overflow-hidden bg-black p-0 position-relative">
                            <video id="classroomHtml5Video" controls controlsList="nodownload" oncontextmenu="return false;" style="width:100%;max-height:560px;display:block;margin:0 auto;background:#000;">
                                <source src="' . htmlspecialchars($url) . '" type="video/mp4">
                                Your browser does not support HTML5 video.
                            </video>
                        </div>';

            default:
                if (stripos($url, '<iframe') !== false) {
                    return '<div class="video-container-responsive shadow-sm rounded-4 overflow-hidden position-relative" style="background:#000;">' . $url . '</div>';
                }
                return '<div class="video-container-responsive shadow-sm rounded-4 overflow-hidden position-relative" style="background:#000;">
                            <iframe src="' . htmlspecialchars($url) . '" 
                                    title="' . htmlspecialchars($title) . '" 
                                    frameborder="0" 
                                    allowfullscreen
                                    style="position:absolute;top:0;left:0;width:100%;height:100%;border:0;"></iframe>
                        </div>';
        }
    }

    public static function extractYouTubeId(string $url): ?string
    {
        if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?|shorts)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/i', $url, $m)) {
            return $m[1];
        }
        return null;
    }

    public static function extractGoogleDriveId(string $url): ?string
    {
        if (preg_match('/\/file\/d\/([a-zA-Z0-9_-]+)/i', $url, $m)) {
            return $m[1];
        }
        if (preg_match('/[?&]id=([a-zA-Z0-9_-]+)/i', $url, $m)) {
            return $m[1];
        }
        return null;
    }

    public static function extractVimeoId(string $url): ?string
    {
        if (preg_match('/vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|video\/|)(\d+)/i', $url, $m)) {
            return $m[2] ?? $m[1];
        }
        return null;
    }

    public static function extractLoomId(string $url): ?string
    {
        if (preg_match('/loom\.com\/(?:share|embed)\/([a-zA-Z0-9_-]+)/i', $url, $m)) {
            return $m[1];
        }
        return null;
    }

    public static function extractDailymotionId(string $url): ?string
    {
        if (preg_match('/dailymotion\.com\/video\/([a-zA-Z0-9]+)/i', $url, $m)) {
            return $m[1];
        }
        if (preg_match('/dai\.ly\/([a-zA-Z0-9]+)/i', $url, $m)) {
            return $m[1];
        }
        return null;
    }

    public static function extractArchiveId(string $url): ?string
    {
        if (preg_match('/archive\.org\/(?:details|embed)\/([a-zA-Z0-9_.-]+)/i', $url, $m)) {
            return $m[1];
        }
        return null;
    }
}
