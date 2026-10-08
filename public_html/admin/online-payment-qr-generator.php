<?php

// Start the session
session_start();

$_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    
    
// Check if the user is not logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // Redirect to the login page or any other page you want
    header("Location: ../login.php");
    exit;
}

?>


<?php include '../Header.php'; ?>

<!-- page-title -->
<div class="ttm-page-title-row">
    <div class="ttm-page-title-row-bg-layer ttm-bg-layer"></div>
    <div class="container">
        <div class="row">
            <div class="col-md-12 text-center"> 
                <div class="title-box ttm-textcolor-white">
                    <div class="page-title-heading ttm-textcolor-white">
                        <h2 class="title">Payment QR Generator</h2>
                    </div><!-- /.page-title-captions -->
                    <div class="breadcrumb-wrapper">
                        <span>
                            <a title="Homepage" href="/"><i class="ti ti-home"></i> Home </a>
                        </span>
                        <span class="ttm-bread-sep"> &nbsp; :&nbsp;: &nbsp; </span>
                        <span><span class="ttm-textcolor-skincolor">Payment QR Generator</span></span>
                    </div>  
                </div>
            </div><!-- /.col-md-12 -->  
        </div><!-- /.row -->  
    </div><!-- /.container -->                      
</div><!-- page-title end -->

<!-- error-404 start -->
<section class="error-404">
<header class="section-title text-center">
        <h5>Payment QR Generator</h5>
    </header>

</head>
<body>
<div class="container mt-5">
    <h3>Online Payment QR Generator</h3>
  <form id="payment-form">
        <div class="form-group">
            <label for="name">Name:</label>
            <input type="text" class="form-control" id="name" placeholder="Enter Name">
        </div>
        <div class="form-group">
            <label for="amount">Amount:</label>
            <input type="number" class="form-control" id="amount" placeholder="Enter Amount">
        </div>
        <div class="form-group">
            <label for="message">Message:</label>
            <input type="text" class="form-control" id="message" placeholder="Enter Message">
        </div>
        <div class="py-4">
        <button type="button" class="btn btn-primary" id="generate-link">Generate Link</button>
        <button type="button" class="btn btn-success ml-2" id="download-qr">Download QR Code</button>
            </div>
    </form>
    
        <div class="text-center" style="py-3">
        <h4 id="namePlaceholder">            </h4>
        <strong id="amountPlaceholder"></strong>
        <p id="messagePlaceholder"></p>
        <div>
        <button id="copy-link" class="btn btn-info mb-30">Copy Link</button>
        <input type="text" id="upi-link" readonly>
    </div>
    </div>
    <div class="py-5 text-center" id="qrcode-container">
    </div>
</div>
</section>


<script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
    <!-- Include QRCode.js library -->
    <script src="https://cdn.rawgit.com/davidshimjs/qrcodejs/gh-pages/qrcode.min.js"></script>
  


<script>
document.addEventListener('DOMContentLoaded', function () {
    const generateLinkButton = document.getElementById('generate-link');
    const downloadQRButton = document.getElementById('download-qr');
    const nameInput = document.getElementById('name');
    const amountInput = document.getElementById('amount');
    const messageInput = document.getElementById('message');
    const qrcodeContainer = document.getElementById('qrcode-container');
    const namePlaceholder = document.getElementById('namePlaceholder');
    const amountPlaceholder = document.getElementById('amountPlaceholder');
    const messagePlaceholder = document.getElementById('messagePlaceholder');
    const copyLinkButton = document.getElementById('copy-link');
    const upiLinkInput = document.getElementById('upi-link');
    

    let qrCodeInstance = null;
generateLinkButton.addEventListener('click', function () {
        const name = encodeURIComponent(nameInput.value);
        const amount = amountInput.value;
        //const message = encodeURIComponent(messageInput.value);
        const message = encodeURIComponent(messageInput.value.trim()); // Use trim() to remove spaces
        
        const decodedName = decodeURIComponent(name.replace(/%20/g, ' '));
        const decodedMessage = decodeURIComponent(message.replace(/%20/g, ' '));
        
        
        if (!name || !amount) {
            showAlert('Please fill in the Name and Amount fields before generating the QR code.');
            return;
        }

       // const upiLink = `upi://pay?pa=yadavshashi366@okaxis&aid=uGICAgICgvbjgTg&am=${amount}&cu=INR&tn=${message}`;
        
        const upiLink = `upi://pay?pa=priyankaprakash1905@okicici&pn=Dt.Priyanka%20Bite%20Diet&aid=uGICAgIDYivHoEQ&am=${amount}&cu=INR&tn=${message}`;


        
        // Clear existing QR code
        qrcodeContainer.innerHTML = '';

        // Generate QR code
        qrCodeInstance = new QRCode(qrcodeContainer, {
            text: upiLink,
            width: 300,
            height: 300,
            colorDark: '#000000',
            colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.H
        });

        // Update placeholders
        namePlaceholder.innerHTML = decodedName;
        amountPlaceholder.innerHTML = amount;
        messagePlaceholder.innerHTML = decodedMessage;
        upiLinkInput.value = upiLink;
    });
    
    copyLinkButton.addEventListener('click', function () {
        if (!upiLinkInput.value) {
            showAlert('Generate the UPI link before copying.');
            return;
        }
        upiLinkInput.select();
        document.execCommand('copy');
        showAlert('Link copied to clipboard.');
    });
    
    downloadQRButton.addEventListener('click', function () {
        if (!qrCodeInstance) {
            showAlert('Generate the QR code before downloading.');
            return;
        }

        const qrContainer = document.getElementById('qrcode-container');
        if (qrContainer) {
            html2canvas(qrContainer, { scale: 2 }).then(canvas => {
                const name = nameInput.value || 'Name';
                const amount = amountInput.value || 'Amount';
                const message = messageInput.value || 'Message';

                const context = canvas.getContext('2d');
                context.fillStyle = '#000000';
                context.font = '12px Arial';

                
const textX = 160;
                const textY = canvas.height - 0;

                context.fillText(name, textX, textY);
                context.fillText(amount, textX, textY + 15);
                context.fillText(message, textX, textY + 30);

                canvas.toBlob(blob => {
                    const link = document.createElement('a');
                    link.href = URL.createObjectURL(blob);
                    link.download = `${name}_${amount}_${message}_QR.png`;
                    link.click();
                }, 'image/png');
            });
        }
    });

    function showAlert(message) {
        const alertDiv = document.createElement('div');
        alertDiv.classList.add('alert', 'alert-danger', 'mt-3');
        alertDiv.textContent = message;
        qrcodeContainer.appendChild(alertDiv);
        
setTimeout(() => {
            alertDiv.remove();
        }, 2000);
    }
});
</script>

<!-- error-404 end -->

<?php include '../footer.php'; ?>
