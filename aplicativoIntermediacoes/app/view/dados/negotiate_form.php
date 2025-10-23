<script src="https://cdn.tailwindcss.com"></script>
<script type="module">
    // Firebase Imports
    import { initializeApp } from "https://www.gstatic.com/firebasejs/11.6.1/firebase-app.js";
    import { getAuth, signInAnonymously, signInWithCustomToken, onAuthStateChanged } from "https://www.gstatic.com/firebasejs/11.6.1/firebase-auth.js";
    import { getFirestore, collection, addDoc, serverTimestamp, setLogLevel } from "https://www.gstatic.com/firebasejs/11.6.1/firebase-firestore.js";

    setLogLevel('Debug'); // Habilita logs de debug do Firestore

    let db;
    let auth;
    let userId;

    // Configurações e Variáveis Globais (MANDATÓRIO)
    const appId = typeof __app_id !== 'undefined' ? __app_id : 'default-app-id';
    const firebaseConfig = typeof __firebase_config !== 'undefined' ? JSON.parse(__firebase_config) : {};
    const initialAuthToken = typeof __initial_auth_token !== 'undefined' ? __initial_auth_token : null;

    // Inicialização do Firebase e Autenticação
    window.initializeFirebase = async function() {
        try {
            const app = initializeApp(firebaseConfig);
            auth = getAuth(app);
            db = getFirestore(app);

            if (initialAuthToken) {
                await signInWithCustomToken(auth, initialAuthToken);
            } else {
                await signInAnonymously(auth);
            }

            onAuthStateChanged(auth, (user) => {
                if (user) {
                    userId = user.uid;
                    console.log("Firebase Auth OK. User ID:", userId);
                    document.getElementById('negotiate-form').addEventListener('submit', handleFormSubmit);
                } else {
                    console.error("Autenticação falhou.");
                    // Implementar modal de erro se autenticação falhar
                }
            });
        } catch (e) {
            console.error("Erro na inicialização do Firebase:", e);
        }
    };

    // --- Funções de Formatação e Unformatação (incluindo /100) ---

    // Formata um valor numérico para moeda brasileira (R$) com divisão por 100
    function formatCurrency(value) {
        const scaledValue = (parseFloat(value) || 0) / 100;
        return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(scaledValue);
    }

    // Formata um valor numérico para percentual (após divisão por 100)
    function formatRate(value) {
        const scaledValue = (parseFloat(value) || 0) / 100;
        return (scaledValue).toFixed(4).replace('.', ',') + ' %';
    }

    // Remove a formatação de moeda e converte para float
    function unformatCurrency(displayValue) {
        if (!displayValue) return 0;
        // Remove R$, pontos e substitui vírgula por ponto
        const cleanValue = displayValue.replace(/[R$\s]/g, '').replace(/\./g, '').replace(',', '.');
        return parseFloat(cleanValue) || 0;
    }
    
    // Remove o percentual e converte para float
    function unformatRate(displayValue) {
        if (!displayValue) return 0;
        return parseFloat(displayValue.replace('%', '').replace(',', '.')) || 0;
    }

    // --- Lógica de Cálculo Dinâmico ---

    window.updateCalculations = function() {
        // Inputs Principais
        const qNegociada = parseFloat(document.getElementById('quantidade_negociada').value) || 0;
        const vBrutoImportado = parseFloat(document.getElementById('valor_bruto_importado').value) || 0; // Valor RAW (x100)
        
        // Vendedor Inputs
        const taxaSaida = unformatRate(document.getElementById('taxa_saida').value);
        const vBrutoSaida = unformatCurrency(document.getElementById('valor_bruto_saida').value);
        const vLiquidoSaida = unformatCurrency(document.getElementById('valor_liquido_saida').value);

        // Comprador Inputs
        const vBrutoEntrada = unformatCurrency(document.getElementById('valor_bruto_entrada').value);

        // Assessor Input
        const vPlataforma = unformatCurrency(document.getElementById('valor_plataforma').value);

        // --- CÁLCULOS DO VENDEDOR ---

        // Preço Unitário de Saída = Valor Líquido Saída / Quantidade
        let precoUnitarioSaida = 0;
        if (qNegociada > 0) {
            precoUnitarioSaida = vLiquidoSaida / qNegociada;
        }
        document.getElementById('preco_unitario_saida').value = formatCurrency(precoUnitarioSaida * 100);

        // Ganho = Valor Líquido Saída - Valor Bruto Importado (corrigido)
        // OBS: Aqui usamos vBrutoImportado / 100 para desescalar o valor do banco
        const ganho = vLiquidoSaida - (vBrutoImportado / 100);
        document.getElementById('ganho_saida').value = formatCurrency(ganho * 100);
        document.getElementById('ganho_saida').classList.toggle('text-green-600', ganho >= 0);
        document.getElementById('ganho_saida').classList.toggle('text-red-600', ganho < 0);

        // Rentabilidade = Ganho / Valor da Plataforma (se vPlataforma > 0)
        let rentabilidadeSaida = 0;
        if (vPlataforma > 0) {
            rentabilidadeSaida = ganho / vPlataforma;
        }
        document.getElementById('rentabilidade_saida').value = (rentabilidadeSaida * 100).toFixed(2).replace('.', ',') + ' %';


        // --- CÁLCULOS DO COMPRADOR ---

        // Preço Unitário de Entrada = Valor Bruto de Entrada / Quantidade
        let precoUnitarioEntrada = 0;
        if (qNegociada > 0) {
            precoUnitarioEntrada = vBrutoEntrada / qNegociada;
        }
        document.getElementById('preco_unitario_entrada').value = formatCurrency(precoUnitarioEntrada * 100);

        // --- CÁLCULOS DO ASSESSOR ---

        // Corretagem = Valor Bruto Entrada - Valor Bruto Saída
        const corretagemAssessor = vBrutoEntrada - vBrutoSaida;
        document.getElementById('corretagem_assessor').value = formatCurrency(corretagemAssessor * 100);
        document.getElementById('corretagem_assessor').classList.toggle('text-green-600', corretagemAssessor >= 0);
        document.getElementById('corretagem_assessor').classList.toggle('text-red-600', corretagemAssessor < 0);

        // ROA = Corretagem / Valor Bruto Saída (se vBrutoSaida > 0)
        let roaAssessor = 0;
        if (vBrutoSaida > 0) {
            roaAssessor = corretagemAssessor / vBrutoSaida;
        }
        document.getElementById('roa_assessor').value = (roaAssessor * 100).toFixed(2).replace('.', ',') + ' %';
    };

    // --- Submissão para o Firestore ---

    async function handleFormSubmit(event) {
        event.preventDefault();
        const button = document.querySelector('button[type="submit"]');
        button.disabled = true;
        button.textContent = 'Processando...';

        try {
            // Obter todos os campos do formulário
            const formData = new FormData(event.target);
            const data = {};
            formData.forEach((value, key) => {
                data[key] = value;
            });
            
            // Adicionar campos calculados (precisam ser recalculados para garantir consistência)
            window.updateCalculations(); 
            data.preco_unitario_saida = unformatCurrency(document.getElementById('preco_unitario_saida').value);
            data.ganho_saida = unformatCurrency(document.getElementById('ganho_saida').value);
            data.rentabilidade_saida = unformatRate(document.getElementById('rentabilidade_saida').value);
            data.preco_unitario_entrada = unformatCurrency(document.getElementById('preco_unitario_entrada').value);
            data.corretagem_assessor = unformatCurrency(document.getElementById('corretagem_assessor').value);
            data.roa_assessor = unformatRate(document.getElementById('roa_assessor').value);
            
            // Adicionar metadados da transação
            data.transacao_id = crypto.randomUUID();
            data.data_transacao = serverTimestamp();
            data.usuario_id = userId;
            
            // Salvar no Firestore
            const collectionPath = `artifacts/${appId}/users/${userId}/negociacoes`;
            await addDoc(collection(db, collectionPath), data);
            
            alertModal('Sucesso!', 'A negociação foi registrada com sucesso no Firestore.', 'success');
            
            // Redirecionar após sucesso (opcional, aqui apenas mostra o sucesso)
            // setTimeout(() => window.location.href = 'index.php?controller=dados&action=visualizar', 2000);

        } catch (error) {
            console.error("Erro ao processar e salvar negociação:", error);
            alertModal('Erro!', `Falha ao salvar a negociação. Detalhes: ${error.message}`, 'error');
        } finally {
            button.disabled = false;
            button.textContent = 'Processar Negociação';
        }
    }
    
    // Simples Modal de Alerta (substituto do alert())
    function alertModal(title, message, type) {
        const modal = document.getElementById('custom-modal');
        const modalTitle = document.getElementById('modal-title');
        const modalMessage = document.getElementById('modal-message');
        const header = document.getElementById('modal-header');

        modalTitle.textContent = title;
        modalMessage.textContent = message;

        header.className = 'px-4 py-3 text-lg font-bold text-white rounded-t-lg';

        if (type === 'success') {
            header.classList.add('bg-green-600');
        } else if (type === 'error') {
            header.classList.add('bg-red-600');
        } else {
            header.classList.add('bg-blue-600');
        }

        modal.classList.remove('hidden');
    }

    // Inicializa o Firebase ao carregar a página
    window.onload = initializeFirebase;
    
    // Adiciona listener para recalcular ao alterar qualquer campo relevante
    document.addEventListener('input', function(e) {
        const relevantFields = ['quantidade_negociada', 'taxa_saida', 'valor_bruto_saida', 'valor_liquido_saida', 'taxa_entrada', 'valor_bruto_entrada', 'valor_plataforma'];
        if (relevantFields.includes(e.target.id)) {
            window.updateCalculations();
        }
    });
});
</script>

<!-- Estilização Principal -->
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap');
    body {
        font-family: 'Inter', sans-serif;
    }
    .input-field {
        width: 100%;
        padding: 10px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        transition: border-color 0.15s ease-in-out;
    }
    .input-field:focus {
        border-color: #3b82f6;
        outline: none;
    }
    .read-only-field {
        background-color: #f3f4f6;
        font-weight: 600;
        cursor: default;
    }
    .text-positive {
        color: #10b981; /* Verde esmeralda */
    }
    .text-negative {
        color: #ef4444; /* Vermelho */
    }
</style>

<main class="p-4 md:p-8 max-w-4xl mx-auto">
    <h2 class="text-3xl font-bold text-gray-800 mb-6 border-b pb-2">Formulário de Negociação</h2>

    <?php 
        // Desescalando os valores numéricos para exibição no cabeçalho
        $vl_bruto_display = number_format(($data['valor_bruto'] ?? 0) / 100, 2, ',', '.');
        $vl_liquido_display = number_format(($data['valor_liquido'] ?? 0) / 100, 2, ',', '.');
        $taxa_display = number_format(($data['taxa_emissao'] ?? 0) / 100, 4, ',', '.') . ' %';
        $quantidade_max = htmlspecialchars($data['quantidade'] ?? 0);
    ?>

    <div id="negotiation-summary" class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 rounded-lg shadow-md">
        <p class="text-sm text-blue-700 font-semibold">
            Detalhes do Ativo:
        </p>
        <p class="mt-1 text-gray-800">
            <span class="font-bold">Cliente:</span> <?= htmlspecialchars($data['nome'] ?? 'N/A') ?> (Conta: <?= htmlspecialchars($data['conta'] ?? 'N/A') ?>)
        </p>
        <p class="mt-1 text-gray-800">
            <span class="font-bold">Produto/Tipo:</span> <?= htmlspecialchars($data['produto'] ?? 'N/A') ?> / <?= htmlspecialchars($data['estrategia'] ?? 'N/A') ?>
        </p>
        <div class="flex flex-wrap gap-x-8 mt-2">
            <p>
                <span class="font-bold text-sm">Taxa Original:</span> <span class="text-blue-600"><?= $taxa_display ?></span>
            </p>
            <p>
                <span class="font-bold text-sm">Valor Líquido:</span> <span class="text-green-600">R$ <?= $vl_liquido_display ?></span>
            </p>
            <p>
                <span class="font-bold text-sm">Qtd. Disponível:</span> <span class="text-gray-800"><?= number_format($data['quantidade'] ?? 0, 0, ',', '.') ?></span>
            </p>
        </div>
    </div>

    <form id="negotiate-form" action="javascript:void(0)" class="bg-white shadow-xl rounded-xl p-6 md:p-8 border border-gray-200">
        <!-- Campos Ocultos (Passam dados brutos do DB para JS) -->
        <input type="hidden" name="conta" value="<?= htmlspecialchars($data['conta'] ?? '') ?>">
        <input type="hidden" name="nome" value="<?= htmlspecialchars($data['nome'] ?? '') ?>">
        <input type="hidden" name="produto" value="<?= htmlspecialchars($data['produto'] ?? '') ?>">
        <input type="hidden" name="estrategia" value="<?= htmlspecialchars($data['estrategia'] ?? '') ?>">
        <input type="hidden" id="valor_bruto_importado" name="valor_bruto_importado" value="<?= htmlspecialchars($data['valor_bruto'] ?? 0) ?>">
        <input type="hidden" id="quantidade_maxima" value="<?= $quantidade_max ?>">

        <!-- Quantidade a Negociar -->
        <div class="mb-6">
            <label for="quantidade_negociada" class="block text-sm font-medium text-gray-700 mb-1">
                Quantidade a Negociar (Máx: <?= number_format($data['quantidade'] ?? 0, 0, ',', '.') ?>):
            </label>
            <input type="number" id="quantidade_negociada" name="quantidade_negociada" min="1" max="<?= $quantidade_max ?>" value="<?= $quantidade_max ?>" 
                   class="input-field text-xl font-bold border-indigo-500" required>
        </div>

        <hr class="my-6 border-gray-300">

        <!-- Seção VENDEDOR (Saída) -->
        <h3 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">Vendedor (Saída)</h3>
        <div class="grid md:grid-cols-3 gap-4 mb-6">
            <div class="md:col-span-1">
                <label for="taxa_saida" class="block text-sm font-medium text-gray-700 mb-1">Taxa de Saída (%):</label>
                <input type="text" id="taxa_saida" name="taxa_saida" placeholder="1.25" class="input-field">
            </div>
            <div class="md:col-span-1">
                <label for="valor_bruto_saida" class="block text-sm font-medium text-gray-700 mb-1">Valor Bruto de Saída (R$):</label>
                <input type="text" id="valor_bruto_saida" name="valor_bruto_saida" placeholder="10.000,00" class="input-field">
            </div>
            <div class="md:col-span-1">
                <label for="valor_liquido_saida" class="block text-sm font-medium text-gray-700 mb-1">Valor Líquido de Saída (R$):</label>
                <input type="text" id="valor_liquido_saida" name="valor_liquido_saida" placeholder="9.800,00" class="input-field" required>
            </div>
        </div>

        <!-- Resultados VENDEDOR -->
        <div class="grid md:grid-cols-3 gap-4 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Preço Unitário (Vendedor):</label>
                <input type="text" id="preco_unitario_saida" class="input-field read-only-field" readonly value="R$ 0,00">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Ganho (Vendedor):</label>
                <input type="text" id="ganho_saida" class="input-field read-only-field text-xl font-bold" readonly value="R$ 0,00">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Rentabilidade (Ganho / Plataforma):</label>
                <input type="text" id="rentabilidade_saida" class="input-field read-only-field" readonly value="0,00 %">
            </div>
        </div>

        <hr class="my-6 border-gray-300">

        <!-- Seção COMPRADOR (Entrada) -->
        <h3 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">Comprador (Entrada)</h3>
        <div class="grid md:grid-cols-4 gap-4 mb-6">
            <div>
                <label for="conta_entrada" class="block text-sm font-medium text-gray-700 mb-1">Conta Comprador:</label>
                <input type="text" id="conta_entrada" name="conta_entrada" placeholder="123456" class="input-field" required>
            </div>
            <div>
                <label for="nome_entrada" class="block text-sm font-medium text-gray-700 mb-1">Nome Comprador:</label>
                <input type="text" id="nome_entrada" name="nome_entrada" placeholder="João da Silva" class="input-field" required>
            </div>
            <div>
                <label for="taxa_entrada" class="block text-sm font-medium text-gray-700 mb-1">Taxa de Entrada (%):</label>
                <input type="text" id="taxa_entrada" name="taxa_entrada" placeholder="1.00" class="input-field">
            </div>
            <div>
                <label for="valor_bruto_entrada" class="block text-sm font-medium text-gray-700 mb-1">Valor Bruto de Entrada (R$):</label>
                <input type="text" id="valor_bruto_entrada" name="valor_bruto_entrada" placeholder="10.100,00" class="input-field" required>
            </div>
        </div>

        <!-- Resultados COMPRADOR -->
        <div class="grid md:grid-cols-4 gap-4 mb-6">
            <div class="md:col-span-1">
                <label class="block text-sm font-medium text-gray-500 mb-1">Preço Unitário (Comprador):</label>
                <input type="text" id="preco_unitario_entrada" class="input-field read-only-field" readonly value="R$ 0,00">
            </div>
        </div>

        <hr class="my-6 border-gray-300">

        <!-- Seção ASSESSOR -->
        <h3 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-2">Assessor e Plataforma</h3>
        <div class="grid md:grid-cols-3 gap-4 mb-6">
            <div>
                <label for="valor_plataforma" class="block text-sm font-medium text-gray-700 mb-1">Valor da Plataforma (R$):</label>
                <input type="text" id="valor_plataforma" name="valor_plataforma" placeholder="50,00" class="input-field" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">Corretagem (Entrada - Saída):</label>
                <input type="text" id="corretagem_assessor" class="input-field read-only-field text-xl font-bold" readonly value="R$ 0,00">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500 mb-1">ROA (Corretagem / Bruto Saída):</label>
                <input type="text" id="roa_assessor" class="input-field read-only-field" readonly value="0,00 %">
            </div>
        </div>

        <!-- Botões de Ação -->
        <div class="mt-8 flex justify-end space-x-4">
            <a href="index.php?controller=dados&action=visualizar" 
               class="px-6 py-3 bg-gray-500 text-white font-semibold rounded-lg shadow-md hover:bg-gray-600 transition">
                Cancelar
            </a>
            <button type="submit" class="px-6 py-3 bg-green-600 text-white font-semibold rounded-lg shadow-md hover:bg-green-700 transition disabled:opacity-50">
                Processar Negociação
            </button>
        </div>
    </form>
    
    <!-- Modal Customizado para Feedback (Substitui alert()) -->
    <div id="custom-modal" class="fixed inset-0 bg-gray-600 bg-opacity-75 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-2xl max-w-sm w-full mx-4">
            <div id="modal-header" class="px-4 py-3 text-lg font-bold text-white rounded-t-lg">
                <h3 id="modal-title">Título</h3>
            </div>
            <div class="p-4">
                <p id="modal-message" class="text-gray-700"></p>
                <div class="mt-4 flex justify-end">
                    <button onclick="document.getElementById('custom-modal').classList.add('hidden')" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        Fechar
                    </button>
                </div>
            </div>
        </div>
    </div>
</main>
