// Mercado Pago Checkout Transparente - Tokenização do cartão
// Este script será chamado após o SDK ser carregado e a public_key estar disponível

document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('mp-checkout-form');
    if (!form) return;

    // Alterna exibição dos formulários conforme método
    function toggleForms() {
        const metodo = form.querySelector('input[name="metodo"]:checked').value;
        document.getElementById('mp-card-form').style.display = metodo === 'cartao' ? '' : 'none';
        document.getElementById('mp-boleto-form').style.display = metodo === 'boleto' ? '' : 'none';
        document.getElementById('mp-pix-form').style.display = metodo === 'pix' ? '' : 'none';
    }
    form.querySelectorAll('input[name="metodo"]').forEach(radio => {
        radio.addEventListener('change', toggleForms);
    });
    toggleForms();

    // Cartão: tokenização Mercado Pago
    let mp = window.MercadoPagoObj;
    function submitCard(data) {
        fetch(window.location.pathname, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(data)
        })
        .then(r => r.json())
        .then(resp => {
            if (resp.status === 'success') {
                if (resp.pix_qr && resp.pix_copy) {
                    // Exibe QR Code e código Pix
                    exibirPix(resp.pix_qr, resp.pix_copy);
                } else if (resp.redirect) {
                    window.location.href = resp.redirect;
                } else {
                    window.location.href = '/public/checkout/revisao.php';
                }
            } else {
                alert(resp.message || 'Erro ao processar pagamento.');
            }
        })
        .catch(() => alert('Erro ao processar pagamento.'));
    }

    // Exibe QR Code Pix e código copia/cola
    function exibirPix(qrBase64, pixCopy) {
        let container = document.getElementById('pix-result');
        if (!container) {
            container = document.createElement('div');
            container.id = 'pix-result';
            container.className = 'mt-4 alert alert-success';
            form.parentNode.insertBefore(container, form.nextSibling);
        }
        container.innerHTML = `
            <h5>Pagamento via Pix</h5>
            <p>Escaneie o QR Code abaixo no app do seu banco ou copie o código Pix.</p>
            <img src="data:image/png;base64,${qrBase64}" alt="QR Code Pix" style="max-width:220px;display:block;margin:0 auto 1em;">
            <div class="mb-2"><strong>Código copia e cola:</strong></div>
            <textarea class="form-control mb-2" readonly rows="2">${pixCopy}</textarea>
            <button class="btn btn-outline-primary" onclick="navigator.clipboard.writeText('${pixCopy}')">Copiar código</button>
            <div class="mt-3">Após o pagamento, clique em <a href="/public/checkout/revisao.php">Continuar</a>.</div>
        `;
        window.scrollTo({top: container.offsetTop - 40, behavior: 'smooth'});
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const metodo = form.querySelector('input[name="metodo"]:checked').value;
        if (metodo === 'cartao') {
            if (!mp) return alert('SDK Mercado Pago não carregado.');
            // Tokenização cartão
            mp.cardToken.create({
                cardNumber: document.getElementById('cardNumber').value,
                cardholderName: document.getElementById('cardholderName').value,
                cardExpirationMonth: document.getElementById('cardExpiration').value.split('/')[0],
                cardExpirationYear: '20' + document.getElementById('cardExpiration').value.split('/')[1],
                securityCode: document.getElementById('securityCode').value,
                identificationType: 'CPF',
                identificationNumber: document.getElementById('docNumber').value
            }).then(function(result) {
                if (result.status === 200 || result.status === 201) {
                    submitCard({
                        metodo: 'cartao',
                        token: result.body.id,
                        paymentMethodId: result.body.cardholder.identification.type || 'visa',
                        email: '', // pode ser adicionado campo de email se desejar
                        docType: 'CPF',
                        docNumber: document.getElementById('docNumber').value
                    });
                } else {
                    alert('Erro ao tokenizar cartão: ' + (result.message || '')); 
                }
            });
        } else if (metodo === 'boleto') {
            // Envia dados do boleto
            submitCard({
                metodo: 'boleto',
                name: document.getElementById('boletoName').value,
                email: document.getElementById('boletoEmail').value,
                docNumber: document.getElementById('boletoCPF').value
            });
        } else if (metodo === 'pix') {
            // Envia dados do Pix
            submitCard({
                metodo: 'pix',
                name: document.getElementById('pixName').value,
                email: document.getElementById('pixEmail').value,
                docNumber: document.getElementById('pixCPF').value
            });
        }
    });
});
