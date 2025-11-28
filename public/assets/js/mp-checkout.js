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
        // Usa a rota MVC para processar pagamento
        const paymentUrl = window.location.pathname.includes('/checkout/pagamento') 
            ? window.location.pathname 
            : '/checkout/pagamento';
        fetch(paymentUrl, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(data)
        })
        .then(r => r.text()) // Primeiro pegamos como texto para depurar se necessário
        .then(text => {
            let resp;
            try {
                resp = JSON.parse(text); // Tenta converter para JSON
            } catch (e) {
                console.error("Erro CRÍTICO do PHP (não é JSON):", text);
                alert("Erro no servidor. Veja o console (F12) para detalhes.");
                return;
            }

            if (resp.status === 'success') {
                // PIX: redireciona para revisão (QR Code será gerado lá)
                if (resp.redirect) {
                    // Se o redirect for relativo, converte para absoluto
                    const redirectUrl = resp.redirect.startsWith('/') 
                        ? resp.redirect 
                        : (window.location.origin + '/' + resp.redirect);
                    window.location.href = redirectUrl;
                } else {
                    // Fallback se não houver redirect definido
                    window.location.href = '/checkout/revisao'; 
                }
            } else {
                console.error("Erro retornado pela API:", resp);
                alert(resp.message || 'Erro ao processar pagamento.');
            }
        })
        .catch((err) => {
            console.error("Erro de requisição:", err);
            alert('Erro de comunicação ao processar pagamento.');
        });
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const metodo = form.querySelector('input[name="metodo"]:checked').value;
        const payBtn = document.getElementById('pay-btn');
        if (payBtn) {
            payBtn.disabled = true;
            payBtn.innerHTML = 'Processando...';
        }
        const enableBtn = () => {
            if (payBtn) {
                payBtn.disabled = false;
                payBtn.innerHTML = 'Pagar<span class="icon ms-2">→</span>';
            }
        };
        if (metodo === 'cartao') {
            if (!mp) {
                alert('SDK Mercado Pago não carregado.');
                enableBtn();
                return;
            }
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
                    enableBtn();
                }
            }).catch(() => {
                alert('Erro ao tokenizar cartão.');
                enableBtn();
            });
        } else if (metodo === 'boleto') {
            submitCard({
                metodo: 'boleto',
                name: document.getElementById('boletoName').value,
                email: document.getElementById('boletoEmail').value,
                docNumber: document.getElementById('boletoCPF').value
            });
        } else if (metodo === 'pix') {
            submitCard({
                metodo: 'pix',
                name: document.getElementById('pixName').value,
                email: document.getElementById('pixEmail').value,
                docNumber: document.getElementById('pixCPF').value
            });
        }
        // Reabilita botão em caso de erro no submitCard
        // submitCard já mostra alert em caso de erro
        // Se quiser garantir, pode-se modificar submitCard para aceitar callback de erro
    });
});
