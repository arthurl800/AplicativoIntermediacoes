<?php
class EmpresaView
{

    // Formulário de cadastro da empresa
    public function showFormEmpresa()
    {

        echo "
        <h3>Nova Empresa</h3>
        <form action='?route=empresa&action=create' method='post' enctype='multipart/form-data'>
           
            <div>
                <label for='nome'>Nome:</label>
                <input type='text' id='nome' name='nome' required>
            </div>
            <div>
                <label for='cnpj'>CNPJ:</label>
                <input type='text' id='cnpj' name='cnpj'>
            </div>
            <div>
                <label for='endereco'>Endereço:</label>
                <input type='text' id='endereco' name='endereco'>
            </div>
            <div>
                <label for='telefone'>Telefone:</label>
                <input type='text' id='telefone' name='telefone'>
            </div>
            <button type='submit'>Cadastrar</button>
        </form>
        ";
    }


    // Formulário de edição da empresa
    public function showEditEmpresa($empresa)
    {
        echo "
            <h2>Editar Empresa</h2>
            <form action='?route=empresa&action=update' method='post' enctype='multipart/form-data'>
                <input type='hidden' name='action' value='update'>
                <input type='hidden' name='id' value='{$empresa->getId()}'>
               
                <div>
                    <label for='nome'>Nome:</label>
                    <input type='text' id='nome' name='nome' required value='{$empresa->getNome()}'>
                </div>
                <div>
                    <label for='cnpj'>CNPJ:</label>
                    <input type='text' id='cnpj' name='cnpj' value='{$empresa->getCnpj()}'>
                </div>
                <div>
                    <label for='endereco'>Endereço:</label>
                    <input type='text' id='endereco' name='endereco' value='{$empresa->getEndereco()}'>
                </div>

                <div >
                    <label for='telefone'>Telefone:</label>
                    <input type='text' id='telefone' name='telefone' value='{$empresa->getTelefone()}'>
                </div>

                <button type='submit'>Atualizar</button>
            </form>
        ";
    }

    // Página inicial de Empresas (listagem)
    public function listEmpresas($empresas)
    {

        if (isset($_SESSION['message'])) {
            
            $message = $_SESSION['message'];
            echo "<div style='text-align: center; color:green;'>{$message}</div><br>";

            // Limpa a mensagem após exibi-la
            unset($_SESSION['message']);
        }

        echo "<div style='place-items: center; display: grid;' >";
        echo "<h3>Lista de Empresas</h3>";
        
        echo "<input type='text' id='search' onkeyup='searchEmpresas()' placeholder='Buscar Empresa'>";

        echo "<button type='submit' onclick=\"window.location.href='?route=empresa&action=create'\">Inserir Empresa</button>";
        
        echo "<table border='1'>";
        echo "<tr><th>Nome</th><th>CNPJ</th><th>Endereço</th><th>Telefone</th><th>Ações</th></tr>";

        echo "<tbody id='lista-empresas'>";
        foreach ($empresas as $empresa) {
            echo "<tr>";
            echo "<td>{$empresa->getNome()}</td>";
            echo "<td>{$empresa->getCNPJ()}</td>";
            echo "<td>{$empresa->getEndereco()}</td>";
            echo "<td>{$empresa->getTelefone()}</td>";
            echo "<td><a href='?route=empresa&action=update&id={$empresa->getId()}'>Editar</a> <a href='?route=empresa&action=delete&id={$empresa->getId()}' onclick='return confirmarExclusao()'>Apagar</a></td>";
            echo "</tr>";
        }
        echo "</tbody>";
        echo "</table>";
        echo "</div>";
    }

    public function listEmpresasAjax($empresas){
        if (!empty($empresas)) {
            foreach ($empresas as $empresa) {
                echo "<tr>";
                echo "<td>{$empresa->getNome()}</td>";
                echo "<td>{$empresa->getCNPJ()}</td>";
                echo "<td>{$empresa->getEndereco()}</td>";
                echo "<td>{$empresa->getTelefone()}</td>";
                echo "<td><a href='?route=empresa&action=update&id={$empresa->getId()}'>Editar</a> <a href='?route=empresa&action=delete&id={$empresa->getId()}' onclick='return confirmarExclusao()'>Apagar</a></td>";
                echo "</tr>";
            }
        } else echo "<tr><td colspan='5'>Nenhuma empresa encontrada.</td></tr>";
    }
}