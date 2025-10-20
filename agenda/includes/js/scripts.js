function confirmarExclusao() {
    return confirm("Tem certeza que deseja excluir o item?");
}


function searchEmpresas() {

    const query = document.getElementById('search').value;
    const xhr = new XMLHttpRequest();
    xhr.open('GET', `?route=empresa&action=search&query=${query}`, true);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    console.log('Buscando empresa com a query:', query);
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            // Atualiza a tabela de empresas com os resultados da busca
            const empresaLista = document.getElementById('lista-empresas');
            // Atualiza a tabela
            empresaLista.innerHTML = xhr.responseText;

        }
    };
    xhr.send();
}