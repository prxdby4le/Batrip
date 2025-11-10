<?php

namespace App\Controllers;

use App\Core\Controller;

/**
 * PageController - Páginas estáticas
 */
class PageController extends Controller
{
    /**
     * Página Sobre
     */
    public function about(): void
    {
        $data = [
            'pageTitle' => 'Sobre - Batrip',
            'layout' => 'main'
        ];
        
        $this->view('pages.about', $data);
    }
    
    /**
     * Página Personalização
     */
    public function customization(): void
    {
        $data = [
            'pageTitle' => 'Personalização - Batrip',
            'layout' => 'main'
        ];
        
        $this->view('pages.customization', $data);
    }
}
