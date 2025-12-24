<?php
// Determina le classi del body
$isLogin = strpos($_SERVER['REQUEST_URI'], 'login') !== false;
// Aggiungiamo 'flex-col' e 'min-h-screen' per spingere il footer in basso
$bodyClass = $isLogin 
    ? "font-display bg-background-light dark:bg-background-dark text-[#111418] dark:text-white min-h-screen flex flex-col justify-center items-center p-4"
    : "bg-background-light dark:bg-background-dark font-display text-[#111418] antialiased min-h-screen flex flex-col";
?>
<!DOCTYPE html>
<html class="light" lang="it">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Remittance Dashboard</title>
    
    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;900&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    
    <!-- Bootstrap & Tailwind -->
    <link crossorigin="anonymous" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#137fec",
                        "background-light": "#f6f7f8",
                        "background-dark": "#101922",
                    },
                    fontFamily: {
                        "display": ["Inter", "sans-serif"],
                    },
                },
            },
        }
    </script>
    <style>
        a { text-decoration: none; }
        .form-input:focus { box-shadow: none; border-color: #137fec; }
    </style>
</head>
<body class="<?= $bodyClass ?>">

    <?php if (\App\Core\Session::isLoggedIn()): ?>
    <!-- Navbar (Solo se loggati) -->
    <header class="bg-[#101922] text-white sticky top-0 z-50 shadow-md w-full">
        <div class="px-6 md:px-10 py-3 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="size-8 text-primary">
                    <svg fill="currentColor" viewbox="0 0 48 48" xmlns="http://www.w3.org/2000/svg" style="width:32px; height:32px;">
                        <path clip-rule="evenodd" d="M24 0.757355L47.2426 24L24 47.2426L0.757355 24L24 0.757355ZM21 35.7574V12.2426L9.24264 24L21 35.7574Z" fill-rule="evenodd"></path>
                    </svg>
                </div>
                <h1 class="text-lg font-bold tracking-tight m-0">Remittance Dashboard</h1>
            </div>
            <nav class="hidden md:flex items-center gap-8">
                <a class="text-sm font-medium text-gray-400 hover:text-white transition-colors" href="/dashboard">Dashboard</a>
                <a class="text-sm font-medium text-white border-b-2 border-primary pb-0.5" href="/suppliers">Fornitori</a>
            </nav>
            <div class="flex items-center gap-6">
                <div class="hidden sm:block text-sm font-medium text-gray-300">
                    Ciao, <span class="text-white"><?= htmlspecialchars($_SESSION['username'] ?? 'User') ?></span>
                </div>
                <a href="/logout" class="flex items-center justify-center h-9 px-4 rounded-lg bg-white/10 hover:bg-white/20 text-white text-sm font-medium transition-colors border border-white/10 text-decoration-none">
                    <span class="material-symbols-outlined text-[18px] mr-2">logout</span>
                    Logout
                </a>
            </div>
        </div>
    </header>
    <?php endif; ?>

    <!-- Flash Messages -->
    <?php 
    $flash = \App\Core\Session::getFlash();
    if ($flash): 
        $bgColor = $flash['type'] === 'danger' ? 'bg-red-50 border-red-200 text-red-800' : 'bg-green-50 border-green-200 text-green-800';
        $icon = $flash['type'] === 'danger' ? 'error' : 'check_circle';
    ?>
    <div class="w-full max-w-[1200px] mx-auto mt-4 px-4">
        <div class="flex gap-3 rounded-lg border <?= $bgColor ?> p-4 shadow-sm" role="alert">
            <span class="material-symbols-outlined shrink-0 text-xl"><?= $icon ?></span>
            <div class="flex flex-col gap-1 text-sm">
                <p class="font-bold"><?= $flash['type'] === 'danger' ? 'Attenzione' : 'Successo' ?></p>
                <p><?= $flash['message'] ?></p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Contenuto Principale -->
    <!-- Se è login, il contenuto è centrato dal body. Se è dashboard, usiamo flex-1 -->
    <div class="<?= $isLogin ? 'w-full flex justify-center' : 'flex-1 w-full' ?>">
        <?php if (isset($content)) require $content; ?>
    </div>

    <!-- Footer Globale -->
    <footer class="w-full py-6 text-center text-sm text-[#617589] dark:text-[#64748b] mt-auto">
        <div class="flex flex-col gap-2">
            <p>&copy; <?= date('Y') ?> Remittance Dashboard. Tutti i diritti riservati.</p>
            <?php if ($isLogin): ?>
                <div class="flex justify-center gap-4 text-xs">
                    <a href="#" class="hover:text-primary transition-colors">Privacy Policy</a>
                    <a href="#" class="hover:text-primary transition-colors">Termini di Servizio</a>
                    <a href="#" class="hover:text-primary transition-colors">Supporto</a>
                </div>
            <?php endif; ?>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>