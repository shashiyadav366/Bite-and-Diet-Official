// Function to include scripts
function includeScripts() {
  // Include Bootstrap JavaScript from the CDN
  const bootstrapScript = document.createElement('script');
  bootstrapScript.src = 'https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js';
  document.body.appendChild(bootstrapScript);

  // Include Google Tag Manager script
  const gtagScript = document.createElement('script');
  gtagScript.src = 'https://www.googletagmanager.com/gtag/js?id=G-88KZ4XK114';
  document.body.appendChild(gtagScript);
}

// Listen for messages from the main thread
onmessage = function (e) {
  if (e.data === 'includeScripts') {
    includeScripts();
  }
};
