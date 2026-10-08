<?php include 'Header.php'; ?>

<div class="ttm-page-title-row">
  <div class="ttm-bg-layer ttm-page-title-row-bg-layer"></div>
  <div class="container">
    <div class="row">
      <div class="text-center col-md-12">
        <div class="ttm-textcolor-white title-box">
          <div class="ttm-textcolor-white page-title-heading">
            <h1 class="title">Customer Reviews </h1>
          </div>
          <div class="breadcrumb-wrapper">
            <span><a href="/" title="Homepage"><i class="ti ti-home"></i> Home </a></span>
            <span class="ttm-bread-sep">: : </span>
            <span><span class="ttm-textcolor-skincolor">About Us</span></span>
            <span class="ttm-bread-sep">: : </span>
            <span><span class="ttm-textcolor-skincolor">Customer Reviews </span></span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<section class="error-404">
  <header class="text-center section-title pb-4">
    <h5>Customer Reviews </h5>
    <h2>Customer Reviews and Feedback</h2>
  </header>
  <div class="container mt-4">
  <div id="customerReviews" class="row"></div>
  <div class="text-center col-lg-12"><div class="mt-20 res-991-mt-30">
      <a href="https://maps.app.goo.gl/UtWpVfpJoVxDhYw99" class="ttm-btn mb-20 ttm-btn-shape-round ttm-btn-size-md ttm-btn-style-fill ttm-btn-bgcolor-black">More</a> 
      
      <a href="https://g.page/r/CRy5rUUDOCcQEB0/review" class="ttm-btn mb-20 ttm-btn-shape-round ttm-btn-size-md ttm-btn-style-fill ml-15 ttm-btn-bgcolor-skincolor">Review Us</a>
      </div></div>
</div>

 <script>
  // Function to create a customer card
 // Function to create a customer card
// Function to create a customer card
function createCustomerCard(review) {
  const card = document.createElement('div');
  card.classList.add('card', 'customer-card', 'col-12','col-xl-4','col-md-6','col-lg-4');

  // First Row: Image, Name, and Stars
  const firstRow = document.createElement('div');
  firstRow.classList.add('d-flex', 'align-items-center', 'mt-3');

  const image = document.createElement('img');
  image.src = review.customer_image;
  image.alt = review.customer_name;
  image.classList.add('customer-image', 'mr-3');

  const contentWrapper = document.createElement('div');
  contentWrapper.classList.add('customer-details');

  const name = document.createElement('h5');
  name.classList.add('card-title', 'mb-0');
  name.textContent = review.customer_name;

  const stars = document.createElement('div');
  stars.classList.add('star-rating');
  stars.innerHTML = '&#9733; &#9733; &#9733; &#9733; &#9733;';

  contentWrapper.appendChild(name);
  contentWrapper.appendChild(stars);

  firstRow.appendChild(image);
  firstRow.appendChild(contentWrapper);

  // Second Row: Full Review
  const secondRow = document.createElement('div');

  const date = document.createElement('p');
  //date.classList.add('tell');
  date.textContent = review.time;

  const reviewText = document.createElement('p');
  reviewText.classList.add('customer-card-text');
  reviewText.textContent = review.customer_reviews;

  secondRow.appendChild(date);
  secondRow.appendChild(reviewText);

  // Append both rows to the card
  card.appendChild(firstRow);
  card.appendChild(secondRow);

  return card;
}


  // Function to render customer reviews
  function renderCustomerReviews(data) {
    const reviewsContainer = document.getElementById('customerReviews');

    data.forEach(review => {
      const card = createCustomerCard(review);
      reviewsContainer.appendChild(card);
    });
  }

  // Load data from the JSON file
  async function loadData() {
    try {
      const response = await fetch('google-review-data.json');
      const data = await response.json();

      // Call the function to render customer reviews with the loaded data
      renderCustomerReviews(data);
    } catch (error) {
      console.error('Error loading data:', error);
    }
  }

  // Call the function to load and render customer reviews on page load
  window.onload = function () {
    loadData();
  };
</script>


</section>




<?php include 'footer.php'; ?>
