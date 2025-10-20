<main>
    <h2>Importar Dados CSV</h2>
    
    <form action="index.php?controller=upload&action=processUpload" method="POST" enctype="multipart/form-data">
        <p>Selecione o arquivo para importar:</p>
        
        <input type="file" name="arquivo_csv" accept=".csv" required>
        
        <br><br>
        <button type="submit">Enviar e Salvar no Banco</button>
    </form>
</main>