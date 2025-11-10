<?php

namespace App\Controllers;

use App\Core\Controller;

/**
 * TestController - Testes e utilitários
 */
class TestController extends Controller
{
    /**
     * Teste de conexão com banco
     */
    public function database(): void
    {
        try {
            $pdo = \App\Core\Model::getConnection();
            $stmt = $pdo->query("SELECT 'Conexão OK' as status, NOW() as current_time");
            $result = $stmt->fetch();
            
            echo "<h1>Teste de Conexão com Banco de Dados</h1>";
            echo "<p>Status: <strong>{$result['status']}</strong></p>";
            echo "<p>Hora do servidor: <strong>{$result['current_time']}</strong></p>";
            echo "<p>Banco: <strong>" . DB_NAME . "</strong></p>";
            echo "<p>Host: <strong>" . DB_HOST . "</strong></p>";
            
        } catch (\Exception $e) {
            echo "<h1>Erro na Conexão</h1>";
            echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
    
    /**
     * Teste de sessão
     */
    public function session(): void
    {
        echo "<h1>Teste de Sessão</h1>";
        echo "<h2>Dados da Sessão:</h2>";
        echo "<pre>";
        print_r($_SESSION);
        echo "</pre>";
        
        echo "<h2>Session ID:</h2>";
        echo "<p>" . session_id() . "</p>";
        
        echo "<h2>Cookies:</h2>";
        echo "<pre>";
        print_r($_COOKIE);
        echo "</pre>";
    }
}
