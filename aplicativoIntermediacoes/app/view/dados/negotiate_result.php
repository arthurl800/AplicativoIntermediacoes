<main>
	<h2>Resultado da Negociação</h2>

	<?php if (!empty($resultData)): ?>
		<div style="background:#f8f9fa;padding:12px;border-radius:6px;border:1px solid #e9ecef;">
			<p><strong>Conta:</strong> <?= htmlspecialchars($resultData['conta']) ?></p>
			<p><strong>Cliente:</strong> <?= htmlspecialchars($resultData['cliente']) ?></p>
			<p><strong>Tipo:</strong> <?= htmlspecialchars($resultData['tipo']) ?></p>
			<p><strong>Quantidade negociada:</strong> <?= number_format($resultData['quantidade_negociada'] ?? ($resultData['quantidade'] ?? 0), 0, ',', '.') ?></p>
			<hr>
			<p><strong>Valor Bruto Saída:</strong> R$ <?= number_format($resultData['valor_bruto_saida'], 2, ',', '.') ?></p>
			<p><strong>Taxa de Saída (%):</strong> <?= number_format($resultData['taxa_saida'], 4, ',', '.') ?> %</p>
			<p><strong>Valor Bruto Entrada:</strong> R$ <?= number_format($resultData['valor_bruto_entrada'], 2, ',', '.') ?></p>
			<p><strong>Taxa de Entrada (%):</strong> <?= number_format($resultData['taxa_entrada'], 4, ',', '.') ?> %</p>
			<hr>
			<p><strong>Corretagem (R$):</strong> R$ <?= number_format($resultData['corretagem'], 2, ',', '.') ?></p>
			<p><strong>ROA (%):</strong> <?= number_format($resultData['roa'], 4, ',', '.') ?> %</p>
			<hr>
			<p><strong>Retorno ao Vendedor (R$):</strong> R$ <?= number_format($resultData['retorno_vendedor_valor'], 2, ',', '.') ?></p>
			<p><strong>Retorno ao Vendedor (% do Bruto):</strong> <?= number_format($resultData['retorno_vendedor_pct'], 4, ',', '.') ?> %</p>
			<hr>
			<p><strong>Operador:</strong> <?= htmlspecialchars($resultData['operator']['username'] ?? 'Desconhecido') ?></p>
			<?php if (!empty($resultData['saved_id'])): ?>
				<p style="margin-top:8px;color:#155724;background:#d4edda;padding:8px;border-radius:4px;">Negociação salva (ID: <?= (int)$resultData['saved_id'] ?>)</p>
			<?php endif; ?>
		</div>
	<?php else: ?>
		<div style="padding:12px;background:#fff3cd;border-radius:6px;border:1px solid #ffeeba;">Nenhum dado de resultado para exibir.</div>
	<?php endif; ?>

	<div style="margin-top:16px;">
		<a href="index.php?controller=dados&action=visualizar" style="text-decoration:none;background:#007bff;color:white;padding:8px 12px;border-radius:4px;">Voltar à Visualização</a>
	</div>
</main>
