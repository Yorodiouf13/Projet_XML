<?php
session_start();
if (isset($_SESSION['utilisateur_id'])) {
    header('Location: chat.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Plateforme Chat XML - Accueil</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .hero-container {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }
        
        .hero-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 60 60"><g fill="none" fill-rule="evenodd"><g fill="%23ffffff" fill-opacity="0.1"><circle cx="30" cy="30" r="2"/></g></svg>');
            opacity: 0.3;
        }
        
        .hero-content {
            background: white;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
            padding: 60px 40px;
            text-align: center;
            max-width: 700px;
            width: 100%;
            position: relative;
            z-index: 1;
        }
        
        .hero-logo {
            width: 120px;
            height: 120px;
            background: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            color: white;
            margin: 0 auto 32px;
            box-shadow: 0 10px 30px rgba(0, 168, 132, 0.3);
        }
        
        .hero-title {
            font-size: 32px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 16px;
            line-height: 1.2;
        }
        
        .hero-subtitle {
            font-size: 18px;
            color: var(--text-secondary);
            margin-bottom: 40px;
            line-height: 1.5;
        }
        
        .hero-features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 24px;
            margin-bottom: 40px;
        }
        
        .feature-item {
            text-align: center;
        }
        
        .feature-icon {
            width: 60px;
            height: 60px;
            background: var(--background-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: var(--primary-color);
            margin: 0 auto 12px;
        }
        
        .feature-title {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 4px;
        }
        
        .feature-desc {
            font-size: 12px;
            color: var(--text-secondary);
        }
        
        .hero-actions {
            display: flex;
            gap: 16px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .hero-btn {
            padding: 16px 32px;
            border-radius: 12px;
            border: none;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
            min-width: 160px;
            justify-content: center;
        }
        
        .hero-btn.primary {
            background: var(--primary-color);
            color: white;
            box-shadow: 0 4px 15px rgba(0, 168, 132, 0.3);
        }
        
        .hero-btn.primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 168, 132, 0.4);
        }
        
        .hero-btn.secondary {
            background: var(--background-color);
            color: var(--text-primary);
            border: 2px solid var(--border-color);
        }
        
        .hero-btn.secondary:hover {
            background: var(--hover-color);
            transform: translateY(-2px);
        }
        
        .hero-footer {
            margin-top: 40px;
            padding-top: 24px;
            border-top: 1px solid var(--border-color);
            font-size: 12px;
            color: var(--text-muted);
        }
        
        @media (max-width: 768px) {
            .hero-content {
                padding: 24px 24px;
                margin: 20px;
            }
            
            .hero-title {
                font-size: 24px;
            }
            
            .hero-subtitle {
                font-size: 16px;
            }
            
            .hero-actions {
                flex-direction: column;
            }
            
            .hero-btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="hero-container">
        <div class="hero-content">
            <div class="hero-logo">
                <i class="fas fa-comments"></i>
            </div>
            
            <h1 class="hero-title">Plateforme Chat XML</h1>
            <p class="hero-subtitle">
                Connectez-vous avec vos amis et collègues dans un environnement sécurisé et moderne
            </p>
            
            <div class="hero-features">
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-comment-dots"></i>
                    </div>
                    <div class="feature-title">Messages privés</div>
                    <div class="feature-desc">Conversations sécurisées</div>
                </div>
                
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="feature-title">Groupes</div>
                    <div class="feature-desc">Discussions de groupe</div>
                </div>
                
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="feature-title">Partage de fichiers</div>
                    <div class="feature-desc">Documents et médias</div>
                </div>
                
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div class="feature-title">Sécurisé</div>
                    <div class="feature-desc">Données protégées</div>
                </div>
            </div>
            
            <div class="hero-actions">
                <a href="connexion.php" class="hero-btn primary">
                    <i class="fas fa-sign-in-alt"></i>
                    Se connecter
                </a>
                <a href="inscription.php" class="hero-btn secondary">
                    <i class="fas fa-user-plus"></i>
                    Créer un compte
                </a>
            </div>
            
            <div class="hero-footer">
                <p>
                    <i class="fas fa-code"></i>
                    Projet XML • M1 GLSI • Zeynabou, Amadou, Nafy, Yoro, Sira
                </p>
            </div>
        </div>
    </div>
</body>
</html>
