<main>
	<h2>Resultado da Negociação</h2>

	<?php if (!empty($resultData)): ?>
		<div class="negotiation-summary">
			<p><strong>Conta:</strong> <?= htmlspecialchars($resultData['conta']) ?></p>
			<p><strong>Cliente:</strong> <?= htmlspecialchars($resultData['cliente']) ?></p>
			<p><strong>Tipo:</strong> <?= htmlspecialchars($resultData['tipo']) ?></p>
			<p><strong>Quantidade negociada:</strong> <?= number_format($resultData['quantidade_negociada'] ?? ($resultData['quantidade'] ?? 0), 0, ',', '.') ?></p>
			<hr class="form-divider">
			<p><strong>Valor Bruto Saída:</strong> R$ <?= number_format($resultData['valor_bruto_saida'], 2, ',', '.') ?></p>
			<p><strong>Taxa de Saída (%):</strong> <?= number_format($resultData['taxa_saida'], 4, ',', '.') ?> %</p>
			<p><strong>Valor Bruto Entrada:</strong> R$ <?= number_format($resultData['valor_bruto_entrada'], 2, ',', '.') ?></p>
			<p><strong>Taxa de Entrada (%):</strong> <?= number_format($resultData['taxa_entrada'], 4, ',', '.') ?> %</p>
			<hr class="form-divider">
			<p><strong>Corretagem (R$):</strong> R$ <?= number_format($resultData['corretagem'], 2, ',', '.') ?></p>
			<p><strong>ROA (%):</strong> <?= number_format($resultData['roa'], 4, ',', '.') ?> %</p>
			<hr class="form-divider">
			<p><strong>Retorno ao Vendedor (R$):</strong> R$ <?= number_format($resultData['retorno_vendedor_valor'] ?? 0, 2, ',', '.') ?></p>
			<p><strong>Retorno ao Vendedor (% do Bruto):</strong> <?= number_format($resultData['retorno_vendedor_pct'] ?? 0, 4, ',', '.') ?> %</p>
			<hr class="form-divider">
			<p><strong>Operador:</strong> <?= htmlspecialchars($resultData['operator']['username'] ?? 'Desconhecido') ?></p>
			<?php if (!empty($resultData['saved_id'])): ?>
				<p class="message success mt-4">Negociação salva (ID: <?= (int)$resultData['saved_id'] ?>)</p>
			<?php endif; ?>
		</div>
	<?php else: ?>
		<div class="message warning">Nenhum dado de resultado para exibir.</div>
	<?php endif; ?>

	<div class="form-actions">
		<a href="index.php?controller=dados&action=visualizar" class="btn btn-primary">Voltar à Visualização</a>
	</div>
</main>
