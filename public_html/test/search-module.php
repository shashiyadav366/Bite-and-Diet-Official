<?php
require_once __DIR__ . '/../json_db.php';

    $metadata = jd_read('metadata');
    $metadataJson = json_encode($metadata);
?>

<!-- Your JavaScript code -->
<script >
    var metadata = {};
    var blogData = [];
    var videoData = [];

    // Fetch metadata from PHP
    <?php
    require_once __DIR__ . '/../json_db.php';

        // Read metadata from JSON
        $metadata = jd_read('metadata');

        // Fetch success stories from JSON
        $successStories = [];
        foreach (jd_read('success_stories') as $row) {
            $successStories[] = [
                'customer_id' => $row['id'],
                'customer_name' => $row['name'],
                'customer_image' => $row['image'],
                'customer_dietplan_type' => $row['dietplan_type'],
                'customer_achievement' => $row['achievement'],
                'customer_pdf' => $row['pdf'],
            ];
        }

        echo "var metadata = " . json_encode($metadata) . ";";
        echo "var successStories = " . json_encode($successStories) . ";";
    ?>
</script>
    


<script>
    function handleUserInput() {
        // Fetch blog data from an API or file
        fetch('/allposts.json')
            .then(response => response.json())
            .then(data => {
                blogData = data; 
            })
            .catch(error => console.error('Error fetching blog data:', error));
        
        // Fetch video data from an API or file
        fetch('/youtube_videos.json')
            .then(response => response.json())
            .then(data => {
                videoData = data; 
            })
            .catch(error => console.error('Error fetching video data:', error));
        
        var input = document.getElementById('chatInput').value.trim().toLowerCase();
        if (input) {
            addMessage(input, 'user');
            document.getElementById('chatInput').value = '';
            setTimeout(function() {
                var response = generateResponse(input);
                addMessage(response, 'bot');
            }, 500);
        }
    }

    function addMessage(text, sender) {
        var messageContainer = document.createElement('div');
        messageContainer.className = 'message ' + sender;
        var bubble = document.createElement('div');
        bubble.className = 'bubble';
        bubble.innerHTML = text;
        messageContainer.appendChild(bubble);
        document.getElementById('chatMessages').appendChild(messageContainer);
        document.getElementById('chatMessages').scrollTop = document.getElementById('chatMessages').scrollHeight;
    }

    function generateResponse(input) {
        var keywords = input.toLowerCase().split(' ').filter(keyword => keyword.length > 2);
        var matchingUrls = [];
        var matchingVideos = [];
        var matchingPosts = [];
        var matchingStories = [];

        // Filter metadata for matching URLs
        for (var urlPattern in metadata) {
            if (metadata.hasOwnProperty(urlPattern)) {
                var metaDataForURL = metadata[urlPattern];
                var pageTitle = (metaDataForURL.pagetitle || '').toLowerCase();
                var pageDescription = (metaDataForURL.pagedescription || '').toLowerCase();
                var keywordsInMetadata = (metaDataForURL.keywords || '').toLowerCase().split(',').map(keyword => keyword.trim());

                var keywordMatch = keywords.some(keyword => urlPattern.includes(keyword) || pageDescription.includes(keyword) || keywordsInMetadata.includes(keyword));
                if (keywordMatch) {
                    matchingUrls.push({ urlPattern, metaDataForURL });
                }
            }
        }

        // Filter blogData for matching posts
        for (var post of blogData) {
            var postTitle = (post.title || '').toLowerCase();
            var keywordsInPost = (post.keywords || '').toLowerCase().split(',').map(keyword => keyword.trim());

            var keywordMatch = keywords.some(keyword => postTitle.includes(keyword) || keywordsInPost.includes(keyword));
            if (keywordMatch) {
                matchingPosts.push(post);
            }
        }


// Filter videoData for matching videos
for (var video of videoData) {
    if (!video.videoId) {
        video.title = '';
    }

    var videoTitle = (video.title || '').toLowerCase();
    var videoSlug = video.slug;
    var keywordsInVideo = (video.keywords || '').toLowerCase().split(',').map(keyword => keyword.trim());

    var keywordMatch = keywords.some(keyword => videoTitle.includes(keyword) || keywordsInVideo.includes(keyword));

    if (keywordMatch) {
        matchingVideos.push(video);
    }
}



        // Filter successStories for matching stories
        for (var story of successStories) {
            var dietPlanType = (story.customer_dietplan_type || '').toLowerCase();
            var achievement = (story.customer_achievement || '').toLowerCase();

            var keywordMatch = keywords.some(keyword => dietPlanType.includes(keyword) || achievement.includes(keyword));
            if (keywordMatch) {
                matchingStories.push(story);
            }
        }

        // Create the response HTML
        var responseHtml = '';

        if (matchingUrls.length > 0) {
            responseHtml += '<h5 class="">Web Pages:</h5><div class="list-group">';
            for (var match of matchingUrls) {
                var urlPattern = match.urlPattern;
                var pageTitle = match.metaDataForURL.pagetitle;
                responseHtml += `
                    <a href="${urlPattern}" class="list-group-item list-group-item-action my-1">
                        <div class="wow">
                            <img src="https://biteanddiet.in/images/share.png" class="img-fluid rounded mr-3" class="search-module-image" alt="${pageTitle}">
                            <div>
                                <h6 class="mb-1">${pageTitle}</h6>
                            </div>
                        </div>
                    </a>`;
            }
            responseHtml += '</div><hr>';
        }

        if (matchingPosts.length > 0) {
            responseHtml += '<h5 class="mt-5">Blog Posts:</h5><ul class="p-0">';
            for (var post of matchingPosts) {
                var PostTitle = post.title.toLowerCase();
                responseHtml += `<li class="search-module-li"><i class="fa fa-arrow-circle-right ttm-textcolor-skincolor px-2"></i><a href="/blog-post/${post.slug}">
                ${PostTitle}</a></li>`;
            }
            responseHtml += '</ul><hr>';
        }

if (matchingVideos.length > 0) {
    responseHtml += '<h5 class="mt-5">Videos:</h5><div class="list-group">';
    for (var video of matchingVideos) {
        var videoTitle = video.title;
        var slug = (videoSlug);
        var customContentUrl = `/video/${slug}`;
        var thumbnailUrl = !video.thumbnail ? 'https://www.biteanddiet.in/images/youtube-default-thumbnail.webp' : video.thumbnail;

        responseHtml += `
            <a href="${customContentUrl}" class="list-group-item list-group-item-action my-1">
                <div class="wow">
                    <img src="${thumbnailUrl}" class="img-fluid rounded mr-3 search-module-image" alt="${videoTitle}">
                    <div class="py-3">
                        <h6 class="mb-1">${videoTitle}</h6>
                        <small>Bite&Diet - Dietician Priyanka</small>
                    </div>
                </div>
            </a>`;
    }
    responseHtml += '</div><hr>';
}
        

        if (matchingStories.length > 0) {
            responseHtml += '<h5 class="mt-5">Success Stories:</h5><div class="list-group">';
            for (var story of matchingStories) {
                var storyTitle = story.customer_achievement;
                responseHtml += `
                    <a href="/${story.customer_pdf}" target="_blank" class="list-group-item list-group-item-action my-1">
                        <div class="wow">
                            <img src="/${story.customer_image}" class="img-fluid rounded mr-3 search-module-image" alt="${story.customer_name}">
                            <div>
                                <h6 class="mb-1">${story.customer_name}</h6>
                                <p class="mb-1">${storyTitle}</p>
                                <small>${story.customer_dietplan_type}</small>
                            </div>
                        </div>
                    </a>`;
            }
            responseHtml += '</div><hr>';
        }

if (responseHtml === '') { 
    return `
        <p>Sorry, I couldn't find any results matching your query.</p>
        <p>For more information, feel free to:</p>
        
            <p><i class="fa fa-phone"></i> Call us at <a href="tel:+918826549878">+91-8826549878</a></p>
            <p><i class="fa fa-whatsapp"></i> Message us on <a href="https://wa.me/918826549878" target="_blank">WhatsApp</a></p>
        
    `;
}

        return responseHtml;
    }
</script>




    

    
    <!-- Chatbot Modal -->
<div class="modal fade chatbot-modal" id="chatbotModal" tabindex="-1" role="dialog" aria-labelledby="chatbotModalLabel" aria-hidden="true">
    <div class="modal-dialog search-modal-right" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="chatbotModalLabel">Bite&Diet Bot</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="chatMessages">
                    <div class="message bot">
                        <div class="bubble">
                            <h6>Hello, </h6>
                            <p class="m-0">Ready to take the first step towards a healthier you?</p>
                            <a href="/form" class="">Enroll Now.</a>
                            <p class="mt-3">Explore our resources and find the information you need.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <input type="text" class="form-control" id="chatInput" placeholder="Ask a question..." onkeydown="if(event.key === 'Enter') handleUserInput()">
                <button class="btn btn-primary" type="button" onclick="handleUserInput()">Send</button>
            </div>
        </div>
    </div>
</div>


<script>
    document.addEventListener('DOMContentLoaded', function() {
    let fabIcon = document.querySelector('.dark-green');
    let isModalOpened = false;
    let colorChangeTimeout;
    let colorChangeInterval;
    let currentColorIndex = 0;
    
    // Array of colors for smooth transitions
    const colors = ['red', 'yellow', 'blue'];

    // Function to smoothly transition colors every 1s
    function smoothColorChange() {
        fabIcon.style.transition = 'background-color 1s';
        fabIcon.style.backgroundColor = colors[currentColorIndex];
        currentColorIndex = (currentColorIndex + 1) % colors.length;
    }

    // Start color changing after 1 minute (60000ms)
    function startColorChangeAfterDelay() {
        colorChangeTimeout = setTimeout(function() {
            colorChangeInterval = setInterval(smoothColorChange, 1000); // Change color every 1s
        }, 10000);  // 1 minute delay
    }

    // Stop the color changing
    function stopColorChange() {
        clearTimeout(colorChangeTimeout); // Stop timeout if color change hasn't started
        clearInterval(colorChangeInterval); // Stop interval if it's already started
        fabIcon.style.backgroundColor = '';  // Reset button color
        fabIcon.style.transition = '';  // Reset transition
    }

    // Event listener to open the modal and stop color cycling
    fabIcon.addEventListener('click', function() {
        if (!isModalOpened) {
            $('#chatbotModal').modal('show');  // Open the modal
            isModalOpened = true;
            stopColorChange();  // Stop the color changing on click
        }
    });

    // When the modal is closed, reset and start the color change countdown again
    $('#chatbotModal').on('hidden.bs.modal', function () {
        isModalOpened = false;  // Reset modal opened state
        startColorChangeAfterDelay();  // Restart color changing after 1 minute
    });

    // Start the initial color change countdown
    startColorChangeAfterDelay();
});

</script>
