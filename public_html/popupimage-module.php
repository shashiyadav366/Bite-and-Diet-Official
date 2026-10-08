<div class="fade modal firstmodal" id="offerModal" aria-labelledby="dialogLabel" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="mt-3 px-4">
                <button class="close" data-dismiss="modal" type="button">×</button>
                <h4 class="modal-title text-center">Celebrating</h4>
            </div>
            <div class="modal-body">
                <!-- Image -->
                <div class="text-center">
                    <img src="https://www.biteanddiet.in/images/social_posts_images/5years_of_bite-and-diet.jpg" alt="5 years of Bite&Diet" class="img-fluid">
                </div>
                <!-- Buttons -->
                <div class="mt-4 text-center">
                    <a href="https://www.biteanddiet.in/social_post_details?id=18042828413065247720" class="btn btn-primary me-2">Know More</a>
                    <a href="https://www.biteanddiet.in/form" class="btn btn-success" >Enroll Now</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        // Check if today's date is before 10th June 2025
        const today = new Date();
        const offerExpiry = new Date("2025-06-10T23:59:59");

        if (today <= offerExpiry) {
            // Show modal after a delay of 35 seconds
            setTimeout(function () {
                // Check if the cookie exists to prevent repeated popups
                if (document.cookie.indexOf("offerModalShown=true") === -1) {
                    $("#offerModal").modal({ show: true });

                    // Set a cookie to remember that the modal has been shown
                    const expirationDate = new Date(new Date().valueOf() + 24 * 60 * 60 * 1000); // Expires in 24 hours
                    document.cookie = "offerModalShown=true;expires=" + expirationDate.toUTCString();
                }
            }, 35000); // 35 seconds delay
        }
    });
</script>
