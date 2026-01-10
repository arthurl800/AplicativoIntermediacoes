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
        
        <div style="margin: 15px 0; padding: 10px; background: #fff; border: 1px solid #ddd; border-radius: 4px;">
            <p style="margin: 0 0 10px 0; font-weight: bold;">Comportamento com registros duplicados:</p>
            
            <label style="display: block; margin: 5px 0;">
                <input type="radio" name="duplicate_strategy" value="skip" checked>
                <strong>Pular duplicatas</strong> - Ignora registros que já existem (recomendado)
            </label>
            
            <label style="display: block; margin: 5px 0;">
                <input type="radio" name="duplicate_strategy" value="replace">
                <strong>Substituir duplicatas</strong> - Atualiza registros existentes com novos valores
            </label>
            
            <label style="display: block; margin: 5px 0;">
                <input type="radio" name="duplicate_strategy" value="error">
                <strong>Reportar erro</strong> - Falha ao encontrar duplicatas (comportamento atual)
            </label>
        </div>
        
        <button type="submit" class="btn btn-primary">Enviar e Salvar no Banco</button>
    </form>
</div>

  

</main>