<?php

namespace App\Controllers;

use App\Core\Controller;

/**
 * ShippingController - Tela de Frete
 * Calcula e seleciona opções de frete com padrão visual do endereço do checkout
 */
class ShippingController extends Controller
{
    /**
     * Exibe formulário de cálculo de frete e resultados (se houver)
     */
    public function index(): void
    {
        // Base input da sessão
        $input = $_SESSION['shipping_input'] ?? [
            'zipcode' => '',
            'address' => '',
            'city' => '',
            'state' => '',
            'weight' => '',
            'length' => '',
            'width' => '',
            'height' => '',
        ];

        // Se vierem dados por query (ex: da página de checkout), mescla e salva
        $queryData = [
            'zipcode' => $_GET['zipcode'] ?? null,
            'address' => $_GET['address'] ?? null,
            'city' => $_GET['city'] ?? null,
            'state' => $_GET['state'] ?? null,
        ];
        foreach ($queryData as $k => $v) {
            if ($v !== null && $v !== '') {
                $input[$k] = trim((string)$v);
            }
        }
        $_SESSION['shipping_input'] = $input;

        $quotes = $_SESSION['shipping_quotes'] ?? [];
        $selected = $_SESSION['shipping'] ?? null;

        $this->view('checkout.shipping', [
            'pageTitle' => 'Calcular Frete - Batrip',
            'input' => $input,
            'quotes' => $quotes,
            'selected' => $selected,
            'layout' => 'main',
        ]);
    }

    /**
     * Processa cálculo de frete (POST)
     */
    public function calculate(): void
    {
        if (!$this->request->isPost()) {
            $this->redirect(BASE_URL . 'frete');
            return;
        }

        // CSRF
        $token = $this->request->header('X-CSRF-Token') ?? $this->request->post('csrf_token') ?? '';
        if (!$this->validateCsrf($token)) {
            $_SESSION['error'] = 'Falha de segurança: CSRF inválido.';
            $this->redirect(BASE_URL . 'frete');
            return;
        }

    $zipcode = trim((string)$this->request->post('zipcode'));
    $address = trim((string)($this->request->post('address') ?? ''));
    $city    = trim((string)($this->request->post('city') ?? ''));
    $state   = trim((string)($this->request->post('state') ?? ''));
        $weight = (float)$this->request->post('weight');
        $length = (float)($this->request->post('length') ?? 0);
        $width  = (float)($this->request->post('width') ?? 0);
        $height = (float)($this->request->post('height') ?? 0);

        $errors = [];

        // Validação CEP brasileiro: 00000-000 ou 00000000
        if (!$this->isValidZipcode($zipcode)) {
            $errors[] = 'CEP inválido. Use o formato 00000-000.';
        }
        if ($weight <= 0) {
            $errors[] = 'Informe o peso (kg) maior que zero.';
        }
        if ($length < 0 || $width < 0 || $height < 0) {
            $errors[] = 'Dimensões não podem ser negativas.';
        }

        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $this->redirect(BASE_URL . 'frete');
            return;
        }

        // Normaliza inputs salvos
        $_SESSION['shipping_input'] = [
            'zipcode' => $this->normalizeZipcode($zipcode),
            'address' => $address,
            'city' => $city,
            'state' => $state,
            'weight' => $weight,
            'length' => $length,
            'width' => $width,
            'height' => $height,
        ];

        // Calcula cotações simples (mock): PAC e SEDEX
        $quotes = $this->buildQuotes($weight, $length, $width, $height);
        $_SESSION['shipping_quotes'] = $quotes;

        // Limpa seleção anterior, se existia
        unset($_SESSION['shipping']);

        $this->redirect(BASE_URL . 'frete');
    }

    /**
     * Seleciona uma opção de frete calculada e redireciona ao checkout
     */
    public function select(): void
    {
        if (!$this->request->isPost()) {
            $this->redirect(BASE_URL . 'frete');
            return;
        }

        // CSRF
        $token = $this->request->header('X-CSRF-Token') ?? $this->request->post('csrf_token') ?? '';
        if (!$this->validateCsrf($token)) {
            $_SESSION['error'] = 'Falha de segurança: CSRF inválido.';
            $this->redirect(BASE_URL . 'frete');
            return;
        }

        $method = (string)$this->request->post('method');
        $quotes = $_SESSION['shipping_quotes'] ?? [];

        if (!isset($quotes[$method])) {
            $_SESSION['error'] = 'Opção de frete inválida ou expirada. Calcule novamente.';
            $this->redirect(BASE_URL . 'frete');
            return;
        }

        $_SESSION['shipping'] = array_merge([
            'method' => $method,
        ], $quotes[$method], [
            'zipcode' => $_SESSION['shipping_input']['zipcode'] ?? '',
        ]);

        $_SESSION['success'] = 'Frete selecionado com sucesso!';
        $this->redirect(BASE_URL . 'checkout');
    }

    private function isValidZipcode(string $zip): bool
    {
        return (bool)preg_match('/^\d{5}-?\d{3}$/', $zip);
    }

    private function normalizeZipcode(string $zip): string
    {
        $zip = preg_replace('/\D/', '', $zip);
        return substr($zip, 0, 5) . '-' . substr($zip, 5, 3);
    }

    /**
     * Gera cotações simuladas de frete
     */
    private function buildQuotes(float $weight, float $length, float $width, float $height): array
    {
        $base = 12.90;
        $byWeight = $weight * 4.5; // R$ por kg

        // Volume em litros aproximado (cm^3 -> L)
        $volumeLiters = ($length * $width * $height) / 1000; // 1000 cm^3 = 1L
        $volumeSurcharge = max(0, ($volumeLiters - 10) * 0.25); // sobretaxa acima de 10L

        $pacCost   = round($base + $byWeight + $volumeSurcharge, 2);
        $sedexCost = round($base + ($byWeight * 1.35) + ($volumeSurcharge * 1.2) + 8.0, 2);

        return [
            'PAC' => [
                'label' => 'PAC',
                'cost' => $pacCost,
                'eta_days' => '5-8 dias úteis',
            ],
            'SEDEX' => [
                'label' => 'SEDEX',
                'cost' => $sedexCost,
                'eta_days' => '2-3 dias úteis',
            ],
        ];
    }
}
