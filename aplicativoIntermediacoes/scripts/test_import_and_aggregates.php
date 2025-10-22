<?php
// scripts/test_import_and_aggregates.php
// Script rápido para testar insertBatch e getNegotiableAggregates

require_once __DIR__ . '/../app/util/Database.php';
require_once __DIR__ . '/../app/model/IntermediacaoModel.php';

$model = new IntermediacaoModel();

$rows = [
    // Data, Mercado, Sub_Mercado, Tipo_Operacao, Ativo, CNPJ, Quantidade, Preco_Unitario, Valor_Bruto, Taxa_Liquidacao, Taxa_Emolumentos, ISS, IRRF, Outras_Despesas, Valor_Liquido, Corretagem, Nome_Corretora, Codigo_Cliente, Descricao_Ativo, Custo_Operacional, Custo_Financ, Ajuste_Op, Total_Operacao
    ['2025-10-01','RF','Privado','VENDA','LCA','00000000000191',14,100.0,1400,0.01,0.005,0,0,0,1390,0,'Corretora X','ACC123','LCA 01',0,0,0,1400],
    ['2025-10-01','RF','Privado','VENDA','LCA','00000000000191',1,100.0,100,0.01,0.005,0,0,0,99,0,'Corretora X','ACC123','LCA 02',0,0,0,100],
    ['2025-10-02','RF','Privado','VENDA','LCA','00000000000191',5,100.0,500,0.01,0.005,0,0,0,495,0,'Corretora Y','ACC456','LCA 03',0,0,0,500],
];

echo "Inserindo amostra...\n";
$res = $model->insertBatch($rows);
var_dump($res);

echo "Agregados:\n";
$ag = $model->getNegotiableAggregates();
print_r($ag);

?>