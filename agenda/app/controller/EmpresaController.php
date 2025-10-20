<?php

include_once 'app/model/Empresa.php';
include_once 'app/view/EmpresaView.php';

class EmpresaController
{

    public function handleRequest($action, $params)
    {

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($action)) {

            switch ($action) {

                case 'create':
                    $nome = $params['nome'];
                    $cnpj = $params['cnpj'];
                    $endereco = $params['endereco'];
                    $telefone = $params['telefone'];

                    $empresa = new Empresa(null, $nome, $cnpj, $endereco, $telefone);

                    if ($empresa->create()) {
                        $_SESSION['message'] = 'Empresa cadastrada com sucesso.';
                    } else {
                        $_SESSION['message'] = 'Ocorreu um erro ao cadastrar a empresa.';
                    }

                    header('Location: ?route=empresa');
                    exit(); // Garante que o redirecionamento ocorra e o script não continue
                    break;

                case 'update':

                    $id = $params['id'];
                    $nome = $params['nome'];
                    $cnpj = $params['cnpj'];
                    $endereco = $params['endereco'];
                    $telefone = $params['telefone'];
                    $empresa = new Empresa($id, $nome, $cnpj, $endereco, $telefone);
                    if ($empresa->update()) {
                        $_SESSION['message'] = 'Empresa atualizada com sucesso';
                    } else {
                        $_SESSION['message'] = 'Ocorreu um erro ao atualizar a empresa.';
                    }
                    header('Location: ?route=empresa');
                    exit();

                    break;
            }
        } else {
            switch ($action) {

                    // abrir formulário de cadastro
                case 'create':
                    $view = new EmpresaView();
                    $view->showFormEmpresa();
                    break;

                    // abrir formulário de edição da empresa
                case 'update':
                    if (isset($params['id'])) {
                        $empresa = Empresa::getById($params['id']);
                        if ($empresa) {
                            $view = new EmpresaView();
                            $view->showEditEmpresa($empresa);
                        } else {
                            echo "Empresa não encontrada.";
                        }
                    } else {
                        echo "ID da empresa não especificado.";
                    }
                    break;

                case 'search':
                    $query = $params['query'] ?? '';

                    // Buscar empresas pelo nome
                    $empresas = Empresa::searchByName($query);

                    $view = new EmpresaView;
                    $view->listEmpresasAjax($empresas);

                    break;

                    // Listar as empresas
                case 'list':

                    $empresas = Empresa::getAll();
                    $view = new EmpresaView();
                    $view->listEmpresas($empresas);

                    break;


                case 'delete':
                    if (isset($params['id'])) {
                        $empresaId = $params['id'];
                        $empresa = Empresa::getById($empresaId);

                        if ($empresa) {
                            if ($empresa->delete()) {
                                $_SESSION['message'] = 'Empresa -- ' . $empresa->getNome() . ' -- excluída com sucesso!';
                            } else {
                                $_SESSION['message'] = 'Ocorreu um erro ao tentar excluir a empresa.';
                            }
                        } else {
                            $_SESSION['message'] = 'Empresa não encontrada.';
                        }
                    } else {
                        $_SESSION['message'] = 'ID da empresa não especificado.';
                    }
                    header('Location: ?route=empresa');
                    exit();
                    break;
            }
        }
    }
}
