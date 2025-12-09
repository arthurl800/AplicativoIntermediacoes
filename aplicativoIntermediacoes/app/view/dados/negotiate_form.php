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
    
    // Referências DOM (disponíveis logo na carga do script)
    const negotiateForm = document.getElementById('negotiate-form');
    let submitButton; // Será definido em initializeFirebase

    // --- Funções de Formatação e Unformatação ---
    
    // Formata um valor numérico para moeda brasileira (R$) (espera valor desescalado, ex: 10000.50)
    function formatCurrency(value) {
        const scaledValue = parseFloat(value) || 0;
        return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(scaledValue);
    }
    
    // Formata um valor numérico para percentual (espera valor em base 100, ex: 5 para 5%)
    function formatRateDisplay(value) {
        const scaledValue = parseFloat(value) || 0;
        // Limita a 2 casas decimais para o display (pode ajustar)
        return scaledValue.toFixed(2).replace('.', ',') + ' %'; 
    }

    // Remove a formatação de moeda e converte para float (o valor puro, desescalado)
    function unformatCurrency(displayValue) {
        if (!displayValue) return 0;
        // Remove R$, espaços, pontos de milhar e substitui vírgula decimal por ponto
        const cleanValue = displayValue.replace(/[R$\s]/g, '').replace(/\./g, '').replace(',', '.');
        return parseFloat(cleanValue) || 0;
    }
    
    // Remove o percentual e converte para float (o valor em base 100)
    function unformatRate(displayValue) {
        if (!displayValue) return 0;
        // Remove %, espaços e substitui vírgula por ponto
        return parseFloat(displayValue.replace('%', '').replace(/\s/g, '').replace(',', '.')) || 0;
    }

    // --- Lógica de Cálculo Dinâmico ---
    
    window.updateCalculations = function() {
        if (!negotiateForm) return;

        // Inputs Principais
        const qNegociada = parseFloat(document.getElementById('quantidade_negociada').value) || 0;
        // vBrutoImportado é o valor RAW (x100) vindo do PHP. Precisa ser desescalado para o cálculo.
        const vBrutoImportadoRaw = parseFloat(document.getElementById('valor_bruto_importado').value) || 0; 
        const vBrutoImportadoDesescalado = vBrutoImportadoRaw / 100;
        
        // Vendedor Inputs (valores desescalados em R$)
        // Aqui é onde o valor do input é lido. Se for R$ 10.000,00, será lido como 10000.00
        const taxaSaida = unformatRate(document.getElementById('taxa_saida').value);
        const vBrutoSaida = unformatCurrency(document.getElementById('valor_bruto_saida').value);
        const vLiquidoSaida = unformatCurrency(document.getElementById('valor_liquido_saida').value);

        // Comprador Inputs (valores desescalados em R$)
        const vBrutoEntrada = unformatCurrency(document.getElementById('valor_bruto_entrada').value);

        // Assessor Input (valor desescalado em R$)
        const vPlataforma = unformatCurrency(document.getElementById('valor_plataforma').value);

        // --- CÁLCULOS DO VENDEDOR ---

        // Preço Unitário de Saída = Valor Líquido Saída / Quantidade
        let precoUnitarioSaida = 0;
        if (qNegociada > 0) {
            precoUnitarioSaida = vLiquidoSaida / qNegociada; 
        }
        document.getElementById('preco_unitario_saida').value = formatCurrency(precoUnitarioSaida);
        
        // Ganho = Valor Líquido Saída - Valor Bruto Importado (desescalado)
        const ganho = vLiquidoSaida - vBrutoImportadoDesescalado;
        document.getElementById('ganho_saida').value = formatCurrency(ganho);
        document.getElementById('ganho_saida').classList.toggle('text-green-600', ganho >= 0);
        document.getElementById('ganho_saida').classList.toggle('text-red-600', ganho < 0);

        // Rentabilidade = Ganho / Valor da Plataforma (se vPlataforma > 0)
        let rentabilidadeSaida = 0;
        if (vPlataforma > 0) {
            rentabilidadeSaida = (ganho / vPlataforma) * 100; // Resultado em % (base 100) 
        }
        document.getElementById('rentabilidade_saida').value = formatRateDisplay(rentabilidadeSaida);


        // --- CÁLCULOS DO COMPRADOR ---

        // Preço Unitário de Entrada = Valor Bruto de Entrada / Quantidade
        let precoUnitarioEntrada = 0;
        if (qNegociada > 0) {
            precoUnitarioEntrada = vBrutoEntrada / qNegociada; 
        }
        document.getElementById('preco_unitario_entrada').value = formatCurrency(precoUnitarioEntrada);

        // --- CÁLCULOS DO ASSESSOR ---

        // Corretagem = Valor Bruto Entrada - Valor Bruto Saída
        const corretagemAssessor = vBrutoEntrada - vBrutoSaida;
        document.getElementById('corretagem_assessor').value = formatCurrency(corretagemAssessor);
        document.getElementById('corretagem_assessor').classList.toggle('text-green-600', corretagemAssessor >= 0);
        document.getElementById('corretagem_assessor').classList.toggle('text-red-600', corretagemAssessor < 0);

        // ROA = Corretagem / Valor Bruto Saída (se vBrutoSaida > 0)
        let roaAssessor = 0;
        if (vBrutoSaida > 0) {
            roaAssessor = (corretagemAssessor / vBrutoSaida) * 100; // Resultado em % (base 100) 
        }
        document.getElementById('roa_assessor').value = formatRateDisplay(roaAssessor);
    };

    // --- Submissão para o Firestore ---

    async function handleFormSubmit(event) {
        event.preventDefault();
        
        console.log("Submit button clicked. Checking auth state...");
        
        // CRÍTICO: Verifica se o Firebase está pronto
        if (!submitButton || !userId || !db) {
            console.error("ERRO: Firebase ou UserID não definidos. Abortando submissão.");
            alertModal('Aguarde', 'Aguarde o carregamento completo do sistema de autenticação e banco de dados.', 'info');
            return;
        }
        
        submitButton.disabled = true;
        submitButton.textContent = 'Processando...';

        try {
            // Re-executa os cálculos para garantir que os valores a serem salvos estão atualizados
            window.updateCalculations(); 
            
            // Obter todos os campos do formulário (incluindo inputs hidden)
            const formData = new FormData(event.target);
            const data = {};
            formData.forEach((value, key) => {
                data[key] = value;
            });
            
            // Adicionar campos calculados e garantir que são números (desescalados)
            // OBS: O Firestore deve receber valores desescalados em R$ ou em % (base 100)
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
            
            console.log("Attempting to save data to Firestore:", data);

            // Salvar no Firestore
            const collectionPath = `artifacts/${appId}/users/${userId}/negociacoes`;
            await addDoc(collection(db, collectionPath), data);
            
            alertModal('Sucesso!', 'A negociação foi registrada com sucesso no Firestore.', 'success');
            
        } catch (error) {
            console.error("ERRO FATAL: Falha ao processar e salvar negociação:", error);
            alertModal('Erro!', `Falha ao salvar a negociação. Detalhes: ${error.message}`, 'error');
        } finally {
            // Garante que o botão seja reativado após o processamento (sucesso ou falha)
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.textContent = 'Processar Negociação';
            }
        }
    }

    // Inicialização do Firebase e Autenticação
    window.initializeFirebase = async function() {
        try {
            const app = initializeApp(firebaseConfig);
            auth = getAuth(app);
            db = getFirestore(app);
            
            // Define o botão de submissão o mais cedo possível
            submitButton = document.querySelector('button[type="submit"]');

            if (initialAuthToken) {
                await signInWithCustomToken(auth, initialAuthToken);
            } else {
                await signInAnonymously(auth);
            }
            
            // Monitora o estado de autenticação (não muda o comportamento do submit se o formulário pedir envio ao servidor)
            onAuthStateChanged(auth, (user) => {
                if (user) {
                    userId = user.uid;
                    console.log("Firebase Auth OK. User ID:", userId);

                    // Se o formulário estiver configurado para submissão ao servidor (campo hidden server_side=1),
                    // não anexamos o listener do Firebase que previne o envio padrão.
                    const serverSide = negotiateForm ? negotiateForm.querySelector('input[name="server_side"]') : null;
                    if (!serverSide) {
                        if (negotiateForm) {
                           negotiateForm.removeEventListener('submit', handleFormSubmit); // Remove duplicados
                           negotiateForm.addEventListener('submit', handleFormSubmit);
                           console.log("Form submit listener attached successfully (Firestore mode).");
                        }
                    } else {
                        console.log("Form configured for server-side submit; skipping Firestore submit handler.");
                    }

                    // EXECUTAR CÁLCULO INICIAL
                    window.updateCalculations();

                } else {
                    console.error("Autenticação falhou. Usuário não logado.");
                    if (submitButton) submitButton.disabled = true;
                }
            });
        } catch (e) {
            console.error("Erro na inicialização do Firebase:", e);
        }
    };
    
    // Simples Modal de Alerta (substituto do alert())
    function alertModal(title, message, type) {
        const modal = document.getElementById('custom-modal'); 
        const modalTitle = document.getElementById('modal-title'); 
        const modalMessage = document.getElementById('modal-message'); 
        const header = document.getElementById('modal-header'); 
        
        modalTitle.textContent = title;
        modalMessage.textContent = message;
        
        header.className = 'modal-header'; // Reset classes
        
        if (type === 'success') {
            header.classList.add('modal-header-success');
        } else if (type === 'error') {
            header.classList.add('modal-header-error');
        } else {
            header.classList.add('modal-header-info');
        }

        modal.classList.remove('hidden');
    }

    // Inicializa o Firebase ao carregar a página
    window.onload = initializeFirebase;
    
    // Adiciona listener de input no documento para recalcular ao alterar qualquer campo relevante
    document.addEventListener('input', function(e) {
        // Verifica se o evento veio de um dos campos relevantes
        const relevantIds = ['quantidade_negociada', 'taxa_saida', 'valor_bruto_saida', 'valor_liquido_saida', 'taxa_entrada', 'valor_bruto_entrada', 'valor_plataforma'];
        if (relevantIds.includes(e.target.id)) {
            window.updateCalculations();
        }
    });
</script>

<main>
    <div class="negotiation-form-container">
    <h2 class="form-section-title">Formulário de Negociação</h2>

    <?php 
        // Desescalando os valores numéricos para exibição no cabeçalho
        $vl_bruto_display = number_format(($data['valor_bruto'] ?? 0) / 100, 2, ',', '.');
        $vl_liquido_display = number_format(($data['valor_liquido'] ?? 0) / 100, 2, ',', '.');
        $taxa_display = number_format(($data['taxa_emissao'] ?? 0) / 1, 2, ',', '.') . ' %';
        $quantidade_max = htmlspecialchars($data['quantidade'] ?? 0);
    ?>
    
    <div id="negotiation-summary" class="negotiation-summary">
        <p class="negotiation-summary-item text-info">
            Detalhes do Ativo:
        </p>
        <p class="negotiation-summary-item">
            <span class="font-bold">Cliente:</span> <?= htmlspecialchars($data['nome'] ?? 'N/A') ?> (Conta: <?= htmlspecialchars($data['conta'] ?? 'N/A') ?>) 
        </p>
        <p class="negotiation-summary-item">
            <span class="font-bold">Produto/Tipo:</span> <?= htmlspecialchars($data['produto'] ?? 'N/A') ?> / <?= htmlspecialchars($data['estrategia'] ?? 'N/A') ?> 
        </p>
        <p class="negotiation-summary-item">
            <span class="font-bold">Vencimento:</span> <?= htmlspecialchars($data['vencimento'] ?? ($data['vencimento_raw'] ?? 'N/A')) ?> 
        </p>
        <div class="negotiation-summary-item-group">
            <p>
                <span class="font-bold">Taxa Original:</span> <span class="text-info"><?= $taxa_display ?></span>
            </p>
            <p>
                <span class="font-bold">Valor Líquido:</span> <span class="text-success">R$ <?= $vl_liquido_display ?></span>
            </p>
            <p>
                <span class="font-bold">Qtd. Disponível:</span> <span><?= number_format($data['quantidade'] ?? 0, 0, ',', '.') ?></span>
            </p>
        </div>
    </div>

    <form id="negotiate-form" action="index.php?controller=dados&action=process_negotiation" method="POST" class="bg-white shadow-xl rounded-xl p-6 md:p-8 border border-gray-200">
        <!-- Campos Ocultos (Passam dados brutos do DB para JS) -->
        <input type="hidden" name="conta" value="<?= htmlspecialchars($data['conta'] ?? '') ?>">
        <input type="hidden" name="nome" value="<?= htmlspecialchars($data['nome'] ?? '') ?>">
    <input type="hidden" name="cliente" value="<?= htmlspecialchars($data['nome'] ?? '') ?>">
        <input type="hidden" name="ativo" value="<?= htmlspecialchars($data['ativo'] ?? '') ?>">
        <input type="hidden" name="produto" value="<?= htmlspecialchars($data['produto'] ?? '') ?>">
    <input type="hidden" name="tipo" value="<?= htmlspecialchars($data['produto'] ?? '') ?>">
        <input type="hidden" name="estrategia" value="<?= htmlspecialchars($data['estrategia'] ?? '') ?>">
    <input type="hidden" name="emissor" value="<?= htmlspecialchars($data['emissor'] ?? ($data['CNPJ'] ?? '')) ?>">
    <input type="hidden" name="vencimento" value="<?= htmlspecialchars($data['vencimento_raw'] ?? ($data['vencimento'] ?? '')) ?>">
        <input type="hidden" id="valor_bruto_importado" name="valor_bruto_importado" value="<?= htmlspecialchars($data['valor_bruto'] ?? 0) ?>">
        <!-- Indica que o formulário deve ser submetido ao servidor (desabilita handler Firestore) -->
        <input type="hidden" name="server_side" value="1">
        <input type="hidden" id="quantidade_maxima" value="<?= $quantidade_max ?>">

        <!-- Quantidade a Negociar -->
        <div class="form-group">
            <label for="quantidade_negociada">
                Quantidade a Negociar (Máx: <?= number_format($data['quantidade'] ?? 0, 0, ',', '.') ?>):
            </label>
            <input type="number" id="quantidade_negociada" name="quantidade_negociada" min="1" max="<?= $quantidade_max ?>" value="<?= $quantidade_max ?>" 
                   class="input-field" required>
        </div>

        <hr class="form-divider">

        <!-- Seção VENDEDOR (Saída) -->
        <h3 class="form-section-title">Vendedor (Saída)</h3>
        <div class="form-grid">
            <div class="form-col">
                <label for="taxa_saida">Taxa de Saída (%):</label>
                <input type="text" id="taxa_saida" name="taxa_saida" placeholder="1.25" class="input-field">
            </div>
            <div class="form-col">
                <label for="valor_bruto_saida">Valor Bruto de Saída (R$):</label>
                <input type="text" id="valor_bruto_saida" name="valor_bruto_saida" placeholder="10.000,00" class="input-field">
            </div>
            <div class="form-col">
                <label for="valor_liquido_saida">Valor Líquido de Saída (R$):</label>
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
                <label for="conta_entrada" class="block text-sm font-medium text-gray-700 mb-1" style="background-color: #ccf3caff;">Conta Comprador:</label>
                <input type="text" id="conta_entrada" name="conta_entrada" placeholder="123456" class="input-field" required>
            </div>
            <div>
                <label for="nome_entrada" class="block text-sm font-medium text-gray-700 mb-1" style="background-color: #ccf3caff;">Nome Comprador:</label>
                <input type="text" id="nome_entrada" name="nome_entrada" placeholder="João da Silva" class="input-field" required>
            </div>
            <div>
                <label for="taxa_entrada" class="block text-sm font-medium text-gray-700 mb-1">Taxa de Entrada (%):</label>
                <input type="text" id="taxa_entrada" name="taxa_entrada" placeholder="1.00" class="input-field">
            </div>
            <div>
                <label for="valor_bruto_entrada" class="block text-sm font-medium text-gray-700 mb-1">Valor Bruto Entrada (R$):</label>
                <input type="text" id="valor_bruto_entrada" name="valor_bruto_entrada" placeholder="10.100,00" class="input-field" required>
            </div>
        </div>

        <!-- Resultados COMPRADOR -->
        <div class="grid md:grid-cols-4 gap-4 mb-6">
            <div class="md:col-span-1">
                <label class="form-label-readonly">Preço Unitário (Comprador):</label>
                <input type="text" id="preco_unitario_entrada" class="input-field read-only-field" readonly value="R$ 0,00">
            </div>
        </div>

        <hr class="form-divider">

        <!-- Seção ASSESSOR -->
        <h3 class="form-section-title">Assessor e Plataforma</h3>
        <div class="form-grid">
            <div class="form-col">
                <label for="valor_plataforma">Valor da Plataforma (R$):</label>
                <input type="text" id="valor_plataforma" name="valor_plataforma" placeholder="50,00" class="input-field" required>
            </div>
            <div class="form-col">
                <label class="form-label-readonly">Corretagem (Entrada - Saída):</label>
                <input type="text" id="corretagem_assessor" class="input-field read-only-field" readonly value="R$ 0,00">
            </div>
            <div class="form-col">
                <label class="form-label-readonly">ROA (Corretagem / Bruto Saída):</label>
                <input type="text" id="roa_assessor" class="input-field read-only-field" readonly value="0,00 %">
            </div>
        </div>

        <!-- Botões de Ação -->
        <div class="form-actions">
            <a href="index.php?controller=dados&action=visualizar" 
               class="btn btn-secondary">
                Cancelar
            </a>
            <button type="submit" class="btn btn-primary">
                Processar Negociação
            </button>
        </div>
    </form>
    
    <!-- Modal Customizado para Feedback (Substitui alert()) -->
    <div id="custom-modal" class="fixed inset-0 bg-gray-600 bg-opacity-75 hidden flex items-center justify-center z-50"> 
        <div class="modal-custom">
            <div id="modal-header" class="modal-header">
                <h3 id="modal-title">Título</h3>
            </div>
            <div class="modal-content">
                <p id="modal-message"></p>
                <div class="modal-footer">
                    <button onclick="document.getElementById('custom-modal').classList.add('hidden')" 
                            class="btn btn-info">
                        Fechar
                    </button>
                </div>
            </div>
        </div>
    </div>
</main>
