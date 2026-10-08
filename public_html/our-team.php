<?php include 'Header.php'; ?>
<div class="ttm-page-title-row">
  <div class="ttm-bg-layer ttm-page-title-row-bg-layer"></div>
  <div class="container">
    <div class="row">
      <div class="col-md-12 text-center">
        <div class="ttm-textcolor-white title-box">
          <div class="ttm-textcolor-white page-title-heading">
            <h1 class="title">Our Team</h1>
          </div>
          <div class="breadcrumb-wrapper">
            <span><a href="./" title="Homepage"><i class="ti ti-home"></i> Home </a></span>
            <span class="ttm-bread-sep">: : </span>
            <span><span class="ttm-textcolor-skincolor">About Us</span></span>
            <span class="ttm-bread-sep">: : </span>
            <span><span class="ttm-textcolor-skincolor">Our Team</span></span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="site-main">
  <section class="clearfix ttm-row about2-top-section">
    <div class="clearfix section-title text-center py-5">
      <div class="title-header">
        <h5>Meet the Bite & Diet Team</h5>
        <h2>Our Dedicated Team Members</h2>
      </div>
          <div class="container">
      <div class="row">
        <div class="col-md-12">
          <p class="team-introduction">At Bite & Diet, our team comprises passionate and experienced professionals committed to enhancing your health and wellness. From our founder and dietitian to our technical and managerial experts, each member brings specialized knowledge and skills to deliver personalized and effective dietary solutions. Meet the faces behind our success and learn how their expertise drives our mission.</p>
      
        </div>
      </div>
      </div>
    </div>
    <div class="container">
      <div class="row" id="team-row"></div>
    </div>
  </section>

  <script>
  document.addEventListener('DOMContentLoaded', function() {
      fetch('our-team.json')
      .then(response => response.json())
      .then(data => {
          const container = document.getElementById('team-row');
          container.innerHTML = '';  // Clear the container

          data.forEach(member => {
              const memberDiv = document.createElement('div');
              memberDiv.className = 'col-lg-3 col-md-6';
              
              // Build the social links if any exist
              let socialLinks = '';
              if (member.facebook || member.instagram || member.linkedin) {
                  socialLinks = `
                      <div class="ttm-social-links-wrapper">
                          <ul class="list-inline social-icons">
                              ${member.facebook ? `<li class="social-facebook"><a href="${member.facebook}" target="_blank"><i class="fa fa-facebook" aria-hidden="true"></i></a></li>` : ''}
                              ${member.instagram ? `<li class="social-instagram"><a href="${member.instagram}" target="_blank"><i class="fa fa-instagram" aria-hidden="true"></i></a></li>` : ''}
                              ${member.linkedin ? `<li class="social-linkedin"><a href="${member.linkedin}" target="_blank"><i class="fa fa-linkedin" aria-hidden="true"></i></a></li>` : ''}
                          </ul>
                      </div>
                  `;
              }

              memberDiv.innerHTML = `
                  <div class="featured-imagebox featured-imagebox-team mb-30 ttm-team-box-view-overlay-style2">
                      <div class="ttm-box-view-overlay ttm-team-style2-box-view-overlay">
                          <div class="featured-thumbnail">
                              <img alt="${member.name} - ${member.position}" class="img-fluid" src="${member.image}">
                              ${socialLinks}
                          </div>
                      </div>
                      <div class="featured-content featured-content-team" style="min-height:unset">
                          <div class="featured-team-title">
                              <h5 class="m-0"><a href="${member.profileUrl || '#'}">${member.name}</a></h5>
                          </div>
                          <div class="ttm-textcolor-white ttm-team-position p-0">${member.position}</div>
                      </div>
                  </div>
              `;
              container.appendChild(memberDiv);
          });
      });
  });
  </script>
</div>

<?php include 'footer.php'; ?>
