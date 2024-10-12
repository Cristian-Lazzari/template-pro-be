<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Success</title>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
</head>
<body>
    <h1>Thank you for your order!</h1>
    <p>Your order has been successfully processed.</p>

    <script type="text/javascript">
        // Funzione per ottenere i parametri query dall'URL
        function getQueryParam(param) {
            const urlParams = new URLSearchParams(window.location.search);
            return urlParams.get(param);
        }

        // Ottieni l'ID dall'URL
        const orderId = getQueryParam('id');

        // Dati dell'ordine che vuoi inviare al server
        const orderData = {
            order_id: orderId,              // Usa l'ID estratto dall'URL
        };

        // Effettua una richiesta POST all'endpoint specificato
        axios.post('https://127.0.0.1:8000/api/orderSuccess', orderData)
        .then(function (response) {
            console.log('Success:', response.data);
            // Qui puoi gestire una risposta positiva
        })
        .catch(function (error) {
            console.error('Error:', error);
            // Qui puoi gestire gli errori
        });
    </script>
</body>
</html>
