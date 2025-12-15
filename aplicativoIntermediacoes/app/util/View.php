<?php
// app/util/View.php

namespace App\Util;

class View {
    private string $baseDir;

    public function __construct() {
        // Define o diretório base para as views e includes
        $this->baseDir = dirname(dirname(__DIR__));
    }

    /**
     * Renderiza uma view, opcionalmente passando dados para ela.
     *
     * @param string $viewPath Caminho para o arquivo da view a partir de 'app/view/'.
     * @param array $data Dados a serem extraídos como variáveis na view.
     */
    public function render(string $viewPath, array $data = []): void {
        // Extrai os dados do array para variáveis locais (ex: $data['title'] vira $title)
        extract($data);

        // Inicia o buffer de saída para capturar o conteúdo da view
        ob_start();
        
        // Inclui o conteúdo principal da view
        require $this->baseDir . '/app/view/' . $viewPath . '.php';
        
        // Armazena o conteúdo da view e limpa o buffer
        $content = ob_get_clean();

        // Inclui o layout principal, que por sua vez irá renderizar a variável $content
        require $this->baseDir . '/includes/layout.php';
    }
}