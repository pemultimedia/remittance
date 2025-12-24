<!-- Main Content Area -->
<main class="flex-1 flex justify-center py-10 px-4 md:px-10">
    <div class="w-full max-w-[1200px] flex flex-col gap-6">
        <!-- Page Heading -->
        <div class="flex flex-col gap-2">
            <h2 class="text-[#111418] dark:text-white text-3xl md:text-4xl font-black tracking-tight">Gestione Fornitori</h2>
            <p class="text-[#617589] dark:text-gray-400 text-base font-normal">Configura i collegamenti ai Fogli Google per i fornitori rilevati.</p>
        </div>
        
        <!-- Table Card -->
        <div class="bg-white dark:bg-[#1e2732] rounded-xl border border-[#dbe0e6] dark:border-gray-700 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <!-- Table Head -->
                    <thead>
                        <tr class="bg-gray-50 dark:bg-[#232d38] border-b border-[#dbe0e6] dark:border-gray-700">
                            <th class="p-4 text-sm font-semibold text-[#111418] dark:text-white w-1/5 min-w-[150px]">Amazon Site</th>
                            <th class="p-4 text-sm font-semibold text-[#111418] dark:text-white w-1/4 min-w-[200px]">Nome Fornitore</th>
                            <th class="p-4 text-sm font-semibold text-[#111418] dark:text-white w-[120px]">Valuta</th>
                            <th class="p-4 text-sm font-semibold text-[#111418] dark:text-white min-w-[180px]">Stato Configurazione</th>
                            <th class="p-4 text-sm font-semibold text-[#617589] dark:text-gray-400 w-[120px] text-right">Azioni</th>
                        </tr>
                    </thead>
                    <!-- Table Body -->
                    <tbody class="divide-y divide-[#dbe0e6] dark:divide-gray-700">
                        <?php if (empty($suppliers)): ?>
                            <tr>
                                <td colspan="5" class="p-4 text-center text-gray-500">Nessun fornitore trovato. Attendi l'esecuzione dello script di importazione.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($suppliers as $s): 
                                $isConfigured = !empty($s['google_spreadsheet_id']);
                            ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-[#2a3441] transition-colors group">
                                <td class="p-4 text-sm text-[#617589] dark:text-gray-300 font-medium flex items-center gap-2">
                                    <span class="material-symbols-outlined text-[20px] text-gray-400">language</span>
                                    <code><?= htmlspecialchars($s['amazon_supplier_site_name']) ?></code>
                                </td>
                                <td class="p-4 text-sm text-[#111418] dark:text-white font-medium">
                                    <?= htmlspecialchars($s['name'] ?? 'N/D') ?>
                                </td>
                                <td class="p-4">
                                    <div class="inline-flex items-center px-2.5 py-1 rounded bg-gray-100 dark:bg-gray-700 text-xs font-semibold text-gray-700 dark:text-gray-300">
                                        <?= htmlspecialchars($s['currency']) ?>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <?php if ($isConfigured): ?>
                                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 text-xs font-bold uppercase tracking-wide border border-green-200 dark:border-green-800">
                                            <span class="material-symbols-outlined text-[14px]">check_circle</span>
                                            Configurato
                                        </div>
                                    <?php else: ?>
                                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400 text-xs font-bold uppercase tracking-wide border border-yellow-200 dark:border-yellow-800">
                                            <span class="material-symbols-outlined text-[14px]">hourglass_empty</span>
                                            Da Configurare
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="p-4 text-right">
                                    <a href="/suppliers/edit?id=<?= $s['id'] ?>" class="inline-flex items-center justify-center h-8 px-4 rounded bg-primary hover:bg-blue-600 text-white text-xs font-bold tracking-wide transition-colors shadow-sm text-decoration-none">
                                        Modifica
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>