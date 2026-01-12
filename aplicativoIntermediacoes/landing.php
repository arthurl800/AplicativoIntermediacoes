<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Experimento Tecnologias - Sistema de Intermediações</title>
    <link rel="stylesheet" href="assets/css/theme.css">
    <link rel="icon" href="assets/images/favicon.ico">
    <style>
        body {
            margin: 0;
            overflow-x: hidden;
        }
        
        .landing {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        /* Hero Section */
        .hero {
            min-height: 90vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #1b5e20 0%, #2e7d32 50%, #000000 100%);
            position: relative;
            overflow: hidden;
        }
        
        .hero::before {
            content: '';
            position: absolute;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
            background-size: 50px 50px;
            animation: moveBackground 20s linear infinite;
        }
        
        @keyframes moveBackground {
            0% {
                transform: translate(0, 0);
            }
            100% {
                transform: translate(50px, 50px);
            }
        }
        
        .hero-content {
            position: relative;
            z-index: 1;
            text-align: center;
            padding: var(--space-2xl);
            max-width: 900px;
            margin: 0 auto;
        }
        
        .logo-container {
            margin-bottom: var(--space-xl);
            animation: fadeInDown 1s ease-out;
        }
        
        .logo-container img {
            max-width: 350px;
            width: 90%;
            height: auto;
            filter: drop-shadow(0 10px 30px rgba(0,0,0,0.3));
        }
        
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .hero h1 {
            color: white;
            font-size: 2.5rem;
            margin-bottom: var(--space-md);
            margin-top: 0;
            line-height: 1.2;
            text-shadow: 0 4px 20px rgba(0,0,0,0.3);
            animation: fadeInUp 1s ease-out 0.3s both;
        }
        
        .hero p {
            color: rgba(255,255,255,0.95);
            font-size: 1.125rem;
            margin-bottom: var(--space-xl);
            line-height: 1.6;
            text-shadow: 0 2px 10px rgba(0,0,0,0.2);
            animation: fadeInUp 1s ease-out 0.5s both;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .hero-buttons {
            display: flex;
            gap: var(--space-md);
            justify-content: center;
            flex-wrap: wrap;
            animation: fadeInUp 1s ease-out 0.7s both;
        }
        
        .btn-hero {
            padding: 1.25rem 2.5rem;
            font-size: 1.125rem;
            font-weight: 700;
            text-decoration: none;
            border-radius: var(--radius-xl);
            transition: all var(--transition-base);
            box-shadow: var(--shadow-xl);
        }
        
        .btn-hero-primary {
            background: white;
            color: #1b5e20;
        }
        
        .btn-hero-primary:hover {
            transform: translateY(-4px) scale(1.05);
            box-shadow: var(--shadow-2xl);
        }
        
        .btn-hero-secondary {
            background: rgba(255,255,255,0.2);
            color: white;
            border: 2px solid white;
            backdrop-filter: blur(10px);
        }
        
        .btn-hero-secondary:hover {
            background: rgba(255,255,255,0.3);
            transform: translateY(-4px) scale(1.05);
            box-shadow: var(--shadow-2xl);
        }
        
        /* Features Section */
        .features {
            padding: var(--space-3xl) var(--space-xl);
            background: var(--bg-secondary);
        }
        
        .features-container {
            max-width: 1280px;
            margin: 0 auto;
        }
        
        .section-title {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: var(--space-2xl);
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: var(--space-xl);
            margin-top: var(--space-2xl);
        }
        
        .feature-card {
            background: white;
            padding: var(--space-xl);
            border-radius: var(--radius-2xl);
            box-shadow: var(--shadow-md);
            transition: all var(--transition-base);
            border: 1px solid var(--gray-200);
        }
        
        .feature-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-2xl);
        }
        
        .feature-number {
            width: 60px;
            height: 60px;
            border-radius: var(--radius-full);
            background: var(--primary-gradient);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: var(--space-lg);
        }
        
        /* Responsividade */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 1.75rem;
            }
            
            .hero p {
                font-size: 1rem;
            }
            
            .logo-container img {
                max-width: 250px;
            }
            
            .btn-hero {
                padding: 1rem 2rem;
                font-size: 1rem;
            }
            
            .hero-content {
                padding: var(--space-lg);
            }
        }
        
        .feature-card h3 {
            color: var(--text-primary);
            font-size: 1.25rem;
            margin-bottom: var(--space-md);
        }
        
        .feature-card p {
            color: var(--text-secondary);
            line-height: 1.6;
        }
        
        /* CTA Section */
        .cta {
            background: linear-gradient(135deg, #d4af37 0%, #f9a825 100%);
            padding: var(--space-3xl) var(--space-xl);
            text-align: center;
        }
        
        .cta h2 {
            color: #000000;
            font-size: 2.5rem;
            margin-bottom: var(--space-lg);
        }
        
        .cta p {
            color: rgba(0,0,0,0.8);
            font-size: 1.25rem;
            margin-bottom: var(--space-2xl);
        }
        
        .cta .btn-hero-primary {
            background: #1b5e20;
            color: white;
        }
        
        /* Footer */
        .landing-footer {
            background: var(--gray-900);
            color: white;
            padding: var(--space-xl);
            text-align: center;
        }
        
        .landing-footer p {
            color: var(--gray-400);
            margin: 0;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2rem;
            }
            
            .hero p {
                font-size: 1rem;
            }
            
            .hero-buttons {
                flex-direction: column;
            }
            
            .btn-hero {
                width: 100%;
            }
            
            .section-title {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body class="landing">
    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <div class="logo-container">
                <img src="assets/images/logo.svg" alt="Experimento Tecnologias">
            </div>
            <h1>Sistema de Gerenciamento de Intermediações</h1>
            <p>Solução completa e moderna para controle de intermediações financeiras, negociações e auditoria em tempo real</p>
            <div class="hero-buttons">
                <a href="index.php?action=login" class="btn-hero btn-hero-primary">Acessar Sistema</a>
                <a href="#features" class="btn-hero btn-hero-secondary">Conhecer Funcionalidades</a>
            </div>
        </div>
    </section>
    
    <!-- Features Section -->
    <section id="features" class="features">
        <div class="features-container">
            <h2 class="section-title">Funcionalidades Principais</h2>
            
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-number">01</div>
                    <h3>Importação Inteligente</h3>
                    <p>Upload e processamento automático de dados via Excel com validação em tempo real e tratamento de erros.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-number">02</div>
                    <h3>Visualização Avançada</h3>
                    <p>Interface intuitiva para consulta e filtragem de intermediações com tabelas responsivas e pesquisa rápida.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-number">03</div>
                    <h3>Gestão de Negociações</h3>
                    <p>Sistema completo para registro, acompanhamento e estorno de negociações com controle total.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-number">04</div>
                    <h3>Auditoria Completa</h3>
                    <p>Rastreamento detalhado de todas as operações do sistema com histórico completo de ações.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-number">05</div>
                    <h3>Segurança Avançada</h3>
                    <p>Sistema robusto de autenticação, permissões e proteção contra vulnerabilidades comuns.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-number">06</div>
                    <h3>Design Responsivo</h3>
                    <p>Interface moderna que se adapta perfeitamente a qualquer dispositivo, mobile ou desktop.</p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- CTA Section -->
    <section class="cta">
        <div class="container">
            <h2>Pronto para começar?</h2>
            <p>Acesse o sistema agora e experimente a melhor solução para gerenciamento de intermediações</p>
            <a href="index.php?action=login" class="btn-hero btn-hero-primary">Fazer Login</a>
        </div>
    </section>
    
    <!-- Footer -->
    <footer class="landing-footer">
        <p>&copy; 2026 Experimento Tecnologias. Desenvolvido com tecnologia e inovação.</p>
    </footer>
</body>
</html>
