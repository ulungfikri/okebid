<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Pembayaran</title>
</head>
<body>
    <div class="container">
        <div class="row">
            <iframe id="sgoplus-iframe" src="" scrolling="no" frameborder="0">
			<input name="amount" value="10000">
			</iframe>
        </div>
    </div>
    <script type="text/javascript" src="https://sandbox-kit.espay.id/public/signature/js"></script>
    <script type="text/javascript">
        window.onload = function() {
            var data = {
                key: "62459f556dfb212be41cf48ade72",
                paymentId: "<?php echo $invoice;?>",
                // backUrl: "https://api.okebid.com/showpaymen/backurl",
                backUrl: "http://localhost/okebid/showpaymen/backurl",
				display : 'option'
            },
            sgoPlusIframe = document.getElementById("sgoplus-iframe");
            if (sgoPlusIframe !== null) sgoPlusIframe.src = SGOSignature.getIframeURL(data);
            SGOSignature.receiveForm();
        };
    </script>
    
</body>
</html>