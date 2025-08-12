<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BIP API - Banque d'Idées de Projets</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />1
    
    <!-- Styles -->
    <?php if(file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot'))): ?>
        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <?php else: ?>
        <style>
            body { 
                font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                margin: 0;
                padding: 0;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .container {
                background: white;
                border-radius: 20px;
                box-shadow: 0 20px 40px rgba(0,0,0,0.1);
                padding: 3rem;
                max-width: 800px;
                width: 90%;
                text-align: center;
            }
            .logo {
                font-size: 3rem;
                font-weight: 600;
                color: #667eea;
                margin-bottom: 0.5rem;
            }
            .subtitle {
                color: #6b7280;
                font-size: 1.2rem;
                margin-bottom: 2rem;
            }
            .description {
                color: #374151;
                line-height: 1.6;
                margin-bottom: 2rem;
                font-size: 1.1rem;
            }
            .api-info {
                background: #f8fafc;
                border: 1px solid #e2e8f0;
                border-radius: 12px;
                padding: 2rem;
                margin: 2rem 0;
            }
            .endpoint {
                font-family: 'Courier New', monospace;
                background: #1f2937;
                color: #10b981;
                padding: 1rem;
                border-radius: 8px;
                margin: 1rem 0;
                font-size: 1.1rem;
            }
            .features {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 1.5rem;
                margin: 2rem 0;
            }
            .feature {
                background: #f8fafc;
                padding: 1.5rem;
                border-radius: 12px;
                border-left: 4px solid #667eea;
            }
            .feature-title {
                font-weight: 600;
                color: #1f2937;
                margin-bottom: 0.5rem;
            }
            .feature-desc {
                color: #6b7280;
                font-size: 0.9rem;
            }
            .nav-links {
                display: flex;
                justify-content: center;
                gap: 1rem;
                margin-top: 2rem;
            }
            .nav-link {
                padding: 0.75rem 1.5rem;
                background: #667eea;
                color: white;
                text-decoration: none;
                border-radius: 8px;
                font-weight: 500;
                transition: all 0.3s;
            }
            .nav-link:hover {
                background: #5a67d8;
                transform: translateY(-2px);
            }
            .status {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                background: #dcfce7;
                color: #166534;
                padding: 0.5rem 1rem;
                border-radius: 20px;
                font-size: 0.9rem;
                font-weight: 500;
                margin-bottom: 1rem;
            }
            .status-dot {
                width: 8px;
                height: 8px;
                background: #16a34a;
                border-radius: 50%;
                animation: pulse 2s infinite;
            }
            @keyframes pulse {
                0%, 100% { opacity: 1; }
                50% { opacity: 0.5; }
            }
        </style>
    <?php endif; ?>
</head>
<body>
    <div class="container">
        <div class="status">
            <span class="status-dot"></span>
            API en ligne
        </div>
        
        <div class="logo">🏦 BIP API</div>
        <div class="subtitle">Banque d'Idées de Projets</div>
        
        <div class="description">
            Plateforme REST API pour la gestion et le suivi des idées de projets, 
            du développement à l'évaluation, avec gestion des utilisateurs via Keycloak.
        </div>
        
        <div class="api-info">
            <h3 style="margin-top: 0; color: #1f2937;">Point d'accès API</h3>
            <div class="endpoint"><?php echo e(url('/api')); ?></div>
            <p style="color: #6b7280; margin-bottom: 0;">
                Documentation disponible via les routes /api
            </p>
        </div>
        
        <div class="features">
            <div class="feature">
                <div class="feature-title">🔐 Authentification</div>
                <div class="feature-desc">Intégration Keycloak pour la gestion sécurisée des utilisateurs et des permissions</div>
            </div>
            
            <div class="feature">
                <div class="feature-title">💡 Gestion d'Idées</div>
                <div class="feature-desc">CRUD complet pour les idées de projets avec workflow de validation</div>
            </div>
            
            <div class="feature">
                <div class="feature-title">📊 Évaluations</div>
                <div class="feature-desc">Système d'évaluation et de notation des projets avec critères personnalisables</div>
            </div>
            
            <div class="feature">
                <div class="feature-title">🏢 Multi-Organisation</div>
                <div class="feature-desc">Support des organisations, secteurs et entités géographiques</div>
            </div>
            
            <div class="feature">
                <div class="feature-title">📋 Workflows</div>
                <div class="feature-desc">Gestion des états et processus de validation des projets</div>
            </div>
            
            <div class="feature">
                <div class="feature-title">📄 Documents</div>
                <div class="feature-desc">Gestion des documents attachés aux projets avec catégorisation</div>
            </div>
        </div>
        
        <?php if(Route::has('login')): ?>
            <div class="nav-links">
                <?php if(auth()->guard()->check()): ?>
                    <a href="<?php echo e(url('/api')); ?>" class="nav-link">📚 Explorer l'API</a>
                <?php else: ?>
                    <a href="<?php echo e(route('login')); ?>" class="nav-link">🔑 Se connecter</a>
                    <?php if(Route::has('register')): ?>
                        <a href="<?php echo e(route('register')); ?>" class="nav-link">📝 S'inscrire</a>
                    <?php endif; ?>
                <?php endif; ?>
                <a href="<?php echo e(url('/api')); ?>" class="nav-link">🚀 Documentation API</a>
            </div>
        <?php endif; ?>
        
        <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #e2e8f0; color: #6b7280; font-size: 0.9rem;">
            <p><strong>Environnement:</strong> <?php echo e(app()->environment()); ?></p>
            <p><strong>Version Laravel:</strong> <?php echo e(app()->version()); ?></p>
        </div>
    </div>
</body>
</html><?php /**PATH /home/unknow/GDIZ/apps/backend_api/resources/views/welcome.blade.php ENDPATH**/ ?>