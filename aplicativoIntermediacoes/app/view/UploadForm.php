<?php
// app/view/upload/UploadForm.php
?>

<main>

<div class="form-container" 
     style="background-color: #fffde7; 
            border: 2px solid #f9a825; 
            border-radius: 8px; 
            padding: 20px; 
            text-align: left; 
            max-width: 600px; 
            margin: 20px auto;">
    
    <h2>Importar Dados</h2>
    
    <form action="index.php?controller=upload&action=processUpload" method="POST" enctype="multipart/form-data">
        <p>Selecione o arquivo para importar:</p>
        
        <input type="file" name="arquivo_csv" accept=".csv,.xlsx,.xls,.ods" required class="input-field">
        
        <br><br>
        <button type="submit" class="btn btn-primary">Enviar e Salvar no Banco</button>
    </form>
</div>

  

</main>