<?php 
// Decode the JSON file and store it as an array
$videoDataArray = json_decode(file_get_contents(__DIR__ . '/youtube_videos.json'), true);

// Initialize variables
$showModal = false;
$videourl = '';
$videothumbnailUrl = '';
$videotitle = 'No Title';
$uploadDate = 'No Date Provided';

// Default thumbnail URL
$defaultThumbnailUrl = 'https://www.biteanddiet.in/images/youtube-default-thumbnail.webp'; 


if (is_array($videoDataArray) && count($videoDataArray) > 0) {
    $randomIndex = rand(0, count($videoDataArray) - 1);
    $videoData = $videoDataArray[$randomIndex];

    if (!empty($videoData['videoId'])) {
        $videoId = $videoData['videoId'];
        $videotitle = $videoData['title'] ?? 'No Title';
        $uploadDate = $videoData['publishedAt'] ?? 'No Date Provided';

        $slug = $videoData['slug'];
        $videoPageUrl = "/video/{$slug}";

        $videourl = "https://www.youtube.com/embed/{$videoId}?rel=0";
        $videothumbnailUrl = !empty($videoData['thumbnail']) 
            ? "https://i.ytimg.com/vi/{$videoId}/maxresdefault.jpg"
            : $defaultThumbnailUrl;

        $showModal = true;
    }
}
?>

<!-- Latest popVideo Modal -->
<?php if ($showModal): ?>
<div class="fade modal firstmodal" id="latestVideoModal" aria-labelledby="dialogLabel" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="mt-3 px-4">
                <button class="close" data-dismiss="modal" type="button">×</button>
                <h4 class="modal-title">Our Latest Video</h4>
            </div>
            <div class="modal-body">
                <div id="videoThumbnailContainer"></div>
                <h6 class="mt-3 px-4 text-center title">
                    Transform Your Body And Mind With 
                    <span class="ttm-textcolor-skincolor">
                        <a href="https://www.youtube.com/channel/UCwl1Lbkl0PhwYm8j3j8fo1A" target="_blank">Nutrition</a>
                    </span>
                </h6>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
$(document).ready(function() {
    setTimeout(function() {
        if (document.cookie.indexOf("visited=true") === -1) {
            <?php if ($showModal): ?>
            var img = new Image();
            img.src = '<?php echo htmlspecialchars($videothumbnailUrl, ENT_QUOTES, "UTF-8"); ?>';

            img.onload = function() {
                $("#latestVideoModal").modal("show");

                var expirationDate = new Date(new Date().valueOf() + 3600000); // 1 hour
                document.cookie = "visited=true;expires=" + expirationDate.toUTCString();

                $('#videoThumbnailContainer').html(`
                    <a href="<?php echo htmlspecialchars($videoPageUrl, ENT_QUOTES, 'UTF-8'); ?>" class="video-thumbnail d-pos-relative">
                        <img src="<?php echo htmlspecialchars($videothumbnailUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($videotitle, ENT_QUOTES, 'UTF-8'); ?>" class="w-100-d-block">
                        <span class="latest-popupmodal">▶</span>
                    </a>
                `);
            };
            <?php endif; ?>
        }
    }, 35000);
});
</script>