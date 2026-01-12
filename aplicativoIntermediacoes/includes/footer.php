    </main>
    
    <footer class="modern-footer">
        <div class="footer-container">
            <div class="footer-content">
                <div class="footer-logo">
                    <img src="assets/images/logo-icon.svg" alt="Experimento Tecnologias">
                    <span>Experimento Tecnologias</span>
                </div>
                <div class="footer-links">
                    <a href="landing.php">Início</a>
                    <span class="separator">•</span>
                    <a href="index.php?controller=dashboard&action=index">Sistema</a>
                    <span class="separator">•</span>
                    <span>&copy; <?= date('Y') ?> Todos os direitos reservados</span>
                </div>
            </div>
        </div>
    </footer>
    
    <style>
        .modern-footer {
            background: var(--gray-900);
            color: white;
            margin-top: var(--space-3xl);
            padding: var(--space-xl);
        }
        
        .footer-container {
            max-width: 1280px;
            margin: 0 auto;
        }
        
        .footer-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: var(--space-md);
        }
        
        .footer-logo {
            display: flex;
            align-items: center;
            gap: var(--space-sm);
        }
        
        .footer-logo img {
            height: 30px;
            filter: brightness(0) invert(1);
        }
        
        .footer-logo span {
            font-weight: 600;
            font-size: 1rem;
        }
        
        .footer-links {
            display: flex;
            align-items: center;
            gap: var(--space-md);
            font-size: 0.875rem;
            color: var(--gray-400);
        }
        
        .footer-links a {
            color: var(--gray-400);
            text-decoration: none;
            transition: color var(--transition-base);
        }
        
        .footer-links a:hover {
            color: white;
        }
        
        .separator {
            color: var(--gray-600);
        }
        
        @media (max-width: 768px) {
            .footer-links {
                flex-direction: column;
                text-align: center;
            }
            
            .separator {
                display: none;
            }
        }
    </style>
</body>
</html>
